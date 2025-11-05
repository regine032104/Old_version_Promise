<?php
// Use the Database wrapper for a single shared PDO connection.
require_once __DIR__ . '/Database.php';

// Get the singleton instance and expose the PDO connection as $pdo for
// backwards compatibility with existing code.
$pdo = Database::getInstance()->getConnection();

// Function to connect to MySQL using PDO (for compatibility with existing code)
function pdo_connect_mysql() {
    global $pdo;
    return $pdo;
}

// Function to format price
function format_price($price) {
    return '₱' . number_format($price, 2);
}

// Template header function
function template_header($title) {
    $header = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - Promise Shop</title>
    <link rel="stylesheet" href="../output.css">
    <link href="https://fonts.googleapis.com/css2?family=Tinos:wght@400;700&family=Unna:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-pink-50 to-rose-50 min-h-screen">
    <!-- NAVBAR -->
    ' . file_get_contents('../components/navbar.html') . '
    <!-- MODAL -->
    ' . file_get_contents('../components/modal.html') . '
    <main class="mx-auto max-w-screen-xl flex-1">';
    return $header;
}

// Template footer function
function template_footer() {
    $footer = '</main>
    <!-- FOOTER -->
    ' . file_get_contents('../components/footer.html') . '
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="../js/filter.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/validation-integration.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../js/reveal.js"></script>
</body>
</html>';
    return $footer;
}
?>
