<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../landing-page.php");
    exit();
}

$email = $_SESSION['email'];
$eventId = $_POST['event_id'] ?? null;
$course = $_POST['course'] ?? '';
$yearLevel = $_POST['year'] ?? '';
$block = $_POST['block'] ?? '';

// Validate event ID
if (!$eventId || !filter_var($eventId, FILTER_VALIDATE_INT)) {
    header("Location: user-dashboard.php?message=invalid_event_id#userEvents");
    exit();
}

include('../config.php');

// Check if already registered
$query = "SELECT 1 FROM event_registrations WHERE user_email = ? AND event_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    header("Location: user-dashboard.php?message=sql_error#userEvents");
    exit();
}
$stmt->bind_param('si', $email, $eventId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: user-dashboard.php?message=already_registered#userEvents");
    exit();
}
$stmt->close();

// Insert registration WITHOUT status column (or with 'approved' if you want)
$query = "INSERT INTO event_registrations (user_email, event_id, course, year, block, status) VALUES (?, ?, ?, ?, ?, 'approved')";
$stmt = $conn->prepare($query);
if (!$stmt) {
    header("Location: user-dashboard.php?message=sql_error#userEvents");
    exit();
}
$stmt->bind_param('sisss', $email, $eventId, $course, $yearLevel, $block);

if ($stmt->execute()) {
    $_SESSION['flash_registration'] = "You have successfully registered for the event!";
    header("Location: user-dashboard.php#userEvents");
    exit();
} else {
    header("Location: user-dashboard.php?message=error#userEvents");
}

$stmt->close();
$conn->close();
exit();
