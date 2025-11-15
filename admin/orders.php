<?php
session_start();
include('header.php'); // Admin header
include('../includes/config.php');

// ---------- UPDATED SQL: include payment_method and shipping_method ----------
$sql = "SELECT 
            o.orderinfo_id AS orderId, 
            SUM(i.sell_price * ol.quantity) AS total, 
            o.status,
            o.payment_method,
            o.shipping_method
        FROM orderinfo o 
        INNER JOIN orderline ol USING (orderinfo_id) 
        INNER JOIN item i USING (item_id)
        GROUP BY o.orderinfo_id
        ORDER BY total DESC";

$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);

?>
<h2>Number of items: <?= $itemCount ?></h2>
<?php include("../includes/alert.php"); ?>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Total</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Shipping Method</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['orderId']}</td>";
        echo "<td>{$row['total']}</td>";

        // Status color
        $statusColor = ($row['status'] === 'Delivered') ? 'green' : 'red';
        echo "<td style='color: $statusColor'>{$row['status']}</td>";

        // Payment method
        echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";

        // Shipping method
        echo "<td>" . htmlspecialchars($row['shipping_method']) . "</td>";

        // Action icon color
        $eyeColor = ($row['status'] === 'Delivered') ? 'gray' : 'blue';
        echo "<td><a href='orderDetails.php?orderinfo_id={$row['orderId']}'><i class='fa-regular fa-eye' style='color: $eyeColor'></i></a></td>";

        echo "</tr>";
    }
    ?>
    </tbody>
</table>

<?php
include('../includes/footer.php');
?>
