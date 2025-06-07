<?php
// Database connection parameters (fallback to env variables if available)
$servername = getenv('DB_HOST') ?: "sql12.freesqldatabase.com";
$username = getenv('DB_USER') ?: "sql12783580";
$password = getenv('DB_PASS') ?: "rEYGPG5ure";
$dbname = getenv('DB_NAME') ?: "sql12783580";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allowed email domains
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Helper function to check allowed email domains
function isAllowedDomain($email, $allowed_domains)
{
    $domain = substr(strrchr($email, "@"), 1);
    return in_array($domain, $allowed_domains);
}

// Define Super Admin email (only once)
if (!defined('SUPER_ADMIN_EMAIL')) {
    define('SUPER_ADMIN_EMAIL', '202310944@gordoncollege.edu.ph');
}
