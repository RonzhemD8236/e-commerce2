checkout.php

<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<h2>❌ You must be logged in to checkout.</h2>");
}

// ✅ Check if cart has items
if (!isset($_SESSION['cart_products']) || count($_SESSION['cart_products']) === 0) {
    die("<h2>❌ Your cart is empty.</h2>");
}

// Fetch customer info using correct columns
$stmt = $conn->prepare("SELECT customer_id, fname, lname, phone, addressline FROM customer WHERE user_id = ? LIMIT 1");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("❌ Customer not found.");
$customer = $result->fetch_assoc();
$customer_id = $customer['customer_id'];
$customer_name = $customer['fname'] . ' ' . $customer['lname'];
$customer_phone = $customer['phone'];
$customer_address = $customer['addressline'];
$stmt->close();

// Calculate merchandise total
$merchandise_total = 0;
foreach ($_SESSION['cart_products'] as $itm) {
    $merchandise_total += $itm['item_price'] * $itm['item_qty'];
}
$default_shipping = 50; // default delivery fee

?>

<h1 align="center">Checkout</h1>

<!-- Customer Info -->
<div style="margin:20px 0; padding:10px; border:1px solid #ccc; border-radius:8px;">
    <strong><?= htmlspecialchars($customer_name) ?></strong> P <?= htmlspecialchars($customer_phone) ?>
    <br>
    <address><?= nl2br(htmlspecialchars($customer_address)) ?></address>
</div>

<!-- Checkout Form -->
<form method="POST" action="checkout_process.php" id="checkoutForm">
    <!-- Cart Items -->
    <h3>Products in Your Cart</h3>
    <table width="100%" cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['cart_products'] as $itm): 
                $subtotal = $itm['item_price'] * $itm['item_qty']; ?>
            <tr>
                <td><?= htmlspecialchars($itm['item_name']) ?></td>
                <td><?= $itm['item_qty'] ?></td>
                <td>₱<?= number_format($itm['item_price'],2) ?></td>
                <td>₱<?= number_format($subtotal,2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>

    <!-- Shipping Option -->
    <h3>Shipping Method</h3>
    <label><input type="radio" name="shipping_method" value="delivery" checked> Delivery (₱50)</label><br>
    <label><input type="radio" name="shipping_method" value="pickup"> Pick-up (₱0)</label>

    <br><br>

    <!-- Payment Method -->
    <h3>Payment Method</h3>
    <label><input type="radio" name="payment_method" value="cod" checked> Cash on Delivery</label><br>
    <label><input type="radio" name="payment_method" value="card"> Credit/Debit Card</label><br>
    <label><input type="radio" name="payment_method" value="ewallet"> E-Wallet</label>

    <div id="cardDetails" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px; border-radius:8px;">
        <h4>Card Details</h4>
        <label>Card Number: <input type="text" name="card_number" maxlength="16"></label><br>
        <label>Expiry Date: <input type="month" name="card_expiry"></label><br>
        <label>CVV: <input type="text" name="card_cvv" maxlength="4"></label><br>
        <label>Name on Card: <input type="text" name="card_name"></label>
    </div>

    <div id="ewalletDetails" style="display:none; margin-top:10px; border:1px solid #ccc; padding:10px; border-radius:8px;">
        <h4>E-Wallet Details</h4>
        <label>Wallet ID: <input type="text" name="ewallet_id"></label><br>
        <label>Name: <input type="text" name="ewallet_name"></label><br>
        <label>Number: <input type="text" name="ewallet_number"></label>
    </div>

    <br>

    <!-- Totals -->
    <div style="margin-top:20px; padding:10px; border:1px solid #ccc; border-radius:8px;">
        <p>Merchandise Subtotal: ₱<span id="merchTotal"><?= number_format($merchandise_total,2) ?></span></p>
        <p>Shipping Fee: ₱<span id="shipFee"><?= number_format($default_shipping,2) ?></span></p>
        <hr>
        <p><strong>Total Payment: ₱<span id="totalPayment"><?= number_format($merchandise_total + $default_shipping,2) ?></span></strong></p>
    </div>

    <!-- Place Order -->
    <div style="margin-top:20px; padding:15px; background:#f1f1f1; border-radius:8px; text-align:center;">
        <button type="submit" style="font-size:18px; padding:10px 30px;">Place Order</button>
    </div>

    <!-- Pass customer ID -->
    <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
</form>

<script>
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const cardDiv = document.getElementById('cardDetails');
const walletDiv = document.getElementById('ewalletDetails');
const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');

paymentRadios.forEach(r => {
    r.addEventListener('change', ()=> {
        cardDiv.style.display = r.value === 'card' ? 'block' : 'none';
        walletDiv.style.display = r.value === 'ewallet' ? 'block' : 'none';
    });
});

shippingRadios.forEach(r => {
    r.addEventListener('change', ()=> {
        const shipFee = r.value === 'delivery' ? 50 : 0;
        document.getElementById('shipFee').textContent = shipFee.toFixed(2);
        const merchTotal = parseFloat(document.getElementById('merchTotal').textContent.replace(/,/g,''));
        document.getElementById('totalPayment').textContent = (merchTotal + shipFee).toFixed(2);
    });
});
</script>

<?php include('../includes/footer.php'); ?>
