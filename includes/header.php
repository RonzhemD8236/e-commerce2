<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Default to 'guest' if not logged in
$role = $_SESSION['role'] ?? 'guest';

// ✅ Determine correct CSS path based on current directory
$current_dir = dirname($_SERVER['PHP_SELF']);
if (strpos($current_dir, '/user') !== false || 
    strpos($current_dir, '/admin') !== false || 
    strpos($current_dir, '/item') !== false) {
    $css_path = '../includes/style/style.css';
} else {
    $css_path = 'includes/style/style.css';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <!-- Bootstrap Icons (for password toggle) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  
  <!-- ✅ Custom CSS - ONLY ONE LINK -->
  <link href="<?php echo htmlspecialchars($css_path); ?>" rel="stylesheet" type="text/css">
  
  <title>Lensify</title>

  <style>
    /* Keep small header-only styles here */
    .profile-img {
      width: 35px;
      height: 35px;
      object-fit: cover;
      border-radius: 50%;
      margin-right: 8px;
    }
    .nav-link.dropdown-toggle.d-flex { align-items: center; }
    .cart-icon { position: relative; }
    .cart-count {
      position: absolute; top: -5px; right: -10px;
      color: white; font-size: 12px;
      background-color: rgba(0, 0, 0, 0.7);
      font-weight: bold; padding: 2px 5px; border-radius: 50%;
    }

    
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
              data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" 
              aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" href="/lensify/e-commerce2/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/lensify/e-commerce2/categories.php">Categories</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/lensify/e-commerce2/products.php">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/lensify/e-commerce2/contact.php">Contact</a>
          </li>
        </ul>
        <center><strong><a class="navbar-brand" href="#">Lensify</a></strong></center>

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item me-3">
            <a href="/lensify/e-commerce2/cart.php" class="nav-link cart-icon">
              <i class="fas fa-shopping-cart fa-lg"></i>
              <?php
                $cartCount = $_SESSION['cart_count'] ?? 0;
                if ($cartCount > 0) echo "<span class='cart-count'>{$cartCount}</span>";
              ?>
            </a>
          </li>

          <?php if (isset($_SESSION['user_id'])):
            $profileImg = $_SESSION['profile_img'] ?? '/lensify/e-commerce2/uploads/default-profile.png';
          ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= htmlspecialchars($profileImg) ?>" alt="Profile" class="profile-img">
                My Account
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/profile.php">Edit Profile</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/account.php">Account Settings</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/myorders.php">My Orders</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/purchased.php">Purchased Products</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/payment.php">Payment Methods / Addresses</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/logout.php">Logout</a></li>

              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a href="/lensify/e-commerce2/user/login.php" class="nav-link">Login</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- PAGE CONTENT STARTS BELOW -->
  <main class="container">