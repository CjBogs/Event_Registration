<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['email']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../landing-page.php");
    exit();
}

$loggedInEmail = $_SESSION['email'];

// Sanitize input
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email)) {
    $_SESSION['message'] = "First name, last name, and email are required.";
    $_SESSION['message_type'] = "error";
    header("Location: ../user/user-dashboard.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Invalid email format.";
    $_SESSION['message_type'] = "error";
    header("Location: ../user/user-dashboard.php");
    exit();
}

if (!isAllowedDomains($email, $allowed_domains)) {
    $_SESSION['message'] = "Only emails from gmail.com or gordoncollege.edu.ph are allowed.";
    $_SESSION['message_type'] = "error";
    header("Location: ../user/user-dashboard.php");
    exit();
}

// Check if email already exists for another account
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND email != ?");
$stmt->bind_param("ss", $email, $loggedInEmail);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['message'] = "This email is already taken.";
    $_SESSION['message_type'] = "error";
    $stmt->close();
    header("Location: ../user/user-dashboard.php");
    exit();
}
$stmt->close();

// Update query
if (!empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssss', $first_name, $last_name, $email, $password_hash, $loggedInEmail);
} else {
    $query = "UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $loggedInEmail);
}

if ($stmt->execute()) {
    $_SESSION['email'] = $email;
    $_SESSION['message'] = "Profile updated successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Update failed. Please try again.";
    $_SESSION['message_type'] = "error";
}

$stmt->close();
header("Location: ../user/user-dashboard.php");
exit();
