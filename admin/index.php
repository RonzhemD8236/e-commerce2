<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// Make sure the current user ID is set
$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch all users
$sql = "SELECT * FROM users ORDER BY id ASC";
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
                if ($row['role'] === 'customer') {
                    $stmtProfile = $conn->prepare("SELECT fname, lname FROM customer WHERE user_id=?");
                    $stmtProfile->bind_param("i", $row['id']);
                    $stmtProfile->execute();
                    $resProfile = $stmtProfile->get_result();
                    if ($resProfile->num_rows > 0) {
                        $profile = $resProfile->fetch_assoc();
                        $fullName = trim($profile['fname'] . ' ' . $profile['lname']);
                        if (!empty($fullName)) {
                            $displayName = $fullName;
                        }
                    }
                }

                // Row style for inactive users
                $rowClass = $row['active'] ? '' : 'table-danger';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($displayName) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <!-- Edit Button -->
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>

                        <!-- Activate/Deactivate Button -->
                        <?php if ($row['active']): ?>
                            <?php if ($row['id'] != $currentUserId): ?>
                                <a href="toggle_status.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Deactivate this user?');">Deactivate</a>
                            <?php else: ?>
                                <a href="#" 
                                class="btn btn-sm btn-secondary disabled" 
                                style="pointer-events: none; opacity: 0.7; cursor: not-allowed;">
                                Deactivate
                                </a>

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
