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

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <?php if(isset($errors['password'])): ?>
                <small class="text-danger"><?= $errors['password'] ?></small>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="password2" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password2" name="confirmPass" required>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>

        <?php if(isset($errors['general'])): ?>
            <div class="alert alert-danger mt-2"><?= $errors['general'] ?></div>
        <?php endif; ?>
    </form>
</div>

<?php
include("../includes/footer.php");
?>
