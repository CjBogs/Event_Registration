<?php
require_once '../config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userId = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

header("Location: ../approve-user-registration.php");
exit();
