<?php
require_once '../config.php';

// Pagination setup
$limit = 5;
$page = isset($_GET['pending_page']) ? max(1, (int)$_GET['pending_page']) : 1;
$start = ($page - 1) * $limit;

// Get total count for pagination
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE status = 'pending'");
$totalData = mysqli_fetch_assoc($totalQuery);
$total = $totalData['total'];
$pages = ceil($total / $limit);

// Fetch pending events
$result = mysqli_query($conn, "SELECT * FROM events WHERE status = 'pending' ORDER BY event_date DESC LIMIT $start, $limit");
?>

<!-- Pending Events Table -->
<div class="overflow-x-auto">
    <table class="min-w-full bg-white shadow rounded-lg">
        <thead class="bg-yellow-100 text-yellow-800">
            <tr>
                <th class="py-2 px-4 text-left">Title</th>
                <th class="py-2 px-4 text-left">Date</th>
                <th class="py-2 px-4 text-left">Description</th>
                <th class="py-2 px-4 text-left">Created By</th>
                <th class="py-2 px-4 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr class="border-t">
                        <td class="py-2 px-4"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($row['event_date']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($row['user_email']) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" action="update-event-status.php" class="flex space-x-2">
                                <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                                <button name="action" value="approve" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Approve</button>
                                <button name="action" value="reject" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="py-4 text-center text-yellow-600">No pending events found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4 flex justify-center space-x-2">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a
            href="?pending_page=<?= $i ?>#super"
            class="px-3 py-1 rounded <?= $i == $page ? 'bg-yellow-600 text-white' : 'bg-white text-yellow-600 border border-yellow-600' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>