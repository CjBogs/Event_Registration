<?php
// Read DB credentials from environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'users_db';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allowed email domains (modify as needed)
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Helper function
function isAllowedDomains($email, $allowed_domains)
{
    $domain = substr(strrchr($email, "@"), 1);
    return in_array($domain, $allowed_domains);
}

// Define the Super Admin email if not already defined
if (!defined('SUPER_ADMIN_EMAIL')) {
    define('SUPER_ADMIN_EMAIL', '202310944@gordoncollege.edu.ph');
}
