<?php
// Database configuration and PDO connection
// Assumptions:
// - Running on local XAMPP: host=127.0.0.1, user=root, empty password
// - Database name from sql dump is `cenro_nasipit`

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'cenro_nasipit');
define('DB_USER', 'root');
define('DB_PASS', '');

// BASE_URL used by some auth pages. Adjust if your site is not at /prototype
if (!defined('BASE_URL')) {
    define('BASE_URL', '/prototype');
}

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed. Please check your configuration.']));
}

?>
