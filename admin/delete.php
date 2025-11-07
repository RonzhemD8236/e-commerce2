<?php
session_start();
include('../includes/config.php');

$id = $_GET['id'];
$sql = "DELETE FROM users WHERE id=$id";

if(mysqli_query($conn, $sql)){
    header("Location: index.php");
    exit();
} else {
    die("Error: ".mysqli_error($conn));
}
?>
