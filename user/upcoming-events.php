<?php
// Get current date for filtering upcoming events
$currentDate = date('Y-m-d');

// Fetch upcoming events from database
$query = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user info from session
$email = $_SESSION['email'] ?? '';
$name = $_SESSION['name'] ?? 'User';
$imagePath = $_SESSION['image'] ?? 'default.png';
?>

<!-- Events Section -->
<main class="p-6 overflow-y-auto flex-1 bg-[#FAF5EE]">
  <?php if ($result->num_rows > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php while ($event = $result->fetch_assoc()): ?>
        <div class="bg-white shadow-md rounded-lg p-4 border-l-4 border-[#1D503A] hover:shadow-lg transition">
          <h2 class="text-lg font-bold text-[#1D503A]"><?= htmlspecialchars($event['title']) ?></h2>
          <p class="text-sm text-gray-700 mt-2"><?= htmlspecialchars($event['description']) ?></p>
          <p class="text-sm text-gray-600 mt-2"><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
          <?php if (!empty($event['location'])): ?>
            <p class="text-sm text-gray-600"><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
          <?php endif; ?>
          <p class="text-xs text-gray-400 mt-2">Created by: <?= htmlspecialchars($event['created_by']) ?></p>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="text-center mt-20 text-gray-600 text-lg">No upcoming events available.</div>
  <?php endif; ?>
</main>
</div>