<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

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
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <?php
                // If user is customer, fetch name from customer table
                $displayName = $row['name'];
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
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($displayName) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
