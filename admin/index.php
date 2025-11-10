<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// Make sure the current user ID is set
$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch all users + customer info (if they exist)
$sql = "
    SELECT 
        u.id, u.username, u.email, u.role, u.active, u.created_at,
        c.fname, c.lname
    FROM users u
    LEFT JOIN customer c ON u.id = c.user_id
    ORDER BY u.id ASC
";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2>User Management</h2>
    <a href="create.php" class="btn btn-primary mb-2">Add New User</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <?php
                // Determine display name
                $displayName = $row['username'];
                if ($row['role'] === 'customer' && !empty($row['fname']) && !empty($row['lname'])) {
                    $displayName = trim($row['fname'] . ' ' . $row['lname']);
                }

                // Row style for inactive users
                $rowClass = $row['active'] ? '' : 'table-danger';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($displayName) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td>
                        <!-- Edit Button (only if user is active) -->
                        <?php if ($row['active']): ?>
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                        <?php endif; ?>

                        <!-- Activate/Deactivate Button -->
                        <?php if ($row['active']): ?>
                            <?php if ($row['id'] != $currentUserId): ?>
                                <a href="toggle_status.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Deactivate this user?');">Deactivate</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Deactivate</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="toggle_status.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-success" 
                               onclick="return confirm('Activate this user?');">Activate</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
