<?php
session_start();
require_once '../config.php';
require_once '../helpers.php';

// Check if user is Super Admin
if (!isSuperAdmin()) {
    header("Location: ../landing-page.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $action = $_POST['action'] ?? '';

    if ($eventId > 0 && in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';

        // Prepare update statement
        $stmt = $conn->prepare("UPDATE events SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $status, $eventId);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Event has been $status successfully.";
            } else {
                $_SESSION['message'] = "Failed to update event status. Please try again.";
            }

            $stmt->close();
        } else {
            $_SESSION['message'] = "Database error: " . $conn->error;
        }
    } else {
        $_SESSION['message'] = "Invalid event ID or action.";
    }
} else {
    $_SESSION['message'] = "Invalid request method.";
}

// Redirect back to dashboard with a hash anchor
header("Location: super_admin_dashboard.php#eventsApproval");
exit();
