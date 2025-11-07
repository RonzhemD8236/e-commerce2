<?php
session_start();
include('../includes/config.php');

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $created_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO users (name, email, password, role, created_at) 
            VALUES ('$name', '$email', '$password', '$role', '$created_at')";
    if(mysqli_query($conn, $sql)){
        header("Location: index.php");
        exit();
    } else {
        die("Error: ".mysqli_error($conn));
    }
}
?>
