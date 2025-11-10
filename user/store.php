<?php
session_start();
include("../includes/config.php");

// Get form data
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPass = trim($_POST['confirmPass']);

// Check if passwords match
if ($password !== $confirmPass) {
    $_SESSION['message'] = 'Passwords do not match';
    header("Location: register.php");
    exit();
}

// Hash password securely
$passwordHashed = password_hash($password, PASSWORD_DEFAULT);

// Insert new user with username
$sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $passwordHashed);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // Insert blank profile linked to this user
    $sqlProfile = "INSERT INTO customer 
                   (user_id, email, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, image_path) 
                   VALUES (?, ?, '', '', '', '', 'Philippines', 'Metro Manila', '', '', '', '')";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("is", $userId, $email);
    $stmtProfile->execute();

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;

    header("Location: /lensify/e-commerce2/user/profile.php");
    exit();
} else {
    $_SESSION['message'] = 'Registration failed. Email or username may already be in use.';
    header("Location: register.php");
    exit();
}
?>
