<?php
session_start();
require_once '../config.php';

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../landing-page.php");
    exit();
}

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_id = isset($_POST['registration_id']) ? (int) $_POST['registration_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($reg_id > 0 && in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';

        $stmt = $conn->prepare("UPDATE event_registrations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $reg_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "User has been {$status}.";
        } else {
            $_SESSION['error'] = "Failed to update registration status.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid registration ID or action.";
    }
}

// Redirect back to the approval page
header("Location: approve-user-registration.php");
exit();
