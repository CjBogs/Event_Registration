<?php
require_once '../config.php';

$result = mysqli_query($conn, "
    SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, created_at 
    FROM users 
    WHERE role = 'admin' AND status = 'pending' 
    ORDER BY created_at DESC
");
?>

<div class="overflow-x-auto bg-white rounded-xl shadow border mx-auto" style="border-color: #1D503A; max-width: 1000px;">
    <table class="w-full text-sm text-left text-gray-700">
        <thead class="bg-[#E5E1DB] border-b text-xs text-[#1D503A] uppercase">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Registered On</th>
                <th class="px-4 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr class="border-b hover:bg-[#FAF5EE]">
                        <td class="px-4 py-3 font-medium text-[#1D503A]"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="px-4 py-3 text-[#1D503A]"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="px-4 py-3 text-[#1D503A]"><?= htmlspecialchars(date('F j, Y', strtotime($row['created_at']))) ?></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center space-x-2">
                                <button
                                    data-user-id="<?= $row['id'] ?>"
                                    class="approve-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm font-semibold"
                                    aria-label="Approve admin <?= htmlspecialchars($row['name']) ?>">
                                    Approve
                                </button>
                                <button
                                    data-user-id="<?= $row['id'] ?>"
                                    class="reject-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-semibold"
                                    aria-label="Reject admin <?= htmlspecialchars($row['name']) ?>">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-500 font-medium">
                        No pending admin accounts found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        function checkIfEmptyTable() {
            const tbody = document.querySelector('table tbody');
            if (!tbody.querySelector('tr') || (tbody.children.length === 1 && tbody.querySelector('tr td[colspan="4"]'))) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-500 font-medium">
                        No pending admin accounts found.
                    </td>
                </tr>`;
            }
        }

        document.querySelectorAll('.approve-btn').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                button.disabled = true;
                fetch('approve_admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'user_id=' + encodeURIComponent(userId)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            button.closest('tr').remove();
                            checkIfEmptyTable();
                        } else {
                            alert('Approve failed: ' + data.message);
                        }
                    })
                    .catch(() => alert('Network error'))
                    .finally(() => button.disabled = false);
            });
        });

        document.querySelectorAll('.reject-btn').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                button.disabled = true;
                fetch('reject_admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'user_id=' + encodeURIComponent(userId)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            button.closest('tr').remove();
                            checkIfEmptyTable();
                        } else {
                            alert('Reject failed: ' + data.message);
                        }
                    })
                    .catch(() => alert('Network error'))
                    .finally(() => button.disabled = false);
            });
        });
    });
</script>