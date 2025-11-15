<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

// Get selected category if any
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

// Define all categories with icons
$categories = [
    'DSLR Cameras' => 'fa-camera',
    'Mirrorless Cameras' => 'fa-camera-retro',
    'Action Cameras' => 'fa-video',
    'Camera Lenses' => 'fa-circle-dot',
    'Tripods & Stabilizers' => 'fa-dharmachakra',
    'Camera Accessories' => 'fa-toolbox'
];

// If a category is selected, fetch products
$products = [];
if ($selectedCategory) {
    $safeCat = mysqli_real_escape_string($conn, $selectedCategory);
    $sql = "SELECT i.*, s.quantity AS stock 
            FROM item i
            LEFT JOIN stock s ON i.item_id = s.item_id
            WHERE i.category = '$safeCat'
            ORDER BY i.item_id DESC";
    
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
}
?>

<div class="container mt-5 mb-5">
    <?php if (!$selectedCategory): ?>
        <!-- Category Selection View -->
        <h1 class="text-center mb-4">Browse by Category</h1>
        <p class="text-center text-muted mb-5">Choose a category to explore our products</p>
        
        <div class="row g-4">
            <?php foreach ($categories as $catName => $icon): ?>
                <div class="col-md-4">
                    <a href="?category=<?= urlencode($catName) ?>" class="text-decoration-none">
                        <div class="category-box p-4 text-center border rounded shadow-sm h-100" style="transition: all 0.3s; cursor: pointer;">
                            <i class="fas <?= $icon ?> fa-3x mb-3" style="color: #000000ff;"></i>
                            <h4 class="mb-0"><?= htmlspecialchars($catName) ?></h4>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <style>
            .category-box:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
                background-color: #f8f9fa;
            }
        </style>
        
    <?php else: ?>
        <!-- Products View for Selected Category -->
        <div class="mb-4">
            <a href="categories.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>
        
        <h1 class="mb-4"><?= htmlspecialchars($selectedCategory) ?></h1>
        
        <?php if (empty($products)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No products found in this category.
            </div>
        <?php else: ?>
            <p class="text-muted mb-4"><?= count($products) ?> product(s) found</p>
            
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <?php
                    // Handle images
                    $images = [];
                    if (!empty($product['image_path'])) {
                        $cleanPath = stripslashes($product['image_path']);
                        $decoded = json_decode($cleanPath, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $images = $decoded;
                        } else {
                            $images = [$cleanPath];
                        }
                    }
                    
                    // Get first image
                    $firstImage = !empty($images) ? $images[0] : 'https://via.placeholder.com/300x200?text=No+Image';
                    if (strpos($firstImage, 'http') !== 0) {
                        $firstImage = ltrim($firstImage, '/');
                        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'];
                        $firstImage = $scheme . '://' . $host . '/e-commerce2/' . $firstImage;
                    }
                    
                    $stock = (int)$product['stock'];
                    $inStock = $stock > 0;
                    ?>
                    
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm" style="transition: transform 0.3s;">
                            <a href="product_details.php?id=<?= $product['item_id'] ?>" class="text-decoration-none">
                                <img src="<?= htmlspecialchars($firstImage) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($product['description']) ?>"
                                     style="height: 200px; object-fit: cover;"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                
                                <div class="card-body">
                                    <h6 class="card-title text-dark" style="height: 48px; overflow: hidden;">
                                        <?= htmlspecialchars($product['description']) ?>
                                    </h6>
                                    
                                    <?php if (!empty($product['short_description'])): ?>
                                        <p class="card-text text-muted small" style="height: 40px; overflow: hidden;">
                                            <?= htmlspecialchars($product['short_description']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="h5 mb-0 text-success">₱<?= number_format($product['sell_price'], 2) ?></span>
                                        <?php if ($inStock): ?>
                                            <small class="text-muted">Stock: <?= $stock ?></small>
                                        <?php else: ?>
                                            <small class="text-danger fw-bold">Out of Stock</small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button class="btn btn-primary w-100 mt-3">
                                        View Details
                                    </button>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
    }
    
    .card a {
        color: inherit;
    }
    
    .card a:hover {
        text-decoration: none;
    }
</style>

<?php include('./includes/footer.php'); ?>