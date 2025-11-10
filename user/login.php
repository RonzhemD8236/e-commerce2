<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT id, email, password, role, active FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $user_email, $hashed_password, $role, $active);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();

        if (!$active) {
            $_SESSION['message'] = 'Your account has been deactivated. Please contact admin.';
        } else if (password_verify($password, $hashed_password)) {
            $_SESSION['email'] = $user_email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['message'] = 'Wrong email or password';
        }
    } else {
        $_SESSION['message'] = 'Wrong email or password';
    }
}
?>

<br>
<div class="row col-md-8 mx-auto">
    <?php include("../includes/alert.php"); ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" class="form-control" name="email" required />
        </div>

        <div class="mb-3 position-relative">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <span id="togglePassword" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                <i class="bi bi-eye"></i>
            </span>
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4" name="submit">Sign in</button>

        <div class="text-center">
            <p>Not a member? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>

<!-- Bootstrap Icons CDN for eye icon -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>

<?php include("../includes/footer.php"); ?>
