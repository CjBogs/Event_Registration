<?php
require_once '../config.php';

$limit = 5;
$page = isset($_GET['rejected_page']) ? max(1, (int)$_GET['rejected_page']) : 1;
$start = ($page - 1) * $limit;

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE status = 'rejected'");
$totalData = mysqli_fetch_assoc($totalQuery);
$total = $totalData['total'];
$pages = ceil($total / $limit);

$result = mysqli_query($conn, "SELECT * FROM events WHERE status = 'rejected' ORDER BY event_date DESC LIMIT $start, $limit");
?>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white shadow rounded-lg">
        <thead class="bg-red-100 text-red-800">
            <tr>
                <th class="py-2 px-4 text-left">Title</th>
                <th class="py-2 px-4 text-left">Date</th>
                <th class="py-2 px-4 text-left">Description</th>
                <th class="py-2 px-4 text-left">Created By</th>
            </tr>
        </thead>
        <tbody class="text-red-800">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr class="border-t">
                        <td class="py-2 px-4"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($row['event_date']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($row['user_email']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="py-4 text-center text-gray-500">No rejected events found.</td>
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
            class="px-3 py-1 rounded <?= $i == $page ? 'bg-red-600 text-white' : 'bg-white text-red-600 border border-red-600' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>