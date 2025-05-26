<?php
session_start();
require_once '../config.php'; // Use the relative path from the admin folder

// Ensure user is logged in and request is POST
if (!isset($_SESSION['email']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landing-page.php");
    exit();
}

$loggedInEmail = $_SESSION['email'];

// Sanitize form inputs
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? ''; // Optional password

// Basic validation
if (empty($first_name) || empty($last_name) || empty($email)) {
    $_SESSION['message'] = "First name, last name, and email are required.";
    $_SESSION['message_type'] = "error";
    header("Location: admin_dashboard.php");
    exit();
}

// Validate allowed email domains
$allowed_domains = ['gmail.com', 'gordoncollege.edu.ph'];
$domain = substr(strrchr($email, "@"), 1);
if (!in_array($domain, $allowed_domains)) {
    $_SESSION['message'] = "Only emails from gmail.com or gordoncollege.edu.ph are allowed.";
    $_SESSION['message_type'] = "error";
    header("Location: admin_dashboard.php");
    exit();
}

// Ensure the new email isn't used by another user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND email != ?");
$stmt->bind_param('ss', $email, $loggedInEmail);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['message'] = "This email is already used by another account.";
    $_SESSION['message_type'] = "error";
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}
$stmt->close();

// Update user data (with or without password)
if (!empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ? WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssss', $first_name, $last_name, $email, $password_hash, $loggedInEmail);
} else {
    $query = "UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE email = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $loggedInEmail);
}

if ($stmt->execute()) {
    // Update session email if it was changed
    if ($email !== $loggedInEmail) {
        $_SESSION['email'] = $email;
    }
    $_SESSION['message'] = "Profile updated successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Failed to update profile. Please try again.";
    $_SESSION['message_type'] = "error";
}

$stmt->close();
header("Location: admin_dashboard.php");
exit();
