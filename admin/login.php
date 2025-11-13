<?php
session_start();

// ✅ Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit();
}

include("../includes/header.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    // ✅ PHP Validation
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
                $_SESSION['message'] = 'Your account has been deactivated. Please contact support.';
            } elseif ($role !== 'admin') {
                $_SESSION['message'] = 'Access denied. Admins only.';
            } elseif (password_verify($password, $hashed_password)) {
                $_SESSION['email'] = $user_email;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['message'] = 'Wrong email or password.';
            }
        } else {
            $_SESSION['message'] = 'Wrong email or password.';
        }
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
    }
}
?>

<style>
/* ✅ Hide top nav */
nav.navbar {
    display: none !important;
}

body {
    background: url('../uploads/login-bg.jpeg') no-repeat center center;
    background-size: cover;
    position: relative;
    min-height: 100vh;
    overflow: hidden;
}

body::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 0;
}

.main-content {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 2rem;
}

.login-container {
    background-color: rgba(255,255,255,0.85);
    backdrop-filter: blur(10px);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.btn-signin {
    background-color: #000 !important;
    border-color: #000 !important;
    color: white !important;
}

.btn-signin:hover {
    background-color: #333 !important;
    border-color: #333 !important;
}
</style>

<div class="main-content">
    <div class="content">
        <div class="login-container">
            <?php include("../includes/alert.php"); ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="loginForm">
                <h3 class="text-center mb-4">Admin Login</h3>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="text" class="form-control" name="email" id="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <small class="text-danger" id="emailError"></small>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <span id="togglePassword" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                        <i class="bi bi-eye"></i>
                    </span>
                    <small class="text-danger" id="passwordError"></small>
                </div>

                <button type="submit" class="btn btn-signin w-100 mb-3" name="submit">Login</button>
            </form>
        </div>
    </div>
</div>

<script>
// ✅ Toggle Password Visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    const pw = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// ✅ Client-side Validation
document.getElementById('loginForm').addEventListener('submit', function (e) {
    let valid = true;
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


    if (email === '') {
        document.getElementById('emailError').textContent = 'Email is required.';
        valid = false;
    } else if (!emailPattern.test(email)) {
        document.getElementById('emailError').textContent = 'Invalid email format.';
        valid = false;
    }

    if (password === '') {
        document.getElementById('passwordError').textContent = 'Password is required.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
