<?php
session_start();
include('../includes/header.php'); // User header
include('../includes/config.php');

// ---------- LOGIN CHECK ----------
if (!isset($_SESSION['user_id'])) {
    die("<h2>❌ You must be logged in to view your orders.</h2>");
}

// ---------- GET CUSTOMER ID ----------
$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT customer_id FROM customer WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Customer not found.");
}

$customer = $result->fetch_assoc();
$customer_id = (int)$customer['customer_id'];
$stmt->close();

// ---------- FETCH USER'S ORDERS ----------
$sql = "SELECT 
            o.orderinfo_id AS orderId,
            o.date_placed,
            SUM(i.sell_price * ol.quantity) + o.shipping AS total,
            o.status,
            o.payment_method,
            o.shipping_method
        FROM orderinfo o 
        INNER JOIN orderline ol USING (orderinfo_id) 
        INNER JOIN item i USING (item_id)
        WHERE o.customer_id = $customer_id
        GROUP BY o.orderinfo_id
        ORDER BY o.date_placed DESC";

$result = mysqli_query($conn, $sql);
$orderCount = mysqli_num_rows($result);

?>
<h1 align="center">My Orders</h1>

<h3>Total Orders: <?= $orderCount ?></h3>

<?php if ($orderCount === 0): ?>
    <p>You have no orders yet. <a href="../index.php">Start shopping!</a></p>
<?php else: ?>

<table class="table table-striped table-bordered" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Date Placed</th>
            <th>Total</th>
            <th>Payment Method</th>
            <th>Shipping Method</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['orderId']}</td>";
        echo "<td>" . date('M d, Y', strtotime($row['date_placed'])) . "</td>";
        echo "<td>₱" . number_format($row['total'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
        echo "<td>" . htmlspecialchars($row['shipping_method']) . "</td>";

        // Status with color
        $statusColor = ($row['status'] === 'Delivered') ? 'green' : 
                      (($row['status'] === 'Pending') ? 'orange' : 'red');
        echo "<td style='color: $statusColor; font-weight:bold;'>{$row['status']}</td>";

        echo "</tr>";
    }
    ?>
    </tbody>
</table>
<div class="main-content"></div>

<?php endif; ?>
<?php include('../includes/footer.php'); ?>