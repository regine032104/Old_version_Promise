<?php
session_start();
require_once('connections.php');
require_once('session_check.php');

// Use Database wrapper instance
$db = Database::getInstance();
$pdo = $db->getConnection();

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../pages/home.php');
    exit;
}

// Check if cart exists and is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ../pages/cart.php');
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

try {
    // Start transaction using wrapper
    $db->beginTransaction();
    
    // Calculate total amount
    $total_amount = 0;
    $cart_items = [];
    
    // Get product details for cart items and lock rows for update to prevent oversell
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    // Use FOR UPDATE to lock selected product rows within this transaction
    $stmt = $db->query("SELECT * FROM products WHERE product_id IN ($placeholders) FOR UPDATE", $product_ids);
    $products = $stmt->fetchAll();
    
    // Calculate total, validate stock, and prepare cart items
    foreach ($products as $product) {
        $product_id = $product['product_id'];
        $quantity = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id] : 0;

        // Validate requested quantity against available stock
        $available = isset($product['quantity_in_stock']) ? (int)$product['quantity_in_stock'] : 0;
        if ($quantity <= 0) {
            // Invalid quantity requested
            $db->rollBack();
            header('Location: ../pages/cart.php?error=invalid_quantity');
            exit;
        }
        if ($quantity > $available) {
            // Not enough stock for this item
            $db->rollBack();
            header('Location: ../pages/cart.php?error=out_of_stock&product_id=' . $product_id);
            exit;
        }

        $item_total = $product['price'] * $quantity;
        $total_amount += $item_total;

        $cart_items[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'unit_price' => $product['price']
        ];
    }
    
    // Get user's address from database
    $user = $db->fetchOne("SELECT * FROM customers WHERE customer_id = ?", [$user_id]);
    
    $shipping_address = '';
    if ($user) {
        $address_parts = array_filter([
            $user['street_address'],
            $user['barangay'],
            $user['city'],
            $user['province'],
            $user['zip_code']
        ]);
        $shipping_address = implode(', ', $address_parts);
    }
    
    $notes = 'Order placed via website';
    
    // Re-run the insert with correct shipping address and notes (safer to bind after computing address)
    $db->execute("INSERT INTO orders (customer_id, total_amount, status, shipping_address, payment_method, notes) 
        VALUES (?, ?, 'pending', ?, 'Cash on Delivery', ?)", [$user_id, $total_amount, $shipping_address, $notes]);
    $order_id = $db->lastInsertId();
    
    // Insert order items
    // Insert order items
    foreach ($cart_items as $item) {
        $db->execute("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)", [
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price']
        ]);
    }
    
    // Decrement product stock for each ordered item
    // Decrement product stock for each ordered item
    foreach ($cart_items as $item) {
        $db->execute("UPDATE products SET quantity_in_stock = quantity_in_stock - ? WHERE product_id = ?", [$item['quantity'], $item['product_id']]);
    }
    
    // Commit transaction
    $db->commit();
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Store order ID in session for success page
    $_SESSION['last_order_id'] = $order_id;
    
    // Redirect to success page
    header('Location: ../pages/placeorder.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    // Rollback using wrapper if transaction is active
    if ($db->getConnection()->inTransaction()) {
        $db->rollBack();
    }
    
    // Log error (you might want to implement proper logging)
    error_log("Order placement error: " . $e->getMessage());
    
    // Redirect to cart with error message
    header('Location: ../pages/cart.php?error=1');
    exit;
}
?>
