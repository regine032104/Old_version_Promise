<?php
require 'connections.php';

// Database wrapper
$db = Database::getInstance();
$pdo = $db->getConnection();

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required!']);
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format!']);
    exit;
}

try {
    // Check if user exists
    $user = $db->fetchOne("SELECT customer_id, first_name, last_name, email, password_hash FROM customers WHERE email = ?", [$email]);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password!']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password!']);
        exit;
    }
    
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $user['customer_id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    
    // If there is a session cart, merge it into the DB-backed cart for this user
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $sessionCart = $_SESSION['cart'];
        try {
            // Start a transaction for merging
            $db->beginTransaction();

            // Get or create cart for this user
            $row = $db->fetchOne('SELECT cart_id FROM carts WHERE customer_id = ? LIMIT 1', [$user['customer_id']]);
            if ($row) {
                $cart_id = (int)$row['cart_id'];
            } else {
                $db->execute('INSERT INTO carts (customer_id) VALUES (?)', [$user['customer_id']]);
                $cart_id = (int)$db->lastInsertId();
            }

            foreach ($sessionCart as $product_id => $qty) {
                $product_id = (int)$product_id;
                $qty = (int)$qty;
                if ($product_id <= 0 || $qty <= 0) continue;

                // Get available stock
                $prod = $db->fetchOne('SELECT quantity_in_stock FROM products WHERE product_id = ?', [$product_id]);
                if (!$prod) continue;
                $available = (int)$prod['quantity_in_stock'];

                // Get existing in cart
                $crow = $db->fetchOne('SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart_id, $product_id]);
                $existing = $crow ? (int)$crow['quantity'] : 0;

                $new_qty = $existing + $qty;
                if ($new_qty > $available) {
                    $new_qty = $available;
                }

                if ($new_qty <= 0) {
                    // remove if exists
                    if ($existing > 0) {
                        $db->execute('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart_id, $product_id]);
                    }
                    continue;
                }

                if ($existing > 0) {
                    $db->execute('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?', [$new_qty, $cart_id, $product_id]);
                } else {
                    $db->execute('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)', [$cart_id, $product_id, $new_qty]);
                }
            }

            // Commit merge
            $db->commit();

            // Sync session cart to DB cart items
            $items = $db->fetchAll('SELECT product_id, quantity FROM cart_items WHERE cart_id = ?', [$cart_id]);
            $_SESSION['cart'] = [];
            foreach ($items as $it) {
                $_SESSION['cart'][(int)$it['product_id']] = (int)$it['quantity'];
            }

        } catch (Exception $e) {
            if ($db->getConnection()->inTransaction()) $db->rollBack();
            // Log merge error but continue login
            error_log('Cart merge error: ' . $e->getMessage());
        }
    }
    
    // After successful login, redirect to intended page or default
    $redirectTo = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '../pages/home.php';
    unset($_SESSION['redirect_after_login']);

    echo json_encode([
        'success' => true, 
        'message' => 'Login successful! Welcome back, ' . $user['first_name'] . '!',
        'user' => [
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email']
        ],
        'redirect' => $redirectTo,
        'cartCount' => isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>