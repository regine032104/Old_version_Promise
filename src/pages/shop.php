<?php
require_once('../backend/session_check.php');
require_once('../backend/connections.php');

$isLoggedIn = isLoggedIn();

// The amounts of products to show on each page
$num_products_on_each_page = 9;

$current_page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int) $_GET['p'] : 1;

$stmt = $pdo->prepare('SELECT * FROM products ORDER BY date_added DESC LIMIT ?,?');

$stmt->bindValue(1, ($current_page - 1) * $num_products_on_each_page, PDO::PARAM_INT);
$stmt->bindValue(2, $num_products_on_each_page, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of products
$total_products = $pdo->query('SELECT * FROM products')->rowCount();

require_once('../layouts/app.php');
renderHeader([
  'title' => 'Products - Promise Shop',
  'isLoggedIn' => $isLoggedIn,
  'bodyClass' => 'bg-white min-h-screen flex flex-col',
  'mainClass' => 'flex-1'

]);

// HERO
$title = "OUR";
$highlight = 'PRODUCTS';
$subtitle = "We Browse our curated collection of wedding attire crafted for elegance, comfort and timeless memories.";
$extra_class = "py-32";

include('../components/hero.html');
?>



<div class="mx-auto max-w-screen-xl py-10">
  <div class="flex flex-col gap-8">
    <!-- ALL PRODUCTS -->
    <h2
      class="font-Tinos text-center text-2xl leading-none tracking-widest text-pink-950 uppercase md:tracking-[0.6em] lg:tracking-[0.8em]">
      All Products
    </h2>
    <!-- Results Count -->
    <div class="w-full mb-6">
      <div class="text-center">
        <p class="text-pink-600 font-medium"><?= $total_products ?> Products Available</p>
      </div>
    </div>

    <div class="mx-auto max-w-screen-xl">
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php if (empty($products)): ?>
          <div class="col-span-full text-center py-12">
            <h3 class="font-Tinos text-2xl text-pink-800 mb-4">No products found</h3>
            <p class="text-pink-600">Check back later for new arrivals!</p>
          </div>
        <?php else: ?>
          <?php foreach ($products as $product): ?>
            <article
              class="product-card flex flex-col overflow-hidden rounded-lg border border-pink-200 bg-white text-pink-950 shadow-[0_8px_30px_rgba(236,72,153,0.08)]">
              <div class="relative h-56 overflow-hidden sm:h-64 md:h-72 lg:h-80">
                <a href="product-detail.php?id=<?= $product['product_id'] ?>">
                  <img src="../<?= str_replace('src/img/', 'img/', $product['image_path']) ?>"
                    alt="<?= htmlspecialchars($product['product_name']) ?>" loading="lazy" decoding="async"
                    sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                    class="h-full w-full object-cover hover:scale-105 transition-transform duration-300">
                </a>
                <button
                  class="absolute top-3 right-3 rounded-full bg-white/80 p-2 text-pink-600 shadow hover:bg-white transition-colors">
                  ♡
                </button>
              </div>
              <div class="flex flex-1 flex-col justify-between p-4">
                <div>
                  <h3 class="font-semibold text-pink-700 mb-2">
                    <a href="product-detail.php?id=<?= $product['product_id'] ?>"
                      class="hover:text-pink-800 transition-colors">
                      <?= htmlspecialchars($product['product_name']) ?>
                    </a>
                  </h3>
                  <p class="mt-1 text-pink-600 text-sm mb-2">
                    <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...
                  </p>
                  <p class="text-pink-500 text-xs">
                    Material: <?= htmlspecialchars($product['material']) ?>
                  </p>
                </div>
                <div class="mt-4 flex items-center justify-between">
                  <div class="font-bold text-pink-700"><?= format_price($product['price']) ?></div>
                  <?php if ($isLoggedIn): ?>
                    <form action="../backend/cart_api.php" method="post" class="inline">
                      <input type="hidden" name="action" value="add">
                      <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit"
                        class="cursor-pointer rounded-md bg-gradient-to-r from-pink-500 to-rose-500 px-4 py-2 text-white transition hover:shadow-[0_0_30px_rgba(236,72,153,0.25)] hover:scale-105">
                        Add to Cart
                      </button>
                    </form>
                  <?php else: ?>
                    <button type="button" onclick="openModal('login-modal')"
                      class="cursor-pointer rounded-md bg-gradient-to-r from-pink-500 to-rose-500 px-4 py-2 text-white transition hover:shadow-[0_0_30px_rgba(236,72,153,0.25)] hover:scale-105">
                      Add to Cart
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($total_products > $num_products_on_each_page): ?>
        <div class="flex justify-center items-center mt-12 space-x-4">
          <?php if ($current_page > 1): ?>
            <a href="shop.php?p=<?= $current_page - 1 ?>"
              class="px-4 py-2 bg-pink-200 text-pink-800 rounded-lg hover:bg-pink-300 transition-colors">
              Previous
            </a>
          <?php endif; ?>

          <span class="px-4 py-2 bg-pink-100 text-pink-800 rounded-lg">
            Page <?= $current_page ?> of <?= ceil($total_products / $num_products_on_each_page) ?>
          </span>

          <?php if ($total_products > ($current_page * $num_products_on_each_page)): ?>
            <a href="shop.php?p=<?= $current_page + 1 ?>"
              class="px-4 py-2 bg-pink-200 text-pink-800 rounded-lg hover:bg-pink-300 transition-colors">
              Next
            </a>
          <?php endif; ?>
        </div>
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