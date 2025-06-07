<?php
session_start();
require_once 'config.php';
require_once 'helpers.php'; // Defines redirectUserByRole() and SUPER_ADMIN_EMAIL

$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];

// Helper: Check allowed email domain (case-insensitive)
function isAllowedDomain(string $email, array $allowed_domains): bool
{
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    return in_array($domain, array_map('strtolower', $allowed_domains));
}

// Helper: Redirect with error and store which form was active
function redirectWithError(string $formType, string $errorMessage)
{
    $_SESSION[$formType . '_error'] = $errorMessage;
    $_SESSION['active_form'] = $formType;
    header("Location: landing-page.php");
    exit();
}

// ---------- LOGIN ----------
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check Terms & Conditions checkbox
    if (empty($_POST['terms'])) {
        redirectWithError('login', 'You must agree to the Terms and Conditions before logging in.');
    }

    if (!$email || !$password) {
        redirectWithError('login', 'Please enter email and password.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !isAllowedDomain($email, $allowed_domains)) {
        redirectWithError('login', 'Only Gmail or Gordon College emails are allowed.');
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        $stmt->close();
        redirectWithError('login', 'Database error during login.');
    }

    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $stmt->close();

        if (password_verify($password, $user['password'])) {
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
            $_SESSION['role'] = strtolower($user['role']);
            $_SESSION['status'] = strtolower(trim($user['status'] ?? ''));

            // Redirect user based on role
            redirectUserByRole($user);
            exit();
        } else {
            redirectWithError('login', 'Incorrect password.');
        }
    } else {
        $stmt->close();
        redirectWithError('login', 'Account not found.');
    }
}
