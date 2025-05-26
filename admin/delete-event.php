<?php
require '../config.php';
session_start();

// Validate session and POST request
if (!isset($_SESSION['email']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php");
    exit();
}

$email = $_SESSION['email'];
$eventId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($eventId <= 0) {
    $_SESSION['message'] = "Invalid event ID.";
    header("Location: admin_dashboard.php");
    exit();
}

// Prepare delete query: only delete if the logged-in admin created the event
$stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND created_by = ?");
$stmt->bind_param("is", $eventId, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['message'] = "Event deleted successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Event not found or you don't have permission to delete this event.";
    $_SESSION['message_type'] = "error";
}

$stmt->close();

header("Location: admin_dashboard.php");
exit();
