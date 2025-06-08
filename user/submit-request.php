<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input (basic trimming)
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date  = trim($_POST['event_date'] ?? '');
    $course      = trim($_POST['course'] ?? '');
    $year        = trim($_POST['year'] ?? '');
    $block       = trim($_POST['block'] ?? '');
    $email       = $_SESSION['email'] ?? '';

    $uploadDir = '../uploads/requests/';
    $filePath = '';

    // Create upload directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // Validate and move uploaded file
    if (isset($_FILES['request_file']) && $_FILES['request_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['request_file']['tmp_name'];
        $fileName    = basename($_FILES['request_file']['name']);
        $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($fileExt, $allowedExts)) {
            die("Invalid file type. Allowed: PDF, JPG, PNG.");
        }

        // Optionally check file size here if needed
        // if ($_FILES['request_file']['size'] > 2 * 1024 * 1024) { die("File too large."); }

        $uniqueName = uniqid('request_', true) . '.' . $fileExt;
        $filePath = $uploadDir . $uniqueName;

        if (!move_uploaded_file($fileTmpPath, $filePath)) {
            die("Failed to move uploaded file.");
        }
    } else {
        die("File upload error.");
    }

    // Basic validation before insert
    if ($title && $description && $event_date && $email && $filePath) {
        // Store relative path (web accessible)
        $relativePath = 'uploads/requests/' . basename($filePath);

        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, user_email, course, year, block, request_form_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param('ssssssss', $title, $description, $event_date, $email, $course, $year, $block, $relativePath);

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        $stmt->close();

        // After successful submission:
        $_SESSION['flash_success'] = "Your event request has been sent and is awaiting admin approval.";

        // Redirect to clean URL (no ?success param)
        header("Location: user-dashboard.php#requestEvent");
        exit();
    } else {
        echo "Missing fields or upload failed.";
    }
} else {
    // Redirect if not POST
    header("Location: user-dashboard.php#request-event");
    exit();
}
