<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $conn->query("UPDATE users SET approved = 1 WHERE id = $user_id");
    } elseif ($action === 'reject') {
        $conn->query("UPDATE users SET approved = 2 WHERE id = $user_id");
    }
}

header("Location: ../admin_dashboard.php");
exit();
