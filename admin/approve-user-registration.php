<?php
session_start();
require_once '../config.php'; // Adjust path if needed

if (!isset($_SESSION['email'])) {
  header("Location: ../landing-page.php");
  exit();
}

$name = $_SESSION['name'] ?? 'Admin';
$email = $_SESSION['email'];

// Fetch profile image
$query = "SELECT profile_image FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($imagePath);
$stmt->fetch();
$stmt->close();
$imagePath = $imagePath ?: 'default.png';

// Count pending registrations for badge
$countStmt = $conn->prepare("SELECT COUNT(*) FROM event_registrations er
    JOIN events e ON er.event_id = e.id
    WHERE e.created_by = ? AND er.status = 'pending'");
$countStmt->bind_param("s", $email);
$countStmt->execute();
$countStmt->bind_result($pendingCount);
$countStmt->fetch();
$countStmt->close();

// Fetch all registrations for admin-created events
$stmt = $conn->prepare("SELECT 
                            er.id, 
                            CONCAT(u.first_name, ' ', u.last_name) AS name, 
                            u.email, 
                            CONCAT(er.course, ' ', er.year) AS course_year, 
                            er.block, 
                            e.title AS event_name, 
                            er.status
                        FROM event_registrations er
                        JOIN users u ON er.user_email = u.email
                        JOIN events e ON er.event_id = e.id
                        WHERE e.created_by = ?
                        ORDER BY er.id DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$registrations = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }" class="h-full">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - Approvals</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
    .btn-primary {
      background-color: #1D503A;
      color: #FAF5EE;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #15412B;
    }

    .sidebar-link:hover {
      background-color: #E5E1DB;
      color: #1D503A;
    }

    input:focus,
    textarea:focus {
      outline: none;
      box-shadow: 0 0 0 3px #1D503A;
      border-color: #1D503A;
    }

    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: #1D503A;
      border-radius: 4px;
    }

    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="flex h-screen text-gray-800 font-sans" style="background-color: #FAF5EE;">

<!-- Sidebar -->
  <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed z-30 inset-y-0 left-0 w-64 transition duration-300 transform border-r md:translate-x-0 md:static md:inset-0 flex flex-col justify-between py-6 sidebar-shadow-right"
    style="background-color: #FAF5EE; border-color: #1D503A;">
   <div class="absolute top-2 right-2 md:hidden p-2">
      <button @click="sidebarOpen = false" aria-label="Close sidebar" class="text-2xl font-bold text-[#1D503A] hover:text-[#15412B] focus:outline-none">
        &times;
      </button>
    </div>

    <!-- Logo Section -->
    <div class="flex flex-col items-center border-b pb-4 px-6" style="border-color: #1D503A;">
      <a href="https://gordoncollege.edu.ph/w3/">
        <img src="../Images/gclogo.png" alt="Gordon Logo" class="w-32 h-auto mb-2" />
      </a>
      <p class="text-center font-semibold text-[#1D503A] text-sm">Gordon College</p>
    </div>

    <nav class="px-6 flex flex-col space-y-3 mt-6">
      <a href="admin_dashboard.php" class="block px-4 py-2 rounded font-semibold">Home</a>
      <a href="events.php" class="block px-4 py-2 rounded sidebar-link text-[#1D503A] font-medium"  >Events</a>
      <a href="approve-user-registration.php" class="block px-4 py-2 rounded sidebar-link text-[#1D503A] font-medium relative" style="background-color: #1D503A; color: #FAF5EE;">
        Registration Approvals
        <?php if ($pendingCount > 0): ?>
          <span class="absolute -top-2 -right-2 text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-semibold"><?php echo $pendingCount; ?></span>
        <?php endif; ?>
      </a>
    </nav>

    <div class="px-6 mt-auto">
      ABOUT THIS APP NA MAANGAS
      <div class="border-t border-gray-200 my-4" style="border-color: #1D503A;"></div>
      <div class="flex flex-col items-center space-y-4">
        <p class="text-sm text-[#1D503A] text-center break-words"><?= htmlspecialchars($email) ?></p>
      </div>
    </div>
  </div>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col overflow-hidden">

    <!-- Top Navbar -->
    <header class="px-6 py-4 flex justify-between items-center shadow md:pl-6" style="background-color: #1D503A; color: #FAF5EE;">
      <button @click="sidebarOpen = !sidebarOpen" class="md:hidden focus:outline-none" aria-label="Toggle sidebar menu">
        <svg class="w-6 h-6 text-[#FAF5EE]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <h1 class="text-lg font-semibold">Admin Dashboard</h1>

 <div x-data="{ open: false }" class="relative flex items-center space-x-3">
  <!-- Profile Image -->
  <img src="../uploads/<?php echo htmlspecialchars($imagePath); ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-white shadow object-cover hover:scale-110 transition duration-300 ease-in-out" />

  <!-- Name & Dropdown -->
  <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none" aria-haspopup="true" :aria-expanded="open.toString()">
    <span class="text-sm font-medium"><?php echo htmlspecialchars($name); ?></span>
  </button>

 <!-- Alpine.js Wrapper -->
