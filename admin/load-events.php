<?php
session_start();
require_once '../config.php';

$email = $_SESSION['email'] ?? '';

if ($email) {
    // Fetch all events created by the admin
    $stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $eventsQuery = $stmt->get_result();

    if ($eventsQuery->num_rows > 0) {
        while ($event = $eventsQuery->fetch_assoc()) {
            echo '<div class="border-b pb-4 mb-4">';
            echo '<h3 class="text-lg font-semibold">' . htmlspecialchars($event['title']) . ' — ' . htmlspecialchars($event['event_date']) . '</h3>';
            echo '<p class="text-sm mb-2">' . htmlspecialchars($event['description']) . '</p>';
            echo '<div class="flex gap-2">';
            echo '<a href="edit-event.php?id=' . $event['id'] . '" class="text-blue-600 hover:underline text-sm">Edit</a>';
            echo '<form action="delete-event.php" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this event?\');">';
            echo '<input type="hidden" name="id" value="' . $event['id'] . '">';
            echo '<button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No events found.</p>';
    }

    $stmt->close();
}

$conn->close();
