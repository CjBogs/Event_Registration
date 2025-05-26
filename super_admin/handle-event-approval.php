<?php
require_once '../config.php';

// Helper function to truncate text
function truncate($text, $maxChars = 50)
{
    $text = htmlspecialchars($text);
    return strlen($text) > $maxChars ? substr($text, 0, $maxChars) . '...' : $text;
}

// Fetch all events ordered by date descending
$allEvents = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date DESC");

if (!$allEvents) {
    die('Error fetching events: ' . mysqli_error($conn));
}
?>

<div class="max-w-6xl mx-auto p-6 space-y-8">
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th class="py-3 px-6 text-left">Title</th>
                    <th class="py-3 px-6 text-left">Date</th>
                    <th class="py-3 px-6 text-left">Description</th>
                    <th class="py-3 px-6 text-left">Created By</th>
                    <th class="py-3 px-6 text-left">Status</th>
                    <th class="py-3 px-6 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($allEvents) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($allEvents)) : ?>
                        <?php
                        // Determine status color for badge
                        $statusColor = match ($row['status']) {
                            'approved' => 'green',
                            'rejected' => 'red',
                            default => 'yellow',
                        };
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition-colors duration-200">
                            <td class="py-3 px-6 font-medium text-gray-900"><?= htmlspecialchars($row['title']) ?></td>
                            <td class="py-3 px-6 text-gray-900"><?= htmlspecialchars(date('F j, Y', strtotime($row['event_date']))) ?></td>
                            <td class="py-3 px-6 max-w-xs truncate" title="<?= htmlspecialchars($row['description']) ?>"><?= truncate($row['description']) ?></td>
                            <td class="py-3 px-6 text-gray-900"><?= htmlspecialchars($row['user_email']) ?></td>
                            <td class="py-3 px-6">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                            <?= $statusColor === 'green' ? 'bg-green-100 text-green-700' : ($statusColor === 'red' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-6">
                                <?php if ($row['status'] === 'pending') : ?>
                                    <form method="POST" action="update-event-status.php" class="flex space-x-2">
                                        <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                                        <button name="action" value="approve" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm font-semibold">Approve</button>
                                        <button name="action" value="reject" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-semibold">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-600 font-medium">No events found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>