<?php
require_once '../config.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    exit('Forbidden');
}

$result = $conn->query("SELECT id, name, email, created_at FROM users WHERE role = 'admin' AND status = 'pending'");

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
