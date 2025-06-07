<?php
// Sanitize and truncate long text
function truncate($text, $maxChars = 50)
{
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); // Prevent XSS
    if (strlen($text) > $maxChars) {
        return substr($text, 0, $maxChars) . '...';
    }
    return $text;
}

// Redirect users based on their role or super admin email
function redirectUserByRole(array $user)
{
    $role = strtolower($user['role'] ?? 'user');
    $email = strtolower($user['email'] ?? '');
    $superAdminEmail = strtolower(SUPER_ADMIN_EMAIL); // Define this constant in your config

    if ($email === $superAdminEmail || $role === 'super_admin') {
        header("Location: super_admin/super_admin_dashboard.php");
        exit();
    }

    if ($role === 'admin') {
        header("Location: admin/admin_dashboard.php");
        exit();
    }

    // Default user redirection
    header("Location: user/user-dashboard.php");
    exit();
}

// Check if current session belongs to the Super Admin
function isSuperAdmin()
{
    return isset($_SESSION['email']) && strtolower($_SESSION['email']) === strtolower(SUPER_ADMIN_EMAIL);
}

// Allowed email domains
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Helper: Check allowed email domain (case-insensitive)
function isAllowedDomain(string $email, array $allowed_domains): bool
{
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    return in_array($domain, array_map('strtolower', $allowed_domains));
}
