<?php
session_start();
include('../includes/config.php');

// ---------------------- ADD TO CART ----------------------
if (isset($_POST["type"]) && $_POST["type"] === 'add' && isset($_POST["item_qty"]) && $_POST["item_qty"] > 0) {
    $item_id = (int)$_POST["item_id"];
    $item_name = htmlspecialchars(trim($_POST["item_name"]));
    $item_price = (float)$_POST["item_price"];
    $item_qty = (int)$_POST["item_qty"];

    $new_product = [
        "item_id"    => $item_id,
        "item_name"  => $item_name,
        "item_price" => $item_price,
        "item_qty"   => $item_qty
    ];

    // ✅ If item already exists, add quantity
    if (isset($_SESSION["cart_products"][$item_id])) {
        $_SESSION["cart_products"][$item_id]["item_qty"] += $item_qty;
    } else {
        $_SESSION["cart_products"][$item_id] = $new_product;
    }

    header("Location: ../index.php");
    exit();
}

// ---------------------- UPDATE OR REMOVE ITEMS ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Update item quantities
    if (!empty($_POST["product_qty"])) {
        foreach ($_POST["product_qty"] as $item_id => $qty) {
            $item_id = (int)$item_id;
            $qty = (int)$qty;

            if (isset($_SESSION["cart_products"][$item_id])) {
                if ($qty > 0) {
                    $_SESSION["cart_products"][$item_id]["item_qty"] = $qty;
                } else {
                    // Remove if quantity is 0
                    unset($_SESSION["cart_products"][$item_id]);
                }
            }
        }
    }

    // ✅ Remove selected items (checkbox)
    if (!empty($_POST["remove_code"])) {
        foreach ($_POST["remove_code"] as $item_id) {
            $item_id = (int)$item_id;
            if (isset($_SESSION["cart_products"][$item_id])) {
                unset($_SESSION["cart_products"][$item_id]);
            }
        }
    }

    header("Location: ../index.php");
    exit();
}

// ---------------------- FALLBACK ----------------------
header("Location: ../index.php");
exit();
