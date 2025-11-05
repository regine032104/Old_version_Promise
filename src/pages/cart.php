<?php
require_once('../backend/session_check.php');
require_once('../backend/connections.php');

// Database wrapper instance
$db = Database::getInstance();
$pdo = $db->getConnection();

$isLoggedIn = isLoggedIn();
$user_name = $_SESSION['user_name'] ?? null;
$user_email = $_SESSION['user_email'] ?? null;

// If the user clicked the add to cart button on the product page we can check for the form data
if (isset($_POST['product_id'], $_POST['quantity']) && is_numeric($_POST['product_id']) && is_numeric($_POST['quantity'])) {

    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    $product = $db->fetchOne('SELECT * FROM products WHERE product_id = ?', [$product_id]);
    // Check if the product exists and the requested quantity is valid
    if ($product && $quantity > 0) {
        $available = isset($product['quantity_in_stock']) ? (int)$product['quantity_in_stock'] : 0;
        $existing = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id] : 0;

        // If requested (existing + new) quantity exceeds available stock, reject and redirect with error
        if ($existing + $quantity > $available) {
            // Redirect back to product detail with an out of stock error
            header('Location: product-detail.php?id=' . $product_id . '&error=out_of_stock');
            exit;
        }

        // Product exists in database and stock is sufficient, now we can create/update the session variable for the cart
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            if (array_key_exists($product_id, $_SESSION['cart'])) {
                // Product exists in cart so just update the quantity
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                // Product is not in cart so add it
                $_SESSION['cart'][$product_id] = $quantity;
            }
        } else {
            // There are no products in cart, this will add the first product to cart
            $_SESSION['cart'] = array($product_id => $quantity);
        }
    }
    // Prevent form resubmission...
    header('Location: cart.php');
    exit;
}

// Remove product from cart, check for the URL param "remove", this is the product id, make sure it's a number and check if it's in the cart
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    // Remove the product from the shopping cart
    unset($_SESSION['cart'][$_GET['remove']]);
}

// Update product quantities in cart if the user clicks the "Update" button on the shopping cart page
if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    // Prepare to check stock for all updated items
    $updateError = false;
    $errorProductId = null;

    // Loop through the post data so we can update the quantities for every product in cart
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int)$v;
            // Always do checks and validation
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                // Fetch available stock for this product
                $prod = $db->fetchOne('SELECT quantity_in_stock FROM products WHERE product_id = ?', [$id]);
                $available = $prod ? (int)$prod['quantity_in_stock'] : 0;

                if ($quantity > $available) {
                    // If requested exceeds available, set to available if >0, otherwise remove
                    if ($available > 0) {
                        $_SESSION['cart'][$id] = $available;
                    } else {
                        unset($_SESSION['cart'][$id]);
                    }
                    $updateError = true;
                    $errorProductId = $id;
                } else {
                    // Update new quantity
                    $_SESSION['cart'][$id] = $quantity;
                }
            }
        }
    }

    // Prevent form resubmission... If there was an update error, include product id
    if ($updateError) {
        header('Location: cart.php?error=out_of_stock&product_id=' . $errorProductId);
    } else {
        header('Location: cart.php');
    }
    exit;
}

