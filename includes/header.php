<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="includes/style/style.css" rel="stylesheet" type="text/css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <title>Shop</title>
  <style>
    .profile-img {
      width: 35px;
      height: 35px;
      object-fit: cover;
      border-radius: 50%;
      margin-right: 8px;
    }
    .nav-link.dropdown-toggle.d-flex {
      align-items: center;
    }
    .cart-icon {
      position: relative;
    }
    .cart-count {
      position: absolute;
      top: -5px;
      right: -10px;
      background-color: red;
      color: white;
      font-size: 12px;
      font-weight: bold;
      padding: 2px 5px;
      border-radius: 50%;
    }
    form.d-flex input {
      width: 200px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Lensify</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Left side links -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" href="/lensify/e-commerce2/index.php">Home</a>
          </li>

          <?php $role = $_SESSION['role'] ?? 'user'; ?>
          <?php if($role === 'user'): ?>
            <li class="nav-item">
              <a class="nav-link" href="/lensify/e-commerce2/contact.php">Contact / Support</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="/lensify/e-commerce2/item/index.php">Product Management</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/lensify/e-commerce2/admin/orders.php">Orders Management</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/lensify/e-commerce2/admin/index.php">Users Management</a>
            </li>
          <?php endif; ?>

          <!-- Search bar -->
          <li class="nav-item">
            <form class="d-flex" role="search" action="/lensify/e-commerce2/search.php" method="GET">
              <input class="form-control me-2" type="search" name="q" placeholder="Search products..." aria-label="Search">
              <button class="btn btn-outline-success" type="submit"><i class="fas fa-search"></i></button>
            </form>
          </li>
        </ul>

        <!-- Right side: Cart and Profile -->
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <!-- Cart icon -->
          <li class="nav-item me-3">
            <a href="/lensify/e-commerce2/cart.php" class="nav-link cart-icon">
              <i class="fas fa-shopping-cart fa-lg"></i>
              <?php
                $cartCount = $_SESSION['cart_count'] ?? 0;
                if ($cartCount > 0) {
                  echo "<span class='cart-count'>{$cartCount}</span>";
                }
              ?>
            </a>
          </li>

          <!-- User profile / My Account dropdown -->
          <?php if (isset($_SESSION['user_id'])): 
            $profileImg = $_SESSION['profile_img'] ?? '/lensify/e-commerce2/uploads/default-profile.png';
          ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= $profileImg ?>" alt="Profile" class="profile-img">
              My Account
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/lensify/e-commerce2/user/profile.php">Edit Profile</a></li>

              <?php if($role === 'user'): ?>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/account.php">Account Settings</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/myorders.php">My Orders</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/purchased.php">Purchased Products</a></li>
                <li><a class="dropdown-item" href="/lensify/e-commerce2/user/payment.php">Payment Methods / Addresses</a></li>
              <?php endif; ?>

              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="http://<?= $_SERVER['SERVER_NAME'] ?>/lensify/e-commerce2/user/logout.php">Logout</a></li>
            </ul>
          </li>
          <?php else: ?>
          <li class="nav-item">
            <a href="http://<?= $_SERVER['SERVER_NAME'] ?>/lensify/e-commerce2/user/login.php" class="nav-link">Login</a>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
</body>

</html>
