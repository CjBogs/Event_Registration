<?php
session_start();
require_once '../config.php';

// Check user session and POST data
if (!isset($_SESSION['email']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header("Location: ../landing-page.php");
    exit();
}

$email = $_SESSION['email'];
$eventId = intval($_POST['id']);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';

// Basic validation
if ($eventId <= 0 || empty($title) || empty($description) || empty($event_date)) {
    $_SESSION['message'] = "Please fill in all required fields.";
    $_SESSION['message_type'] = "error";
    header("Location: events.php");
    exit();
}

// Use prepared statement to prevent SQL injection
$query = "UPDATE events SET title = ?, description = ?, event_date = ? WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('sssis', $title, $description, $event_date, $eventId, $email);

if ($stmt->execute()) {
    $_SESSION['message'] = "Event updated successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Failed to update event. Please try again.";
    $_SESSION['message_type'] = "error";
}

$stmt->close();

header("Location: events.php");
exit();
