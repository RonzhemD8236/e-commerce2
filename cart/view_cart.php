<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
?>
<div class="main-content">
<h1 align="center">View Cart</h1>

<form method="POST" action="cart_update.php">
<table width="100%" cellpadding="6" cellspacing="0">
<thead>
<tr>
    <th>Name</th>
    <th>Quantity</th>
    <th>Price</th>
    <th>Total</th>
    <th>Remove</th>
</tr>
</thead>
<tbody>

<?php
$total = 0;

if (!empty($_SESSION['cart_products'])) {

    foreach ($_SESSION['cart_products'] as $id => $itm) {

        $name  = htmlspecialchars($itm['item_name']);
        $qty   = (int)$itm['item_qty'];
        $price = (float)$itm['item_price'];
        $subtotal = $qty * $price;
        $total += $subtotal;

        // Get current stock from DB
        $stmt = $conn->prepare("SELECT quantity FROM stock WHERE item_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($stock);
        $stmt->fetch();
        $stmt->close();

        if ($stock < 0) { $stock = 0; }

        // Total available = current DB stock + quantity in cart
        $availableForCart = $stock + $qty;

        echo "<tr>
                <td>$name</td>
                <td>
                    <input type='number' 
                           name='product_qty[$id]' 
                           value='$qty' 
                           min='1' 
                           max='$availableForCart'
                           style='width:60px;'>
                    <small style='color:gray;'>Max: $availableForCart</small>
                </td>
                <td>₱" . number_format($price, 2) . "</td>
                <td>₱" . number_format($subtotal, 2) . "</td>
                <td><input type='checkbox' name='remove_code[]' value='$id'></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; color:red;'>Your cart is empty.</td></tr>";
}
?>

<tr>
    <td colspan="5" style="text-align:center; font-size:18px;">
        <br><strong>Amount Payable: ₱<?= number_format($total, 2); ?></strong><br><br>
    </td>
</tr>

<tr>
    <td colspan="5" style="text-align:center;">
        <button type="submit" name="update_cart" style="padding:8px 18px;">Update Cart</button>
        <a href="../index.php" style="margin-left:20px;">Add More Items</a>
        <a href="checkout.php" style="margin-left:20px;">Checkout</a>
    </td>
</tr>

</tbody>
</table>
</form>
</div>

<?php include('../includes/footer.php'); ?>
