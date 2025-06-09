<?php
// Prevent reconnecting multiple times
if (!isset($GLOBALS['conn'])) {
    $environment = 'production'; // 'local' or 'production'

    if ($environment === 'local') {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "users_db";
        $port = 3306;
    } else {
        $servername = getenv('DB_HOST') ?: "sql12.freesqldatabase.com";
        $username = getenv('DB_USER') ?: "sql12783588";
        $password = getenv('DB_PASS') ?: "aK5gne4yYM";
        $dbname = getenv('DB_NAME') ?: "sql12783588";
        $port = 3306;
    }

    // Try to establish the connection
    $GLOBALS['conn'] = new mysqli($servername, $username, $password, $dbname, $port);

    if ($GLOBALS['conn']->connect_error) {
        error_log("Database connection failed: " . $GLOBALS['conn']->connect_error);
        die("Database connection error. Please try again later.");
    }

    // Close on shutdown
    register_shutdown_function(function () {
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            $GLOBALS['conn']->close();
        }
    });
}

// Alias for easy use
$conn = $GLOBALS['conn'];

// Allowed email domains
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Super admin
if (!defined('SUPER_ADMIN_EMAIL')) {
    define('SUPER_ADMIN_EMAIL', 'admin1@gordoncollege.edu.ph');
}
?>
