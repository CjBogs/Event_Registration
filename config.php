<?php
// Toggle between 'local' and 'production'
// Use environment variables when deployed, otherwise fallback to hardcoded values
$servername = getenv('DB_HOST') ?: "sql12.freesqldatabase.com";
$username   = getenv('DB_USER') ?: "sql12783588";
$password   = getenv('DB_PASS') ?: "aK5gne4yYM";
$dbname     = getenv('DB_NAME') ?: "sql12783588";
$port       = getenv('DB_PORT') ?: 3306;

// Create connection using MySQLi with exception mode enabled
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    $conn->set_charset("utf8mb4"); // Always set charset
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection error.");
}

// Automatically close connection when script ends
register_shutdown_function(function () use (&$conn) {
    if ($conn instanceof mysqli && $conn->ping()) {
        $conn->close();
    }
});

// Allowed email domains for registration
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Define Super Admin email once
if (!defined('SUPER_ADMIN_EMAIL')) {
    define('SUPER_ADMIN_EMAIL', 'admin1@gordoncollege.edu.ph');
}
?>
