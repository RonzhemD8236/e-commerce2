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

<style>
/* ========================================
   CATEGORIES PAGE STYLES
   ======================================== */

/* Page Wrapper - Add top padding for fixed navbar */
.categories-page-wrapper {
    width: 100%;
    background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
    min-height: calc(100vh - 140px);
    padding-top: 40px;
    padding-bottom: 60px;
}

/* Hero Header Section */
.categories-hero {
    background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
    color: white;
    padding: 60px 20px;
    text-align: center;
    margin-bottom: 50px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.categories-hero h1 {
    font-size: 3em;
    font-weight: 700;
    margin-bottom: 15px;
    letter-spacing: -1px;
}

.categories-hero p {
    font-size: 1.3em;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
}

/* Container */
.categories-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Category Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 35px;
    margin-top: 30px;
}

/* Category Card */
.category-card {
    background: white;
    padding: 50px 30px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 2px solid transparent;
    text-decoration: none;
    display: block;
    position: relative;
    overflow: hidden;
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
    transition: left 0.4s ease;
    z-index: 0;
}

.category-card:hover::before {
    left: 0;
}

.category-card > * {
    position: relative;
    z-index: 1;
}

.category-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-color: #4a0080;
}

.category-icon {
    font-size: 4em;
    color: #4a0080;
    margin-bottom: 25px;
    transition: all 0.4s ease;
}

.category-card:hover .category-icon {
    color: white;
    transform: scale(1.15);
}

.category-name {
    font-size: 1.5em;
    font-weight: 700;
    color: #1a0033;
    margin: 0;
    transition: color 0.4s ease;
}

.category-card:hover .category-name {
    color: white;
}

/* Products View */
.products-view {
    margin-top: 30px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: white;
    color: #1a0033;
    padding: 12px 25px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.back-button:hover {
    background: #4a0080;
    color: white;
    transform: translateX(-5px);
}

.category-title {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.category-title h1 {
    font-size: 2.5em;
    font-weight: 700;
    color: #1a0033;
    margin-bottom: 10px;
}

.product-count {
    color: #666;
    font-size: 1.1em;
    font-weight: 500;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

/* Product Card */
.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: block;
}

.product-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.product-image {
    width: 100%;
    height: 280px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-image-container {
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
    height: 280px;
}

.product-info {
    padding: 25px;
}

.product-title {
    font-size: 1.1em;
    font-weight: 700;
    color: #1a0033;
    margin-bottom: 10px;
    min-height: 50px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-description {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 15px;
    min-height: 40px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.product-price {
    font-size: 1.6em;
    font-weight: 800;
    color: #4a0080;
}

.product-stock {
    font-size: 0.9em;
    color: #666;
}

.product-stock.out {
    color: #f44336;
    font-weight: 700;
}

.view-details-btn {
    background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
    color: white;
    padding: 12px 25px;
    border-radius: 12px;
    text-align: center;
    font-weight: 700;
    margin-top: 20px;
    display: block;
    transition: all 0.3s;
}

.product-card:hover .view-details-btn {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(74, 0, 128, 0.4);
}

/* No Products Message */
.no-products {
    background: white;
    padding: 80px 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.no-products i {
    font-size: 4em;
    color: #4a0080;
    margin-bottom: 20px;
}

.no-products h3 {
    font-size: 1.8em;
    color: #1a0033;
    margin-bottom: 15px;
}

.no-products p {
    color: #666;
    font-size: 1.1em;
}

/* Responsive Design */
@media (max-width: 992px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }
    
    .categories-hero h1 {
        font-size: 2.5em;
    }
}

@media (max-width: 768px) {
    .categories-page-wrapper {
        padding-top: 20px;
    }
    
    .categories-hero {
        padding: 40px 20px;
        margin-bottom: 30px;
    }
    
    .categories-hero h1 {
        font-size: 2em;
    }
    
    .categories-hero p {
        font-size: 1.1em;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .categories-hero h1 {
        font-size: 1.75em;
    }
    
    .category-card {
        padding: 40px 20px;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="categories-page-wrapper">
    <?php if (!$selectedCategory): ?>
        <!-- Category Selection View -->
        <div class="categories-hero">
            <h1>Browse by Category</h1>
            <p>Choose a category to explore our premium products</p>
        </div>
        
        <div class="categories-container">
            <div class="categories-grid">
                <?php foreach ($categories as $catName => $icon): ?>
                    <a href="?category=<?= urlencode($catName) ?>" class="category-card">
                        <i class="fas <?= $icon ?> category-icon"></i>
                        <h4 class="category-name"><?= htmlspecialchars($catName) ?></h4>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Products View for Selected Category -->
        <div class="categories-container products-view">
            <a href="categories.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
            
            <div class="category-title">
                <h1><?= htmlspecialchars($selectedCategory) ?></h1>
                <p class="product-count">
                    <?php if (empty($products)): ?>
                        No products found
                    <?php else: ?>
                        <?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> available
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products Found</h3>
                    <p>We don't have any products in this category yet. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
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
                        $firstImage = !empty($images) ? $images[0] : 'uploads/default.png';
                        
                        $stock = (int)$product['stock'];
                        $inStock = $stock > 0;
                        ?>
                        
                        <a href="product_details.php?id=<?= $product['item_id'] ?>" class="product-card">
                            <div class="product-image-container">
                                <img src="<?= htmlspecialchars($firstImage) ?>" 
                                     class="product-image" 
                                     alt="<?= htmlspecialchars($product['description']) ?>"
                                     onerror="this.src='uploads/default.png'">
                            </div>
                            
                            <div class="product-info">
                                <h6 class="product-title">
                                    <?= htmlspecialchars($product['description']) ?>
                                </h6>
                                
                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="product-description">
                                        <?= htmlspecialchars($product['short_description']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="product-footer">
                                    <span class="product-price">₱<?= number_format($product['sell_price'], 2) ?></span>
                                    <?php if ($inStock): ?>
                                        <small class="product-stock">Stock: <?= $stock ?></small>
                                    <?php else: ?>
                                        <small class="product-stock out">Out of Stock</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="view-details-btn">
                                    View Details
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include('./includes/footer.php'); ?>