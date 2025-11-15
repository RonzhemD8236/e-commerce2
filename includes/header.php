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
    <title>Lensify - Your Camera Shop</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="<?php echo htmlspecialchars($css_path); ?>" rel="stylesheet" type="text/css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: 100%;
        }
        
        body {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
        }
        
        /* Fixed Navbar at Top */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #fff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: #333;
            padding: 0.5rem 1rem;
        }
        
        .nav-link:hover {
            color: #0d6efd;
        }
        
        /* Main Content Area - Takes remaining space */
        .main-content {
            flex: 1 0 auto;
            margin-top: 70px;
            width: 100%;
        }
        
        /* Footer will be pushed to bottom naturally */
        .footer {
            flex-shrink: 0;
        }

        /* Profile & Cart Styles */
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
            color: white; 
            font-size: 12px;
            background-color: rgba(0, 0, 0, 0.7);
            font-weight: bold; 
            padding: 2px 5px; 
            border-radius: 50%;
        }
    </style>
</head>
<body>

<!-- FIXED NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/e-commerce2/homepage.php">📸 Lensify</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/e-commerce2/categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/e-commerce2/index.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/e-commerce2/contact.php">Contact</a>
                </li>
            </ul>
            
            <!-- Right Side Nav Items -->
            <ul class="navbar-nav">
                <!-- Shopping Cart -->
                <li class="nav-item me-3">
                    <a href="/e-commerce2/cart/view_cart.php" class="nav-link cart-icon">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php
                            $cartCount = $_SESSION['cart_count'] ?? 0;
                            if ($cartCount > 0) echo "<span class='cart-count'>{$cartCount}</span>";
                        ?>
                    </a>
                </li>

                <!-- User Account / Login -->
                <?php if (isset($_SESSION['user_id'])):
                    $profileImg = $_SESSION['profile_img'] ?? '/e-commerce2/uploads/default-profile.png';
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="profile-img">
                            My Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/e-commerce2/user/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/e-commerce2/user/myorders.php">My Orders</a></li>
                            <li><a class="dropdown-item" href="/e-commerce2/user/purchased.php">Purchased Products</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/e-commerce2/user/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/e-commerce2/user/login.php" class="nav-link">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- PAGE CONTENT STARTS BELOW -->