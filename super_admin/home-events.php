<?php
require_once '../config.php';  // ensure $conn is defined
require_once '../helpers.php'; // make sure truncate() is defined here

// Define custom order for status: pending first, approved next, rejected last
// We use FIELD() in MySQL to specify custom ordering
$query = "SELECT * FROM events ORDER BY 
    FIELD(status, 'pending', 'approved', 'rejected'),
    event_date DESC";

$allEvents = mysqli_query($conn, $query);

if (!$allEvents) {
    echo "Error fetching events: " . mysqli_error($conn);
    $allEvents = [];
}
?>

<div class="overflow-x-auto bg-white rounded-xl shadow border mx-auto" style="border-color: #1D503A; max-width: 1000px;">
    <table class="w-full text-sm text-left text-gray-700">
        <thead class="bg-[#E5E1DB] border-b text-xs text-[#1D503A] uppercase">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Description</th>
                <th class="px-4 py-3">Created By</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($allEvents)): ?>
                <?php
                $statusColor = match ($row['status']) {
                    'approved' => 'green',
                    'rejected' => 'red',
                    default => 'yellow',
                };
                ?>
                <tr class="border-b hover:bg-[#FAF5EE]">
                    <td class="px-4 py-3 font-medium text-[#1D503A]"><?= htmlspecialchars($row['title']) ?></td>
                    <td class="px-4 py-3 text-[#1D503A]"><?= htmlspecialchars(date('F j, Y', strtotime($row['event_date']))) ?></td>
                    <td class="px-4 py-3 max-w-xs truncate text-[#1D503A]" title="<?= htmlspecialchars($row['description']) ?>"><?= truncate($row['description']) ?></td>
                    <td class="px-4 py-3 text-[#1D503A]"><?= htmlspecialchars($row['user_email']) ?></td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            <?= $statusColor === 'green' ? 'bg-green-100 text-green-700' : ($statusColor === 'red' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if ($row['status'] === 'pending'): ?>
                            <form method="POST" action="update-event-status.php" class="flex justify-center space-x-2">
                                <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                                <button name="action" value="approve" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm font-semibold">Approve</button>
                                <button name="action" value="reject" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-semibold">Reject</button>
                            </form>
                        <?php else: ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>