<?php
require_once('../backend/connections.php');
require_once('../backend/session_check.php');

$isLoggedIn = isLoggedIn();
$product = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    header('Location: shop.php');
    exit;
}

require_once('../layouts/app.php');

renderHeader([
    'title' => $product['product_name'] . ' - Promise Shop',
    'isLoggedIn' => $isLoggedIn,
    'bodyClass' => 'flex min-h-screen flex-col bg-gradient-to-br from-pink-50 to-rose-50',
    'mainClass' => 'flex-1'
]);
?>

<section class="py-10 sm:py-12">
    <div class="mx-auto max-w-screen-xl px-4">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'out_of_stock'): ?>
            <div class="mb-6 rounded-lg border border-orange-200 bg-orange-50 p-4">
                <p class="text-orange-800">Requested quantity is not available. Please choose a smaller quantity.</p>
            </div>
        <?php endif; ?>
        <nav class="mb-6 text-sm text-neutral">
            <ol class="flex items-center gap-2">
                <li><a href="home.php" class="transition-colors text-slate-700  hover:text-pink-500">Home</a></li>
                <li class="text-neutral">/</li>
                <li><a href="shop.php" class="transition-colors text-slate-700  hover:text-pink-500">Shop</a></li>
                <li class="text-slate-700 ">/</li>
                <li class="font-semibold text-slate-700 "><?= htmlspecialchars($product['product_name']); ?></li>
            </ol>
        </nav>

        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <div class="aspect-square overflow-hidden rounded-3xl border border-pink-200 bg-white shadow-[0_10px_30px_rgba(236,72,153,0.08)]">
                    <img src="../<?= str_replace('src/img/', 'img/', $product['image_path']); ?>" alt="<?= htmlspecialchars($product['product_name']); ?>" loading="lazy" decoding="async" sizes="(min-width: 1024px) 50vw, 100vw" class="h-full w-full object-cover" />
                </div>
            </div>

            <div class="space-y-8">
                <header>
                    <h1 class="mb-4 text-4xl font-semibold text-pink-950 "><?= htmlspecialchars($product['product_name']); ?></h1>
                    <div class="mb-4 text-3xl font-bold text-slate-700 "><?= format_price($product['price']); ?></div>
                    <p class="text-lg text-slate-700 ">Material: <?= htmlspecialchars($product['material']); ?></p>
                </header>

                <?php if ($isLoggedIn): ?>
                    <form action="../backend/cart_api.php" method="post" class="space-y-6">
                        <input type="hidden" name="action" value="add">
                        <div>
                            <label for="quantity" class="mb-2 block text-sm font-medium text-neutral">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="99" required class="w-32 rounded-lg border border-pink-200 px-4 py-3 text-center focus:border-pink-5  00 focus:ring-2 focus:ring-pink-500" />
                        </div>
                        <input type="hidden" name="product_id" value="<?= (int) $product['product_id']; ?>" />
                        <button type="submit" class="btn-primary w-full justify-center py-4 text-lg">Add to cart</button>
                    </form>
                <?php else: ?>
                    <div class="space-y-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 ">Quantity</label>
                            <input type="number" value="1" min="1" max="99" disabled class="w-32 rounded-lg border border-pink-200 bg-neutral-100 px-4 py-3 text-center" />
                        </div>
                        <button type="button" onclick="openModal('login-modal')" class="btn-primary w-full justify-center py-4 text-lg">Log in to add to cart</button>
                    </div>
                <?php endif; ?>

                <section class="space-y-6 border-t text-slate-700 pt-6">
                    <div>
                        <h2 class="mb-3 text-2xl font-semibold text-slate-900 ">Description</h2>
                        <p class="text-slate-700 ><?= nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    <div>
                        <h2 class="py-5 mb-3 text-2xl font-semibold text-slate-900 ">Product details</h2>
                        <div class="space-y-2 text-slate-700 ">
                            <p><strong>Material:</strong> <?= htmlspecialchars($product['material']); ?></p>
                            <p><strong>Price:</strong> <?= format_price($product['price']); ?></p>
                            <p><strong>Date added:</strong> <?= date('F j, Y', strtotime($product['date_added'])); ?></p>
                        </div>
                    </div>
                </section>

                <div class="border-t text-slate-700  pt-6">
                    <a href="shop.php" class="inline-flex items-center text-slate-700 transition-colors hover:text-pink-800">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to shop
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

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
