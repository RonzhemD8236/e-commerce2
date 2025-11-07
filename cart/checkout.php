<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("❌ You must be logged in to checkout.");
}

// Check if cart has items
if (!isset($_SESSION["cart_products"]) || count($_SESSION["cart_products"]) === 0) {
    die("❌ Your cart is empty.");
}

try {
    mysqli_begin_transaction($conn); // start transaction

    // Get customer ID from session user
    $sql = "SELECT customer_id FROM customer WHERE user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) === 0) {
        throw new Exception("Customer not found.");
    }

    $row = mysqli_fetch_assoc($result);
    $customer_id = $row['customer_id'];

    // Insert into orderinfo
    $shipping = 10.00;
    $q = "INSERT INTO orderinfo(customer_id, date_placed, date_shipped, shipping) VALUES (?, NOW(), NOW(), ?)";
    $stmt1 = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt1, 'id', $customer_id, $shipping);
    mysqli_stmt_execute($stmt1);
    $orderinfo_id = mysqli_insert_id($conn);

    // Prepare orderline insert
    $q2 = "INSERT INTO orderline(orderinfo_id, item_id, quantity) VALUES (?, ?, ?)";
    $stmt2 = mysqli_prepare($conn, $q2);

    // Prepare stock update
    $q3 = "UPDATE stock SET quantity = quantity - ? WHERE item_id = ?";
    $stmt3 = mysqli_prepare($conn, $q3);

    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_qty = (int)$cart_itm["item_qty"];
        $product_code = (int)$cart_itm["item_id"];

        // Insert order line
        mysqli_stmt_bind_param($stmt2, 'iii', $orderinfo_id, $product_code, $product_qty);
        mysqli_stmt_execute($stmt2);

        // Update stock
        mysqli_stmt_bind_param($stmt3, 'ii', $product_qty, $product_code);
        mysqli_stmt_execute($stmt3);
    }

    // Commit transaction
    mysqli_commit($conn);

    // Clear cart
    unset($_SESSION["cart_products"]);

    // Show confirmation
    echo "<h2>✅ Thank you! Your order has been placed.</h2>";
    echo "<p>Order ID: {$orderinfo_id}</p>";
    echo '<p><a href="../index.php">Continue Shopping</a></p>';

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo "<h2>❌ Error processing order:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

include('../includes/footer.php');
?>
