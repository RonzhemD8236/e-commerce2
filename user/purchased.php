<?php
session_start();
include('../includes/header.php'); // User header
include('../includes/config.php');

// ---------- LOGIN CHECK ----------
if (!isset($_SESSION['user_id'])) {
    die("<h2>❌ You must be logged in to view your purchased items.</h2>");
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

// ---------- FETCH DELIVERED ORDERS ONLY ----------
$sql = "SELECT 
            o.orderinfo_id AS orderId,
            o.date_placed,
            o.date_shipped,
            SUM(i.sell_price * ol.quantity) + o.shipping AS total,
            o.payment_method,
            o.shipping_method
        FROM orderinfo o 
        INNER JOIN orderline ol USING (orderinfo_id) 
        INNER JOIN item i USING (item_id)
        WHERE o.customer_id = $customer_id 
        AND o.status = 'Delivered'
        GROUP BY o.orderinfo_id
        ORDER BY o.date_shipped DESC";

$result = mysqli_query($conn, $sql);
$orderCount = mysqli_num_rows($result);

?>

<h1 align="center">My Purchases</h1>

<h3>Delivered Orders: <?= $orderCount ?></h3>

<?php if ($orderCount === 0): ?>
    <p>You have no delivered orders yet. Check your <a href="myorders.php">orders</a> for pending deliveries.</p>
<?php else: ?>

<table class="table table-striped table-bordered" style="width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Date Ordered</th>
            <th>Date Delivered</th>
            <th>Total</th>
            <th>Payment Method</th>
            <th>Shipping Method</th>
        </tr>
    </thead>
    <tbody>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['orderId']}</td>";
        echo "<td>" . date('M d, Y', strtotime($row['date_placed'])) . "</td>";
        
        // Date shipped (delivered date)
        $deliveredDate = $row['date_shipped'] ? date('M d, Y', strtotime($row['date_shipped'])) : 'N/A';
        echo "<td>{$deliveredDate}</td>";
        
        echo "<td>₱" . number_format($row['total'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
        echo "<td>" . htmlspecialchars($row['shipping_method']) . "</td>";

        echo "</tr>";
    }
    ?>
    </tbody>
</table>

<?php endif; ?>

<?php include('../includes/footer.php'); ?>