<div x-data="{ open: false, showProfileModal: false }" class="relative">

  <!-- Dropdown Trigger (icon or simple avatar) -->
  <button @click="open = !open" class="w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center hover:bg-green-700 focus:outline-none">
    <!-- Replace with an avatar image or icon if desired -->
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A4 4 0 0112 16h0a4 4 0 016.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  </button>

  <!-- Dropdown Menu -->
  <div 
    x-show="open" 
    @click.away="open = false" 
    x-transition 
    class="absolute right-0 mt-2 w-32 bg-white border rounded shadow text-gray-800 z-20"
  >
    <button 
      @click="showProfileModal = true; open = false" 
      class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
    >
      Profile
    </button>
    <a 
      href="../logout.php" 
      class="block px-4 py-2 text-sm hover:bg-gray-100"
    >
      Logout
    </a>
  </div>

<!-- Profile Modal -->
<div 
  x-show="showProfileModal" 
  x-transition 
  class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center px-4 overflow-auto"
>
  <div 
    @click.away="showProfileModal = false" 
    class="rounded-2xl shadow-2xl w-full max-w-md relative p-8 sm:p-10" 
    style="background-color: #E5E1DB; color: #1D503A;"
  >
    <button 
      @click="showProfileModal = false" 
      class="absolute top-4 right-5 text-[#1D503A] hover:text-[#144124] text-2xl"
    >&times;</button>

    <h2 class="text-2xl font-bold mb-6 text-center">Edit Profile</h2>

    <!-- Profile Image Upload -->
    <form action="../upload-image.php" method="POST" enctype="multipart/form-data" class="w-full text-center mb-6">
      <input 
        type="file" 
        name="profile_image" 
        accept="image/*" 
        class="block w-full text-sm text-[#1D503A] mb-2 file:mr-2 file:py-1 file:px-3 file:rounded file:border file:text-sm file:bg-[#C9C3B9] file:text-[#1D503A] hover:file:bg-[#B0A998]" 
      />
      <button 
        type="submit" 
        class="w-full px-4 py-2 text-sm font-semibold rounded-full bg-white text-red-500 border border-red-300 hover:bg-gray-200 transition"
      >
        Upload
      </button>
    </form>

    <!-- Profile Info Update -->
    <form action="../admin/update-profile.php" method="POST" class="mb-6">
      <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1 text-[#1D503A]">First Name</label>
          <input 
            type="text" 
            name="first_name" 
            value="<?= htmlspecialchars($admin['first_name'] ?? '') ?>" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-[#1D503A] focus:outline-none focus:ring-2 focus:ring-[#1D503A]" 
            placeholder="First Name"
            required
          >
        </div>
        <div>
          <label class="block text-sm font-medium mb-1 text-[#1D503A]">Last Name</label>
          <input 
            type="text" 
            name="last_name" 
            value="<?= htmlspecialchars($admin['last_name'] ?? '') ?>" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-[#1D503A] focus:outline-none focus:ring-2 focus:ring-[#1D503A]" 
            placeholder="Last Name"
            required
          >
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1 text-[#1D503A]">Password (optional)</label>
        <input 
          type="password" 
          name="password" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-[#1D503A] focus:outline-none focus:ring-2 focus:ring-[#1D503A]" 
          placeholder="New Password"
        >
        <p class="text-xs text-gray-600 mt-1">Leave blank if you don't want to change it.</p>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium mb-1 text-[#1D503A]">Email</label>
        <input 
          type="email" 
          name="email" 
          value="<?= htmlspecialchars($admin['email'] ?? '') ?>" 
          class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-[#1D503A] focus:outline-none focus:ring-2 focus:ring-[#1D503A]" 
          placeholder="you@example.com"
          required
        >
      </div>

      <div class="flex justify-end gap-3">
        <button 
          type="button" 
          @click="showProfileModal = false" 
          class="px-6 py-2 text-sm font-semibold rounded-full bg-[#C9C3B9] text-[#1D503A] hover:bg-[#B0A998] transition"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="px-6 py-2 text-sm font-semibold rounded-full bg-[#1D503A] text-white hover:bg-[#144124] transition shadow-md"
        >
          Save
        </button>
      </div>
    </form>
  </div>
</div>


</div>


    </header>

    <!-- Main Page Content -->
    <main class="flex-1 overflow-y-auto p-6 max-w-6xl mx-auto mt-6">
      <section>
        <h2 class="text-2xl font-semibold mb-6" style="color: #1D503A;">All Registration Requests</h2>

        <?php if ($registrations->num_rows > 0): ?>
          <div class="overflow-x-auto bg-white rounded-xl shadow border" style="border-color: #1D503A;">
            <table class="min-w-full text-sm text-left text-gray-700">
              <thead class="bg-[#E5E1DB] border-b text-xs text-[#1D503A] uppercase">
                <tr>
                  <th class="px-6 py-4">Name</th>
                  <th class="px-6 py-4">Email</th>
                  <th class="px-6 py-4">Course & Year</th>
                  <th class="px-6 py-4">Event</th>
                  <th class="px-6 py-4">Block</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $registrations->fetch_assoc()): ?>
                  <?php
                  $status = $row['status'];
                  $statusColor = match ($status) {
                    'approved' => 'green',
                    'rejected' => 'red',
                    default => 'yellow',
                  };
                  ?>
                  <tr class="border-b hover:bg-[#FAF5EE]">
                    <td class="px-6 py-4 font-medium text-[#1D503A]"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-6 py-4 text-[#1D503A]"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="px-6 py-4 text-[#1D503A]"><?= htmlspecialchars($row['course_year']) ?></td>
                    <td class="px-6 py-4 text-[#1D503A]"><?= htmlspecialchars($row['event_name']) ?></td>
                    <td class="px-6 py-4 text-[#1D503A]"><?= htmlspecialchars($row['block']) ?></td>

                    <!-- Status Block (Copied design from event list) -->
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 rounded-full text-xs font-semibold
                    <?= $statusColor === 'green' ? 'bg-green-100 text-green-700' : ($statusColor === 'red' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                        <?= htmlspecialchars($status) ?>
                      </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 text-center space-x-2">
                      <?php if ($status === 'pending'): ?>
                        <form action="user-registration-status.php" method="POST" class="inline">
                          <input type="hidden" name="registration_id" value="<?= (int)$row['id'] ?>" />
                          <input type="hidden" name="action" value="approve" />
                          <button
                            type="submit"
                            class="btn-primary px-3 py-1 rounded text-sm hover:bg-[#15412B] transition">
                            Approve
                          </button>
                        </form>

                        <form action="user-registration-status.php" method="POST" class="inline">
                          <input type="hidden" name="registration_id" value="<?= (int)$row['id'] ?>" />
                          <input type="hidden" name="action" value="reject" />
                          <button
                            type="submit"
                            class="px-3 py-1 rounded text-sm bg-red-600 text-white hover:bg-red-700 transition">
                            Reject
                          </button>
                        </form>
                      <?php else: ?>
                        <!-- No buttons, show placeholder -->
                        <span class="text-sm text-gray-400 italic">No actions</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-center font-semibold text-[#1D503A]">No registration requests found.</p>
        <?php endif; ?>
      </section>
    </main>
  </div>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>