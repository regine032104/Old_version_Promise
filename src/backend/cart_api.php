<?php
session_start();
require_once('connections.php');
require_once('session_check.php');

header('Content-Type: application/json');

// Database wrapper instance (backwards-compatible with $pdo usage)
$db = Database::getInstance();
$pdo = $db->getConnection();

// Helper: sum cart count
function cart_count() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

// Helper: get or create DB cart for logged in user
function get_or_create_db_cart($customer_id) {
    global $db;
    $row = $db->fetchOne('SELECT cart_id FROM carts WHERE customer_id = ? LIMIT 1', [$customer_id]);
    if ($row) return (int)$row['cart_id'];

    $db->execute('INSERT INTO carts (customer_id) VALUES (?)', [$customer_id]);
    return (int)$db->lastInsertId();
}

function db_cart_count($cart_id) {
    global $db;
    $r = $db->fetchOne('SELECT SUM(quantity) as c FROM cart_items WHERE cart_id = ?', [$cart_id]);
    return $r ? (int)$r['c'] : 0;
}

function get_db_cart_map($cart_id) {
    global $db;
    $items = $db->fetchAll('SELECT product_id, quantity FROM cart_items WHERE cart_id = ?', [$cart_id]);
    $map = [];
    foreach ($items as $it) $map[(int)$it['product_id']] = (int)$it['quantity'];
    return $map;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'get';

try {
    if ($action === 'add') {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        if ($product_id <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
            exit;
        }

        // Fetch product stock
        $prod = $db->fetchOne('SELECT product_id, quantity_in_stock FROM products WHERE product_id = ?', [$product_id]);
        if (!$prod) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        $available = (int)$prod['quantity_in_stock'];

        if (isLoggedIn()) {
            // Use DB-backed cart for logged in users
            $customer_id = $_SESSION['user_id'];
            $cart_id = get_or_create_db_cart($customer_id);

            // Check existing in DB
            $row = $db->fetchOne('SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart_id, $product_id]);
            $existing = $row ? (int)$row['quantity'] : 0;

            if ($existing + $quantity > $available) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available', 'available' => $available, 'current' => $existing]);
                exit;
            }

            if ($row) {
                $db->execute('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?', [$existing + $quantity, $cart_id, $product_id]);
            } else {
                $db->execute('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)', [$cart_id, $product_id, $quantity]);
            }

            // Sync session cart to DB representation
            $_SESSION['cart'] = get_db_cart_map($cart_id);
            echo json_encode(['success' => true, 'message' => 'Added to cart', 'cartCount' => db_cart_count($cart_id)]);
            exit;
        } else {
            $existing = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id] : 0;

            if ($existing + $quantity > $available) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available', 'available' => $available, 'current' => $existing]);
                exit;
            }

            // Update session cart
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];
            $_SESSION['cart'][$product_id] = $existing + $quantity;

            echo json_encode(['success' => true, 'message' => 'Added to cart', 'cartCount' => cart_count()]);
            exit;
        }
    }

    if ($action === 'bulk_update') {
        // Expect keys like quantity-<id>
        $updated = [];
        $errors = [];
        foreach ($_POST as $k => $v) {
            if (strpos($k, 'quantity-') === 0) {
                $id = (int)str_replace('quantity-', '', $k);
                $qty = (int)$v;
                if ($id <= 0) continue;

                // Get available stock
                $prod = $db->fetchOne('SELECT quantity_in_stock FROM products WHERE product_id = ?', [$id]);
                $available = $prod ? (int)$prod['quantity_in_stock'] : 0;

                if (isLoggedIn()) {
                    // operate on DB cart
                    $customer_id = $_SESSION['user_id'];
                    $cart_id = get_or_create_db_cart($customer_id);

                    if ($qty <= 0) {
                        $db->execute('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart_id, $id]);
                        $updated[$id] = 0;
                        continue;
                    }

                    if ($qty > $available) {
                        if ($available > 0) {
                            $db->execute('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = ?', [$cart_id, $id, $available, $available]);
                            $updated[$id] = $available;
                            $errors[] = ['product_id' => $id, 'message' => 'Adjusted to available stock'];
                        } else {
                            $db->execute('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart_id, $id]);
                            $updated[$id] = 0;
                            $errors[] = ['product_id' => $id, 'message' => 'Removed, out of stock'];
                        }
                    } else {
                        $db->execute('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = ?', [$cart_id, $id, $qty, $qty]);
                        $updated[$id] = $qty;
                    }

                    // sync session
                    $_SESSION['cart'] = get_db_cart_map($cart_id);
                    continue;
                }

                if ($qty <= 0) {
                    // remove from cart
                    if (isset($_SESSION['cart'][$id])) unset($_SESSION['cart'][$id]);
                    $updated[$id] = 0;
                    continue;
                }

                if ($qty > $available) {
                    // set to available or remove
                    if ($available > 0) {
                        $_SESSION['cart'][$id] = $available;
                        $updated[$id] = $available;
                        $errors[] = ['product_id' => $id, 'message' => 'Adjusted to available stock'];
                    } else {
                        unset($_SESSION['cart'][$id]);
                        $updated[$id] = 0;
                        $errors[] = ['product_id' => $id, 'message' => 'Removed, out of stock'];
                    }
                } else {
                    $_SESSION['cart'][$id] = $qty;
                    $updated[$id] = $qty;
                }
            }
        }

        echo json_encode(['success' => true, 'updated' => $updated, 'errors' => $errors, 'cartCount' => cart_count()]);
        exit;
    }

    if ($action === 'remove') {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if (isLoggedIn()) {
            $customer_id = $_SESSION['user_id'];
            $cart_id = get_or_create_db_cart($customer_id);
            if ($product_id > 0) {
                $db->execute('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart_id, $product_id]);
            }
            $_SESSION['cart'] = get_db_cart_map($cart_id);
            echo json_encode(['success' => true, 'cartCount' => db_cart_count($cart_id)]);
        } else {
            if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            echo json_encode(['success' => true, 'cartCount' => cart_count()]);
        }
        exit;
    }

    // Default: return cart summary
    if (isLoggedIn()) {
        $customer_id = $_SESSION['user_id'];
        $cart_id = get_or_create_db_cart($customer_id);
        $cart = get_db_cart_map($cart_id);
        echo json_encode(['success' => true, 'cart' => $cart, 'cartCount' => db_cart_count($cart_id)]);
    } else {
        $cart = $_SESSION['cart'] ?? [];
        echo json_encode(['success' => true, 'cart' => $cart, 'cartCount' => cart_count()]);
    }
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

?>