// Send the user to the place order page if they click the Place Order button, also the cart should not be empty
if (isset($_POST['placeorder']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    header('Location: ../backend/place_order.php');
    exit;
}

// Check the session variable for products in cart
$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$products = array();
$subtotal = 0.00;
// If there are products in cart
if ($products_in_cart) {
    // There are products in the cart so we need to select those products from the database
    // Products in cart array to question mark string array, we need the SQL statement to include IN (?,?,?,...etc)
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    // We only need the array keys, not the values, the keys are the id's of the products
    $products = $db->fetchAll('SELECT * FROM products WHERE product_id IN (' . $array_to_question_marks . ')', array_keys($products_in_cart));
    // Calculate the subtotal
    foreach ($products as $product) {
        $subtotal += (float)$product['price'] * (int)$products_in_cart[$product['product_id']];
    }
}
?>
<?php
require_once('../layouts/app.php');
renderHeader([
    'title' => 'Cart - Promise Shop',
    'isLoggedIn' => $isLoggedIn,
    'bodyClass' => 'bg-white min-h-screen flex flex-col',
    'mainClass' => 'flex-1'
]);
?>
<div class="py-20 sm:py-32 cart content-wrapper">
    <div class="container mx-auto px-4 py-6 sm:px-6">
        <h1 class="font-Tinos text-4xl text-pink-950 mb-8 text-center">Shopping Cart</h1>
        <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
            <p class="text-red-800">There was an error placing your order. Please try again.</p>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'out_of_stock'): ?>
        <div class="mb-6 rounded-lg border border-orange-200 bg-orange-50 p-4">
            <p class="text-orange-800">One or more items in your cart exceeded available stock and were adjusted or removed. Please review quantities.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
        <div class="flex flex-col-reverse gap-8 lg:flex-row">
            <section class="flex-1">
                <div class="flex min-h-[360px] flex-col items-center justify-center rounded-2xl border border-pink-200 bg-white p-10 text-center shadow-[0_10px_30px_rgba(236,72,153,0.06)]">
                    <h2 class="font-Tinos text-3xl font-semibold text-pink-800 sm:text-4xl">
                        Your cart is empty.
                    </h2>
                    <p class="font-unna mt-4 max-w-md text-base leading-relaxed text-pink-700/80 sm:text-lg">
                        Browse our products and add items to see them here. We'll save
                        your selections once you add them.
                    </p>
                    <a href="shop.php"
                        class="mt-8 inline-flex items-center justify-center rounded-full bg-gradient-to-r from-pink-500 to-rose-500 px-8 py-3 font-medium text-white transition hover:shadow-[0_0_40px_rgba(236,72,153,0.25)] focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 focus:ring-offset-pink-100 focus:outline-none">Start
                        Shopping</a>
                </div>
            </section>

            <aside class="w-full self-start rounded-xl border border-transparent bg-white p-6 shadow-lg lg:sticky lg:top-24 lg:w-96">
                <h3 class="mb-4 text-2xl font-semibold text-pink-800">
                    Order Summary
                </h3>
                <div class="mb-2 flex justify-between text-pink-700/80">
                    <span>Subtotal</span>
                    <span>$0.00</span>
                </div>
                <div class="mb-4 flex justify-between text-pink-700/80">
                    <span>Estimated Shipping</span>
                    <span>$0.00</span>
                </div>
                <hr class="my-4 border-t border-pink-100" />
                <div class="mb-6 flex justify-between text-lg font-semibold text-pink-800">
                    <span>Total</span>
                    <span>$0.00</span>
                </div>
                <button disabled
                    class="w-full cursor-not-allowed rounded-md bg-pink-300 py-3 font-semibold text-white/80 opacity-90">
                    Cart is Empty
                </button>
            </aside>
        </div>
        <?php else: ?>
        <form action="cart.php" method="post">
            <div class="flex flex-col-reverse gap-8 lg:flex-row">
                <section class="flex-1">
                    <div class="bg-white rounded-2xl border border-pink-200 shadow-[0_10px_30px_rgba(236,72,153,0.06)] overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-pink-50">
                                    <tr>
                                        <td class="p-4 font-semibold text-pink-800" colspan="2">Product</td>
                                        <td class="p-4 font-semibold text-pink-800">Price</td>
                                        <td class="p-4 font-semibold text-pink-800">Quantity</td>
                                        <td class="p-4 font-semibold text-pink-800">Total</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr class="border-t border-pink-100" data-product-id="<?=$product['product_id']?>">
                                        <td class="p-4">
                                            <a href="product-detail.php?id=<?=$product['product_id']?>" class="block">
                                                <img src="../<?=str_replace('src/img/', 'img/', $product['image_path'])?>" width="80" height="80" alt="<?=$product['product_name']?>" loading="lazy" decoding="async" class="rounded-lg object-cover">
                                            </a>
                                        </td>
                                        <td class="p-4">
                                            <a href="product-detail.php?id=<?=$product['product_id']?>" class="font-semibold text-pink-800 hover:text-pink-600"><?=$product['product_name']?></a>
                                            <br>
                                            <small class="text-pink-600"><?=$product['material']?></small>
                                            <br>
                                            <a href="cart.php?remove=<?=$product['product_id']?>" class="text-red-500 hover:text-red-700 text-sm remove-item" data-product-id="<?=$product['product_id']?>">Remove</a>
                                        </td>
                                        <td class="p-4 font-semibold text-pink-800" data-unit-price="<?=htmlspecialchars($product['price'])?>"><?=format_price($product['price'])?></td>
                                        <td class="p-4">
                                            <input id="quantity-<?=$product['product_id']?>" data-product-id="<?=$product['product_id']?>" type="number" name="quantity-<?=$product['product_id']?>" value="<?=$products_in_cart[$product['product_id']]?>" min="1" max="99" placeholder="Quantity" required class="w-20 px-2 py-1 border border-pink-200 rounded text-center">
                                        </td>
                                        <td class="p-4 font-semibold text-pink-800"><span class="row-total" id="row-total-<?=$product['product_id']?>"><?=format_price($product['price'] * $products_in_cart[$product['product_id']])?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <aside class="w-full self-start rounded-xl border border-transparent bg-white p-6 shadow-lg lg:sticky lg:top-24 lg:w-96">
                    <h3 class="mb-4 text-2xl font-semibold text-pink-800">
                        Order Summary
                    </h3>
                    <div class="mb-2 flex justify-between text-pink-700/80">
                        <span>Subtotal</span>
                        <span id="cart-subtotal"><?=format_price($subtotal)?></span>
                    </div>
                    <div class="mb-4 flex justify-between text-pink-700/80">
                        <span>Estimated Shipping</span>
                        <span>$0.00</span>
                    </div>
                    <hr class="my-4 border-t border-pink-100" />
                    <div class="mb-6 flex justify-between text-lg font-semibold text-pink-800">
                        <span>Total</span>
                        <span id="cart-total"><?=format_price($subtotal)?></span>
                    </div>
                    <div class="space-y-3">
                        <input type="submit" value="Update Cart" name="update" class="w-full rounded-md bg-pink-200 py-3 font-semibold text-pink-800 hover:bg-pink-300 transition-colors">
                        <input type="submit" value="Place Order" name="placeorder" class="w-full rounded-md bg-gradient-to-r from-pink-500 to-rose-500 py-3 font-semibold text-white hover:shadow-[0_0_30px_rgba(236,72,153,0.25)] transition-all">
                    </div>
                </aside>
            </div>
        </form>
        <?php endif; ?>
    </div>
    </div>
<?php
renderFooter([
    'scripts' => [
        '<script src="https://unpkg.com/motion@latest/dist/motion.umd.js"></script>',
        '<script src="../js/main.js"></script>',
        '<script src="../js/validation-integration.js"></script>',
        '<script src="../js/auth.js"></script>',
        '<script src="../js/reveal.js"></script>',
        '<script src="../js/reviews.js"></script>'
    ]
]);
?>