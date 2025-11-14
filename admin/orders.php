<?php
session_start();
include('header.php'); // Admin header
include('../includes/config.php');

// Determine if a status filter is applied
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the SQL query with optional status filter
$sql = "SELECT 
            o.orderinfo_id AS orderId, 
            SUM(i.sell_price * ol.quantity) AS total, 
            o.status,
            o.date_placed
        FROM orderinfo o 
        INNER JOIN orderline ol USING (orderinfo_id) 
        INNER JOIN item i USING (item_id)";

// Add WHERE clause if status filter is applied
if ($statusFilter === 'delivered') {
    $sql .= " WHERE o.status = 'Delivered'";
} elseif ($statusFilter === 'pending') {
    $sql .= " WHERE o.status != 'Delivered'";
}

$sql .= " GROUP BY o.orderinfo_id
          ORDER BY o.orderinfo_id DESC";

$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);
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
    border-color: gray !important;
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

.btn-sm.btn-primary {
    background-color: #c8b6ff !important;
    border-color: #c8b6ff !important;
    color: #2d2d2d !important;
}

.btn-sm.btn-primary:hover {
    background-color: #b8a3ff !important;
    border-color: #b8a3ff !important;
}

.status-btns .status-btn {
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

.status-btns .status-btn.active {
    color: gray;
    border-bottom: 2px solid gray;
}

table th, table td {
    vertical-align: middle;
    text-align: center;
}

table th:first-child, table td:first-child {
    width: 100px;
}

table th:nth-child(2), table td:nth-child(2) {
    width: 150px;
}

table {
    table-layout: fixed;
    width: 100%;
}

/* Full width container with background image */
.header-container {
    width: 100%;
    background-image: url('https://i.pinimg.com/736x/f6/4f/65/f64f65ff3bb28459e934fa38db43dd99.jpg');
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

/* Status badges */
.status-delivered {
    color: #28a745;
    font-weight: 600;
}

.status-pending {
    color: #dc3545;
    font-weight: 600;
}

/* Action icon styling */
.action-icon {
    font-size: 18px;
    transition: all 0.2s ease;
}

.action-icon:hover {
    transform: scale(1.2);
}

.stats-badge {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 20px;
    display: inline-block;
    margin-left: 15px;
}

.stats-badge strong {
    color: #333;
    font-size: 16px;
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
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        // Search through Order ID, Total, and Status columns
        for (let j = 0; j < 3; j++) {
            if (cells[j]) {
                const cellText = cells[j].textContent || cells[j].innerText;
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>

<div class="container-fluid px-4">
    <!-- Full Width Header with Background Image -->
    <div class="header-container">
        <div class="text-content">
            <h1>Order Management</h1>
            <p>Monitor and manage all customer orders. Track order status, review order details, and ensure timely delivery of products to customers.</p>
        </div>
    </div>

    <?php include("../includes/alert.php"); ?>

    <!-- Header Row with Search and Stats -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="search-container" style="flex: 1; max-width: 400px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Search orders..." onkeyup="searchTable()">
        </div>
    </div>
    <br><br>

    <!-- Status Filter Buttons -->
    <div class="mb-3 status-btns">
        <a href="orders.php" class="status-btn <?= $statusFilter == '' ? 'active' : '' ?>">All Orders</a>
        <a href="orders.php?status=delivered" class="status-btn <?= $statusFilter == 'delivered' ? 'active' : '' ?>">Delivered</a>
        <a href="orders.php?status=pending" class="status-btn <?= $statusFilter == 'pending' ? 'active' : '' ?>">Pending</a>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date Placed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <?php
            $statusClass = ($row['status'] === 'Delivered') ? 'status-delivered' : 'status-pending';
            $iconColor = ($row['status'] === 'Delivered') ? 'gray' : '#c8b6ff';
            $datePlaced = !empty($row['date_placed']) ? date('Y-m-d H:i', strtotime($row['date_placed'])) : '-';
            ?>
            <tr>
                <td><?= $row['orderId'] ?></td>
                <td>₱<?= number_format($row['total'], 2) ?></td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($datePlaced) ?></td>
                <td>
                    <a href="orderDetails.php?orderinfo_id=<?= $row['orderId'] ?>" title="View Details">
                        <i class="fa-regular fa-eye action-icon" style="color: <?= $iconColor ?>"></i>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
        <div class="stats-badge">
            <strong>Total Orders: <?= $itemCount ?></strong>
        </div>

<?php
include('../includes/footer.php');
?>