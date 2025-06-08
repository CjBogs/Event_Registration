<?php
$currentDate = date('Y-m-d');

$query = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();

$email = $_SESSION['email'] ?? '';
$name = $_SESSION['name'] ?? 'User';
$imagePath = $_SESSION['image'] ?? 'default.png';

// Group events by week start date (Monday)
$eventsByWeek = [];

while ($event = $result->fetch_assoc()) {
  $eventDate = new DateTime($event['event_date']);

  // Get the Monday of the event's week
  $weekStart = clone $eventDate;
  $weekStart->modify('Monday this week');

  // Format week start date as string key (e.g., "2025-06-02")
  $weekKey = $weekStart->format('Y-m-d');

  $eventsByWeek[$weekKey][] = $event;
}
?>

<!-- Events Section -->
<main class="p-6 overflow-y-auto flex-1 bg-[#FAF5EE]">
  <?php if (!empty($eventsByWeek)): ?>
    <?php foreach ($eventsByWeek as $weekStart => $events): ?>
      <?php
      $weekStartDate = new DateTime($weekStart);
      $weekEndDate = clone $weekStartDate;
      $weekEndDate->modify('Sunday this week');
      ?>
      <section class="mb-10">
        <h2 class="text-2xl font-bold text-[#1D503A] mb-4">
          Week of <?= $weekStartDate->format('M d, Y') ?> - <?= $weekEndDate->format('M d, Y') ?>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($events as $event): ?>
            <div class="bg-white shadow-md rounded-xl border border-gray-200 hover:shadow-lg transition duration-300 p-5 relative">
              <div class="flex items-center gap-3 mb-2">
                <div class="bg-[#1D503A] text-white p-2 rounded-full">
                  <i data-lucide="calendar-heart" class="w-5 h-5"></i>
                </div>
                <h2 class="text-lg font-semibold text-[#1D503A]"><?= htmlspecialchars($event['title']) ?></h2>
              </div>

              <p class="text-sm text-gray-700 mb-2"><?= htmlspecialchars($event['description']) ?></p>

              <div class="text-sm text-gray-600 flex items-center mb-1">
                <i data-lucide="calendar" class="w-4 h-4 mr-2 text-[#1D503A]"></i>
                <span><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></span>
              </div>

              <?php if (!empty($event['location'])): ?>
                <div class="text-sm text-gray-600 flex items-center mb-1">
                  <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-[#1D503A]"></i>
                  <span><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></span>
                </div>
              <?php endif; ?>

              <div class="text-xs text-gray-400 mt-3 flex items-center">
                <i data-lucide="user" class="w-3 h-3 mr-1"></i>
                Created by: <?= htmlspecialchars($event['created_by']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="flex flex-col items-center justify-center mt-20 text-gray-600 text-lg">
      <i data-lucide="calendar-x" class="w-8 h-8 mb-3 text-[#1D503A]"></i>
      No upcoming events available.
    </div>
  <?php endif; ?>
</main>
<script>
  lucide.createIcons();
</script>