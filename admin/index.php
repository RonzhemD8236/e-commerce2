<?php
session_start();
include('../admin/header.php');
include('../includes/config.php');

// Make sure the current user ID is set
$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Determine if a role filter is applied
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Fetch all users + customer info (if they exist)
$sql = "
    SELECT 
        u.id, u.username, u.email, u.role, u.active, u.created_at,
        c.fname, c.lname
    FROM users u
    LEFT JOIN customer c ON u.id = c.user_id
";

// Add WHERE clause if role filter is applied
if ($roleFilter === 'customer') {
    $sql .= " WHERE u.role = 'customer'";
} elseif ($roleFilter === 'admin') {
    $sql .= " WHERE u.role = 'admin'";
}

$sql .= " ORDER BY u.id ASC";
$result = mysqli_query($conn, $sql);
?>
<style>
.role-btns .role-btn {
    background-color: white;    /* White background */
    color: #333;                /* Dark text */
    border: none;               /* Remove all borders */
    border-radius: 0;           /* Remove button rounding */
    margin-right: 10px;         /* Spacing between buttons */
    padding: 8px 16px;
    position: relative;         /* Needed for bottom border effect */
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

/* Bottom border on hover */
.role-btns .role-btn:hover {
    color: #007bff;             /* Text color on hover */
    border-bottom: 2px solid #007bff;
}

/* Active / selected button */
.role-btns .role-btn.active {
    color: #007bff;
    border-bottom: 2px solid #007bff;
}

table th, table td {
    vertical-align: middle;
    text-align: center;
}

table th:first-child, table td:first-child {
    width: 50px; /* ID column */
}

table th:nth-child(2), table td:nth-child(2) {
    width: 150px; /* Name column */
}

table th:nth-child(3), table td:nth-child(3) {
    width: 200px; /* Email column */
}

table {
    table-layout: fixed;
    width: 100%;
}

/* Adjust other columns similarly */


</style>

<div class="container mt-4">
    <div class="container mt-4">
    <!-- Header Row with Title and Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>User Management</h2>
        <a href="create.php" class="btn btn-success">Add New User</a>
    </div>
<!-- Role Filter Buttons -->
<div class="mb-3 role-btns">
    <a href="index.php" class="role-btn <?= $roleFilter == '' ? 'active' : '' ?>">All Users</a>
    <a href="index.php?role=customer" class="role-btn <?= $roleFilter == 'customer' ? 'active' : '' ?>">Customers</a>
    <a href="index.php?role=admin" class="role-btn <?= $roleFilter == 'admin' ? 'active' : '' ?>">Admins</a>
</div>


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
        if ($row['role'] === 'customer') {
            $displayName = (!empty($row['fname']) && !empty($row['lname'])) 
                ? trim($row['fname'] . ' ' . $row['lname']) 
                : $row['username'];
        } else {
            $displayName = $row['username']; // Admins always use username
        }

        // Ensure email and created_at are never empty
        $email = !empty($row['email']) ? $row['email'] : '-';
        $createdAt = !empty($row['created_at']) ? $row['created_at'] : '-';

        // Row style for inactive users
        $rowClass = $row['active'] ? '' : 'table-danger';
        ?>
        <tr class="<?= $rowClass ?>">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($displayName) ?></td>
            <td><?= htmlspecialchars($email) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
            <td><?= htmlspecialchars($createdAt) ?></td>
            <td>
                <!-- Edit Button -->
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
