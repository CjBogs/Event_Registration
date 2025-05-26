<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once('../config.php');

// Get user info from session
$name  = $_SESSION['name'] ?? '';
$email = $_SESSION['email'] ?? '';

// Get profile image
$imagePath = 'default.png';
if ($email) {
  $query = "SELECT profile_image FROM users WHERE email = ?";
  if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();
  }
}

// Fetch approved events
$events = $conn->query("SELECT id, title, description, event_date FROM events WHERE status = 'approved' ORDER BY event_date ASC");

// Success message flag
$registrationMessage = ($_GET['registered'] ?? '') === 'true'
  ? 'Your registration has been sent! Please wait for admin approval. Thank you!'
  : '';
?>

<section
  x-data="{
    showSuccessModal: <?= $registrationMessage ? 'true' : 'false' ?>,
    showRegisterForm: false,
    showModal: false,
    selectedEvent: {},
    course: '',
    year: '',
    block: ''
  }"
  x-init="
    if(showSuccessModal) {
      setTimeout(() => {
        showSuccessModal = false;
        // Remove 'registered' from URL so modal won't show on reload
        const url = new URL(window.location);
        url.searchParams.delete('registered');
        window.history.replaceState({}, document.title, url);
      }, 4000)
    }
  "
  class="pt-2 md:pt-2 px-6 md:px-10 mt-0 max-w-6xl mx-auto">
  <h2 class="text-2xl font-semibold mb-6 text-[#1D503A]">Available Events</h2>

  <!-- Success Message Modal -->
  <div
    x-show="showSuccessModal"
    x-transition
    @click.away="showSuccessModal = false"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    style="display: none;">
    <div
      @click.stop
      class="relative bg-green-100 text-green-800 px-6 py-4 rounded shadow-lg max-w-md text-center font-semibold">

      <?= htmlspecialchars($registrationMessage) ?>

      <!-- Close button -->
      <button
        @click="showSuccessModal = false"
        class="absolute top-2 right-2 text-green-800 hover:text-green-900 font-bold text-xl leading-none focus:outline-none"
        aria-label="Close modal"
        title="Close">
        &times;
      </button>
    </div>
  </div>
  <div class="overflow-x-auto bg-white rounded-xl shadow border" style="border-color: #1D503A;">
    <table class="min-w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100 border-b text-xs text-gray-500 uppercase">
        <tr>
          <th class="px-6 py-4">Title</th>
          <th class="px-6 py-4">Date</th>
          <th class="px-6 py-4">Description</th>
          <th class="px-6 py-4">Status</th>
          <th class="px-6 py-4 text-center">Actions</th>
        </tr>
      </thead>
    </table>
    <div style="max-height: 360px; overflow-y: auto;">
      <table class="min-w-full text-sm text-left text-gray-700">
        <tbody>
          <?php if ($events && $events->num_rows > 0): ?>
            <?php while ($event = $events->fetch_assoc()): ?>
              <?php
              $statusMessage = '';
              $stmt = $conn->prepare("SELECT status FROM event_registrations WHERE user_email = ? AND event_id = ?");
              $stmt->bind_param('si', $email, $event['id']);
              $stmt->execute();
              $stmt->bind_result($status);
              if ($stmt->fetch()) {
                $statusMessage = match ($status) {
                  'approved' => 'Approved',
                  'rejected' => 'Rejected',
                  default     => 'Pending Approval',
                };
              }
              $stmt->close();
              ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($event['title']) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($event['event_date']) ?></td>
                <td class="px-6 py-4 max-w-xs overflow-hidden text-ellipsis whitespace-nowrap"><?= htmlspecialchars($event['description']) ?></td>
                <td class="px-6 py-4">
                  <?php if ($statusMessage): ?>
                    <?php
                    $statusColor = match ($statusMessage) {
                      'Approved' => 'text-green-800',
                      'Rejected' => 'text-red-800',
                      default    => 'text-yellow-800',
                    };
                    ?>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $statusColor ?>">
                      <?= $statusMessage ?>
                    </span>
                  <?php else: ?>
                    <span class="text-gray-400 italic text-xs">Not Registered</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center space-x-2">
                    <?php if (!$statusMessage): ?>
                      <button
                        @click="selectedEvent = { id: <?= $event['id'] ?>, title: '<?= addslashes($event['title']) ?>', date: '<?= addslashes($event['event_date']) ?>', description: `<?= addslashes($event['description']) ?>` }; showRegisterForm = true"
                        class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-500">
                        Register
                      </button>
                    <?php endif; ?>
                    <button
                      @click="selectedEvent = { title: '<?= addslashes($event['title']) ?>', date: '<?= addslashes($event['event_date']) ?>', description: `<?= addslashes($event['description']) ?>` }; showModal = true"
                      class="text-sm text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded">
                      View
                    </button>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center px-6 py-6 text-gray-500">No events available.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Registration Modal -->
    <div
      x-show="showRegisterForm"
      x-transition
      @click.away="showRegisterForm = false"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      style="display: none;">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Register for Event</h3>
        <form action="register-event.php" method="POST">
          <input type="hidden" name="event_id" :value="selectedEvent.id">
          <div class="mb-4">
            <label for="course" class="block text-gray-600">Course</label>
            <input
              type="text"
              name="course"
              x-model="course"
              placeholder="e.g., BIST"
              required
              class="w-full px-4 py-2 mt-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500" />
          </div>
          <div class="mb-4">
            <label for="year" class="block text-gray-600">Year Level</label>
            <input
              type="text"
              name="year"
              x-model="year"
              placeholder="e.g., 1st Year"
              required
              class="w-full px-4 py-2 mt-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500" />
          </div>
          <div class="mb-4">
            <label for="block" class="block text-gray-600">Block</label>
            <input
              type="text"
              name="block"
              x-model="block"
              placeholder="e.g., D"
              required
              class="w-full px-4 py-2 mt-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500" />
          </div>
          <div class="flex justify-between space-x-2">
            <button
              type="button"
              @click="showRegisterForm = false"
              class="w-1/2 py-2 bg-gray-300 text-white rounded-md hover:bg-gray-400">
              Cancel
            </button>
            <button
              type="submit"
              class="w-1/2 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500">
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div
      x-show="showModal"
      x-transition
      @click.away="showModal = false"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      style="display: none;">
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-2" x-text="selectedEvent.title"></h3>
        <p class="text-sm text-gray-600 mb-1"><strong>Date:</strong> <span x-text="selectedEvent.date"></span></p>
        <p class="text-sm text-gray-700 mt-4" x-text="selectedEvent.description"></p>
        <div class="mt-6 text-right">
          <button
            @click="showModal = false"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            Close
          </button>
        </div>
      </div>
    </div>
</section>