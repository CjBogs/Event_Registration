<?php
require_once '../config.php';

if (!isset($_SESSION['email'])) {
  header('Location: ../landing-page.php');
  exit();
}

$email = $_SESSION['email'] ?? '';

// Fetch user's first and last name
$stmtName = $conn->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
$stmtName->bind_param("s", $email);
$stmtName->execute();
$resultName = $stmtName->get_result();
$name = '';
if ($resultName && $resultName->num_rows > 0) {
  $row = $resultName->fetch_assoc();
  $name = $row['first_name'] . ' ' . $row['last_name'];
}

// Fetch user's registered events with pending or approved status
$stmt = $conn->prepare("
    SELECT e.id, e.title, e.description, e.event_date, er.status
    FROM event_registrations er
    JOIN events e ON er.event_id = e.id
    WHERE er.user_email = ? AND er.status IN ('pending', 'approved')
    ORDER BY e.event_date ASC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$events = $stmt->get_result();
?>

<div x-data="{ showModal: false, selectedEvent: {} }" class="pt-2 px-6 pb-6">

  <!-- Wrapper flex container to center -->
  <div class="max-w-4xl mx-auto">
    <div class="text-center mb-6">
      <h2 class="text-xl font-semibold" style="color: #1D503A;">Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
      <?php if (!empty($events)): ?>
        <p class="text-lg text-[#4A5D4C] border-b-2 pb-2" style="border-color: #1D503A;">Here are your event registrations.</p>
      <?php else: ?>
        <p class="text-lg text-[#4A5D4C] border-b-2 pb-2" style="border-color: #1D503A;">You have no event registrations yet.</p>
      <?php endif; ?>
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow border" style="border-color: #1D503A;">
      <table class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-100 border-b text-xs text-gray-500 uppercase sticky top-0 z-10">
          <tr>
            <th class="px-6 py-4">Title</th>
            <th class="px-6 py-4">Date</th>
            <th class="px-6 py-4">Description</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-center">View</th>
          </tr>
        </thead>
      </table>
      <div style="max-height: 360px; overflow-y: auto;">
        <table class="min-w-full text-sm text-left text-gray-700">
          <tbody>
            <?php if ($events->num_rows > 0): ?>
              <?php while ($event = $events->fetch_assoc()):
                $statusMessage = $event['status'] === 'approved' ? 'Approved' : 'Pending Approval';
                $statusColor = $event['status'] === 'approved' ? 'text-green-800' : 'text-yellow-800';
              ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($event['title']) ?></td>
                  <td class="px-6 py-4"><?= htmlspecialchars($event['event_date']) ?></td>
                  <td class="px-6 py-4 max-w-xs overflow-hidden text-ellipsis whitespace-nowrap" title="<?= htmlspecialchars($event['description']) ?>"><?= htmlspecialchars($event['description']) ?></td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusColor ?>"><?= $statusMessage ?></span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <button
                      @click="selectedEvent = { 
                    title: '<?= addslashes($event['title']) ?>', 
                    date: '<?= addslashes($event['event_date']) ?>', 
                    description: `<?= addslashes($event['description']) ?>` 
                  }; showModal = true"
                      class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-500">
                      View
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center px-6 py-6 text-gray-500">No pending or approved events found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div
      x-show="showModal"
      x-cloak
      tabindex="-1"
      role="dialog"
      aria-modal="true"
      aria-labelledby="viewModalTitle"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
      style="backdrop-filter: blur(4px);">
      <div
        @click.outside="showModal = false"
        @keydown.escape.window="showModal = false"
        class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6 overflow-auto max-h-[90vh]">
        <h3 id="viewModalTitle" class="text-xl font-bold text-[#1D503A] mb-4">Event Details</h3>

        <div class="mb-4">
          <label class="block text-sm font-semibold text-[#1D503A] mb-1">Title:</label>
          <p class="text-gray-800" x-text="selectedEvent.title"></p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-semibold text-[#1D503A] mb-1">Date:</label>
          <p class="text-gray-800" x-text="selectedEvent.date"></p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-semibold text-[#1D503A] mb-1">Description:</label>
          <p class="text-gray-800 whitespace-pre-wrap" x-text="selectedEvent.description"></p>
        </div>

        <div class="text-right pt-2">
          <button
            @click="showModal = false"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>