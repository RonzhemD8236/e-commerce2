checkoutprocess.php

<?php
session_start();
include('../includes/config.php');

// ---------- LOGIN CHECK ----------
if (!isset($_SESSION['user_id'])) {
    die("❌ You must be logged in to checkout.");
}

// ---------- CART CHECK ----------
if (!isset($_SESSION["cart_products"]) || count($_SESSION["cart_products"]) === 0) {
    die("❌ Your cart is empty.");
}

// ---------- GET POST DATA ----------
$shipping_method = $_POST['shipping_method'] ?? 'delivery';
$payment_method  = $_POST['payment_method'] ?? 'cod';

$card_number = $_POST['card_number'] ?? null;
$card_name   = $_POST['card_name'] ?? null;
$card_expiry = $_POST['card_expiry'] ?? null;
$card_cvv    = $_POST['card_cvv'] ?? null;

$ewallet_number = $_POST['ewallet_number'] ?? null;
$ewallet_name   = $_POST['ewallet_name'] ?? null;
$ewallet_id     = $_POST['ewallet_id'] ?? null;

// Shipping cost
$shipping_cost = ($shipping_method === 'delivery') ? 50 : 0;

try {
    // ---------- TRANSACTION START ----------
    mysqli_begin_transaction($conn);

    // ---------- GET CUSTOMER ID ----------
    $user_id = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT customer_id FROM customer WHERE user_id = $user_id LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Customer not found.");
    }
    $row = $result->fetch_assoc();
    $customer_id = (int)$row['customer_id'];

    // ---------- INSERT INTO orderinfo ----------
    $shipping_method_safe = $conn->real_escape_string($shipping_method);
    $payment_method_safe  = $conn->real_escape_string($payment_method);
    $shipping_cost_safe   = (float)$shipping_cost;

    $sql_orderinfo = "
        INSERT INTO orderinfo(customer_id, date_placed, shipping, shipping_method, payment_method, status)
        VALUES ($customer_id, NOW(), $shipping_cost_safe, '$shipping_method_safe', '$payment_method_safe', 'Pending')
    ";
    if (!$conn->query($sql_orderinfo)) {
        throw new Exception("Order insert failed: " . $conn->error);
    }
    $orderinfo_id = $conn->insert_id;

    // ---------- INSERT ORDER LINES AND UPDATE STOCK ----------
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $item_id = (int)($cart_itm['item_id'] ?? 0); // Use item_id directly
        $qty     = (int)($cart_itm['item_qty'] ?? 0);

        if ($item_id === 0 || $qty <= 0) {
            throw new Exception("Invalid cart item: {$cart_itm['item_name']}");
        }

        // Insert order line
        $sql_orderline = "
            INSERT INTO orderline(orderinfo_id, item_id, quantity)
            VALUES ($orderinfo_id, $item_id, $qty)
        ";
        if (!$conn->query($sql_orderline)) {
            throw new Exception("Order line insert failed for item_id: $item_id - " . $conn->error);
        }

        // Update stock
        $sql_stock = "UPDATE stock SET quantity = quantity - $qty WHERE item_id = $item_id";
        if (!$conn->query($sql_stock)) {
            throw new Exception("Stock update failed for item_id: $item_id - " . $conn->error);
        }
    }

    // ---------- SAVE PAYMENT DETAILS ----------
    if ($payment_method === 'card') {
        $card_number_safe = $conn->real_escape_string($card_number);
        $card_name_safe   = $conn->real_escape_string($card_name);
        $card_expiry_safe = $conn->real_escape_string($card_expiry);
        $card_cvv_safe    = $conn->real_escape_string($card_cvv);

        $sql_card = "
            INSERT INTO payment_card(orderinfo_id, card_number, card_name, card_expiry, card_cvv)
            VALUES ($orderinfo_id, '$card_number_safe', '$card_name_safe', '$card_expiry_safe', '$card_cvv_safe')
        ";
        if (!$conn->query($sql_card)) {
            throw new Exception("Card payment insert failed: " . $conn->error);
        }
    } elseif ($payment_method === 'ewallet') {
        $ewallet_number_safe = $conn->real_escape_string($ewallet_number);
        $ewallet_name_safe   = $conn->real_escape_string($ewallet_name);
        $ewallet_id_safe     = $conn->real_escape_string($ewallet_id);

        $sql_ewallet = "
            INSERT INTO payment_ewallet(orderinfo_id, wallet_number, wallet_name, wallet_id)
            VALUES ($orderinfo_id, '$ewallet_number_safe', '$ewallet_name_safe', '$ewallet_id_safe')
        ";
        if (!$conn->query($sql_ewallet)) {
            throw new Exception("E-wallet payment insert failed: " . $conn->error);
        }
    }
    // COD requires no extra data

    // ---------- COMMIT ----------
    mysqli_commit($conn);

    // Clear cart
    unset($_SESSION["cart_products"]);

    // ---------- CONFIRMATION ----------
    echo "<h2>✅ Thank you! Your order has been placed.</h2>";
    echo "<p>Order ID: {$orderinfo_id}</p>";
    echo '<p><a href="/lensify/e-commerce2/index.php">Continue Shopping</a></p>';

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<h2>❌ Error processing order:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
