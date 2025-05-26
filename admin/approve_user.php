<?php
session_start();
require_once '../config.php'; // Updated path

if (!isset($_SESSION['email'])) {
    header("Location: ../landing-page.php");
    exit();
}

$reg_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$reg_id || !in_array($action, ['approve', 'reject'])) {
    die("Invalid request.");
}

if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE event_registrations SET approved = 1 WHERE id = ?");
} else {
    $stmt = $conn->prepare("DELETE FROM event_registrations WHERE id = ?");
}

$stmt->bind_param("i", $reg_id);
$stmt->execute();
$stmt->close();

header("Location: ../approve-user-registration.php");
exit();
