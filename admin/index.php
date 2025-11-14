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
/* Custom button colors - Black and Lavender palette */
.btn-success {
    background-color: black !important;
    border-color: black !important;
    color: white !important;
}

.btn-success:hover {
    background-color: gray !important;
    border-color:  !important;
}

.btn-warning {
    background-color: #2d2d2d !important;
    border-color: #2d2d2d !important;
    color: white !important;
}

.btn-warning:hover {
    background-color: #1a1a1a !important;
    border-color: #1a1a1a !important;
}

.btn-danger {
    background-color: #4a4a4a !important;
    border-color: #4a4a4a !important;
    color: white !important;
}

.btn-danger:hover {
    background-color: #363636 !important;
    border-color: #363636 !important;
}

.btn-sm.btn-success {
    background-color: #c8b6ff !important;
    border-color: #c8b6ff !important;
    color: #2d2d2d !important;
}

.btn-sm.btn-success:hover {
    background-color: #b8a3ff !important;
    border-color: #b8a3ff !important;
}

.role-btns .role-btn {
    background-color: white;
    color: #333;
    border: none;
    border-radius: 0;
    margin-right: 10px;
    padding: 8px 16px;
    position: relative;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.role-btns .role-btn.active {
    color: gray;
    border-bottom: 2px solid gray;
}

table th, table td {
    vertical-align: middle;
    text-align: center;
}

table th:first-child, table td:first-child {
    width: 50px;
}

table th:nth-child(2), table td:nth-child(2) {
    width: 150px;
}

table th:nth-child(3), table td:nth-child(3) {
    width: 200px;
}

table {
    table-layout: fixed;
    width: 100%;
}

/* Full width container with background image */
.header-container {
    width: 100%;
    background-image: url('https://i.pinimg.com/736x/3c/9d/da/3c9dda82bd749af35aa66cc6b56900be.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 60px 50px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

/* Dark overlay for better text readability */
.header-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1;
}

.header-container .text-content {
    position: relative;
    z-index: 2;
    color: white;
    max-width: 800px;
}

.header-container .text-content h1 {
    margin: 0 0 15px 0;
    font-size: 42px;
    font-weight: bold;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.header-container .text-content p {
    margin: 0;
    font-size: 17px;
    color: rgba(255, 255, 255, 0.95);
    line-height: 1.6;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

/* Search bar styling */
#searchInput {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 15px;
    font-size: 15px;
    transition: all 0.3s ease;
}

#searchInput:focus {
    border-color: #9b87f5;
    box-shadow: 0 0 0 0.2rem rgba(155, 135, 245, 0.25);
    outline: none;
}
</style>

<script>
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.querySelector('.table');
    const rows = table.getElementsByTagName('tr');
    
    // Start from 1 to skip the header row
    for (let i = 1; i < rows.length; i++) {
        const nameCell = rows[i].getElementsByTagName('td')[1]; // Name is in the second column
        
        if (nameCell) {
            const nameText = nameCell.textContent || nameCell.innerText;
            
            if (nameText.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}
</script>

<div class="container-fluid px-4">
    <!-- Full Width Header with Background Image -->
    <div class="header-container">
        <div class="text-content">
            <h1>User Management</h1>
            <p>Manage and oversee user roles, activate/deactivate users, and maintain user data securely. Control access and permissions for all system users.</p>
        </div>
    </div>

    <!-- Header Row with Search and Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="search-container" style="flex: 1; max-width: 400px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name..." onkeyup="searchTable()">
        </div>
        <a href="create.php" class="btn btn-success">Add New User</a>
    </div>
    <br><br>
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
                $displayName = $row['username'];
            }

            $email = !empty($row['email']) ? $row['email'] : '-';
            $createdAt = !empty($row['created_at']) ? $row['created_at'] : '-';
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
                    <?php if ($row['active']): ?>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                    <?php endif; ?>

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