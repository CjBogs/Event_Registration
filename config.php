<?php
// Database connection parameters (fallback to env variables if available)
$servername = getenv('DB_HOST') ?: "sql12.freesqldatabase.com";
$username = getenv('DB_USER') ?: "sql12783588";
$password = getenv('DB_PASS') ?: "aK5gne4yYM";
$dbname = getenv('DB_NAME') ?: "sql12783588";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allowed email domains (optional, for global usage)
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Define Super Admin email (only once)
if (!defined('SUPER_ADMIN_EMAIL')) {
    define('SUPER_ADMIN_EMAIL', '202310944@gordoncollege.edu.ph');
}
