<?php
/**
 * Shared layout helpers for Promise Shop frontend pages.
 */

if (!function_exists('renderHeader')) {
    function renderHeader(array $options = []): void
    {
        $title = $options['title'] ?? 'Promise Shop';
        $isLoggedIn = $options['isLoggedIn'] ?? false;
        $showModal = $options['showModal'] ?? !$isLoggedIn;
        $bodyClass = $options['bodyClass'] ?? 'min-h-screen bg-white flex flex-col';
        $mainClass = $options['mainClass'] ?? 'flex-1';
        $additionalHead = $options['head'] ?? '';
        ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="../output.css" />
    <?= $additionalHead ?>
</head>

<body class="<?= $bodyClass ?>">
    <?php include('../components/navbar.html'); ?>
    <?php if ($showModal) { include('../components/modal.html'); } ?>
    <main class="<?= $mainClass ?>">
        <?php
    }
}

if (!function_exists('renderFooter')) {
    function renderFooter(array $options = []): void
    {
        $extraScripts = $options['scripts'] ?? [];
        ?>
    </main>
    <?php include('../components/footer.html'); ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="../js/cart.js"></script>
    <script src="../validation/register-validation.js"></script>
    <script src="../validation/login-validation.js"></script>
    <?php
        foreach ($extraScripts as $scriptTag) {
            echo $scriptTag, "\n";
        }
        ?>
</body>

</html>
<?php
    }
}
