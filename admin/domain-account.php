<?php
session_start();
require_once '../config.php';

$allowed_domain = 'gordoncollege.edu.ph'; // Only allow this domain

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check email domain
    $email_domain = substr(strrchr($email, "@"), 1);
    if ($email_domain !== $allowed_domain) {
        $_SESSION['register_error'] = 'Please use domain account';
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    // Check if email already exists
    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered';
        $_SESSION['active_form'] = 'register';
    } else {
        $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')");
    }

    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check email domain
    $email_domain = substr(strrchr($email, "@"), 1);
    if ($email_domain !== $allowed_domain) {
        $_SESSION['login_error'] = 'Please use domain account.';
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user-dashboard.php");
            }
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}
