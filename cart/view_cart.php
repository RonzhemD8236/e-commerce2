<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
// print_r($_SESSION);
?>

<br>
<h1 align="center">View Cart</h1>
<div class="cart-view-table-back">
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
                $total = 0; // Initialize total outside the if block

                if (isset($_SESSION["cart_products"])) {
                    $b = 0; // zebra stripe
                    foreach ($_SESSION["cart_products"] as $cart_itm) {
                        $product_qty = $cart_itm["item_qty"];
                        $product_price = $cart_itm["item_price"];
                        $product_code = $cart_itm["item_id"];
                        $subtotal = ($product_price * $product_qty);
                        $bg_color = ($b++ % 2 == 1) ? 'odd' : 'even';

                        // ✅ Get item_name or fallback to DB
                        if (isset($cart_itm["item_name"]) && !empty($cart_itm["item_name"])) {
                            $product_name = $cart_itm["item_name"];
                        } else {
                            // Fetch from DB if not present
                            $stmt = $conn->prepare("SELECT description FROM item WHERE item_id = ?");
                            $stmt->bind_param("i", $product_code);
                            $stmt->execute();
                            $stmt->bind_result($product_name);
                            $stmt->fetch();
                            $stmt->close();
                            if (empty($product_name)) {
                                $product_name = "Unnamed Product";
                            }
                        }

                        echo '<tr class="' . $bg_color . '">';
                        echo '<td>' . htmlspecialchars($product_name) . '</td>';
                        echo '<td><input type="text" size="2" maxlength="2" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" /></td>';
                        echo '<td>' . $product_price . '</td>';
                        echo '<td>' . $subtotal . '</td>';
                        echo '<td><input type="checkbox" name="remove_code[]" value="' . $product_code . '" /></td>';
                        echo '</tr>';
                        $total += $subtotal;
                    }
                }
                ?>
                <tr>
                    <td colspan="5">
                        <div style="text-align: center;"> <br>
                        Amount Payable : <?= sprintf("%01.2f", $total); ?>
</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5"><a href="index.php" class="button">Add More Items</a>
                        <br>
                        <button type="submit">Update</button>
                        <br>
                        <a href="checkout.php" class="button">Checkout</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<?php
include('../includes/footer.php');
?>
