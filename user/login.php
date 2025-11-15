<?php
session_start();
include("../includes/config.php"); // DB connection

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../homepage.php");
    exit();
}

// Handle login form submission
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $sql = "SELECT id, email, password, role, active FROM users WHERE email=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $user_email, $hashed_password, $role, $active);

        if ($stmt->num_rows === 1) {
            $stmt->fetch();

            if (!$active) {
                $errors[] = 'Your account has been deactivated. Please contact admin.';
            } elseif (password_verify($password, $hashed_password)) {
                $_SESSION['email'] = $user_email;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;
                header("Location: ../index.php");
                exit();
            } else {
                $errors[] = 'Wrong email or password.';
            }
        } else {
            $errors[] = 'Wrong email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Lensify</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: url('../uploads/login-bg.jpeg') no-repeat center center;
    background-size: cover;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

body::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 0;
}

.login-container {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.85);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 400px;
}

.login-container h3 {
    text-align: center;
    margin-bottom: 1.5rem;
}

.btn-signin {
    background-color: #000;
    color: #fff;
    border: none;
    padding: 0.5rem;
    width: 100%;
    cursor: pointer;
    border-radius: 5px;
}

.btn-signin:hover {
    background-color: #333;
}

.text-danger {
    color: red;
    font-size: 0.9rem;
}
</style>
</head>
<body>

<div class="login-container">
    <?php if(!empty($errors)): ?>
        <div class="text-danger mb-3">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" id="loginForm">
        <h3>Login</h3>
        <div style="margin-bottom: 1rem;">
            <label>Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width:100%; padding:0.5rem;">
        </div>

        <div style="margin-bottom: 1rem; position:relative;">
            <label>Password</label>
            <input type="password" name="password" id="password" style="width:100%; padding:0.5rem;">
            <span id="togglePassword" style="position:absolute; right:10px; top:35%; cursor:pointer;">👁️</span>
        </div>

        <button type="submit" name="submit" class="btn-signin">Login</button>
        <p style="text-align:center; margin-top:1rem;">Not a member? <a href="register.php">Register</a></p>
    </form>
</div>

<script>
// Password toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    if (pwd.type === 'password') {
        pwd.type = 'text';
    } else {
        pwd.type = 'password';
    }
});
</script>

</body>
</html>
