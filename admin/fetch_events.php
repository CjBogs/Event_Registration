<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['email'])) {
    header("Location: ../landing-page.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch all events created by this admin
$stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ? ORDER BY event_date DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$eventsResult = $stmt->get_result();

// Display events
if ($eventsResult->num_rows > 0) {
    while ($event = $eventsResult->fetch_assoc()) {
?>
        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($event['title']); ?></h3>
            <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($event['event_date']); ?></p>
            <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($event['description']); ?></p>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                <form action="delete-event.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                </form>
            </div>
        </div>
<?php
    }
} else {
    echo '<p class="text-center text-gray-500">No events created yet.</p>';
}
?>