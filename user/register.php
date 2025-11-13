<?php
session_start();
include("../includes/header.php");

// Get errors and old input if available
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']); // clear after use
?>
<br>
<div class="container-fluid container-lg">
    <?php include("../includes/alert.php"); ?>
    <form action="store.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" 
                   value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
            <?php if(isset($errors['username'])): ?>
                <small class="text-danger"><?= $errors['username'] ?></small>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            <?php if(isset($errors['email'])): ?>
                <small class="text-danger"><?= $errors['email'] ?></small>
            <?php endif; ?>
        </div>

        <!-- Password Field with simple Show/Hide -->
        <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <span id="togglePassword" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                <i class="bi bi-eye"></i>
            </span>
            <?php if(isset($errors['password'])): ?>
                <small class="text-danger"><?= $errors['password'] ?></small>
            <?php endif; ?>
        </div>

        <!-- Confirm Password Field with simple Show/Hide -->
        <div class="mb-3 position-relative">
            <label for="password2" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password2" name="confirmPass" required>
            <span id="togglePassword2" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                <i class="bi bi-eye"></i>
            </span>
        </div>

         <!-- Register and Cancel Buttons -->
        <div class="mb-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="/lensify/e-commerce2/user/login.php" class="btn btn-secondary">Cancel</a>
        </div>

        <?php if(isset($errors['general'])): ?>
            <div class="alert alert-danger mt-2"><?= $errors['general'] ?></div>
        <?php endif; ?>
    </form>
</div>

<style>
    /* Background image only for login page */
    body {
        background: url('https://i.pinimg.com/736x/69/69/b9/6969b990fc444e1aa1af2ef880df8fa8.jpg') no-repeat center center fixed;
        background-size: cover;
    }
</style>

<!-- Bootstrap Icons CDN for eye icon -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Toggle Password Visibility Script -->
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

document.getElementById('togglePassword2').addEventListener('click', function () {
    const passwordField = document.getElementById('password2');
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
