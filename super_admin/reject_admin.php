<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check super admin authentication
if (!isset($_SESSION['email']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

// Validate user_id parameter
if (!isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing user ID']);
    exit();
}

$user_id = intval($_POST['user_id']);

// Prepare and execute update query to reject user
$stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'admin' AND status = 'pending'");
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No matching pending admin found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
exit();
