<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');
?>

<style>
    * {
        box-sizing: border-box;
    }
    
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
        color: white;
        padding: 40px;
        border-radius: 12px;
        margin: 20px auto;
        max-width: 1400px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.3; /* Adjust opacity as needed */
        z-index: 1;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .hero-section h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
        font-weight: 700;
    }
    
    .hero-section p {
        font-size: 1em;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.5;
    }
    
    /* Search Section */
    .search-section {
        max-width: 1400px;
        margin: 20px auto;
        text-align: center;
    }
    
    .search-box {
        display: inline-block;
        width: 100%;
        max-width: 500px;
    }
    
    .search-box input {
        width: 100%;
        padding: 12px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 25px;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: #4a0080;
        box-shadow: 0 0 0 3px rgba(74, 0, 128, 0.1);
    }
    
    /* Filter Section */
    .filter-section {
        max-width: 1400px;
        margin: 20px auto 30px;
        display: flex;
        gap: 30px;
        align-items: flex-start;
    }
    
    .filter-sidebar {
        background: white;
        padding: 25px;
        border-radius: 12px;
        width: 280px;
        flex-shrink: 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .filter-sidebar h3 {
        margin-bottom: 20px;
        color: #1a0033;
        font-size: 1.2em;
        font-weight: 700;
    }
    
    .main-content {
        flex: 1;
        min-width: 0;
    }
    
    .filter-header {
        background: white;
        padding: 20px 25px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .results-count {
        color: #666;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .price-filter {
        margin-top: 0;
    }
    
    .price-filter h4 {
        margin-bottom: 15px;
        color: #1a0033;
        font-size: 0.95em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .price-slider-container {
        margin: 15px 0;
    }
    
    .price-slider {
        width: 100%;
        height: 6px;
        background: #e0e0e0;
        border-radius: 5px;
        outline: none;
        -webkit-appearance: none;
    }
    
    .price-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: #4a0080;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .price-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #4a0080;
        cursor: pointer;
        border-radius: 50%;
        border: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .price-values {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        font-weight: 600;
        font-size: 0.9em;
        color: #4a0080;
    }
    
    .reset-btn {
        background: #1a0033;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 20px;
        transition: all 0.3s;
        width: 100%;
        font-weight: 600;
    }
    
    .reset-btn:hover {
        background: #4a0080;
        transform: translateY(-2px);
    }
    
    /* Products Grid */
    .products-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .products-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
        padding: 20px 0;
    }
    
.product {
    width: 300%;
    height: 300px;
    object-fit: cover;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
}

    
    .product:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .product.out-of-stock {
        opacity: 0.6;
    }
    
    .product > a {
        text-decoration: none;
        color: black;
        width: 100%;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .product-thumb {
        position: relative;
        width: 100%;
        height: 250px;
        overflow: hidden;
        background: #f9f9f9;
    }
    
    .product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        transition: opacity 1s ease, transform 0.3s ease;
    }
    
    .product:hover .product-thumb img {
        transform: scale(1.05);
    }
    
    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #4caf50;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        z-index: 10;
    }
    
    .stock-badge.out {
        background: #f44336;
    }
    
    .product-info {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-name {
        font-size: 1.1em;
        font-weight: 600;
        color: #1a0033;
        margin-bottom: 10px;
        line-height: 1.4;
    }
    
    .product-price {
        font-size: 1.5em;
        font-weight: 700;
        color: #4a0080;
        margin-top: auto;
        text-align: center;
    }
    
    .product-price.out-of-stock {
        color: #f44336;
        font-size: 1.1em;
    }
    
    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #666;
        display: none;
    }
    
    .no-results h3 {
        font-size: 1.5em;
        margin-bottom: 10px;
    }
    
    @media (max-width: 768px) {
        .hero-section h1 {
            font-size: 1.8em;
        }
        
        .hero-section p {
            font-size: 0.95em;
        }
        
        .filter-section {
            flex-direction: column;
        }
        
        .filter-sidebar {
            width: 100%;
        }
        
        .product {
            flex: 1 1 calc(50% - 20px);
        }
    }
    
    @media (max-width: 480px) {
        .product {
            flex: 1 1 100%;
            max-width: 100%;
        }
    }
</style>

<!-- Hero Section -->
<div class="hero-section">
    <!-- Add your banner image here by replacing 'path/to/your/banner-image.jpg' with the actual path -->
    <img src="path/to/your/banner-image.jpg" alt="Banner Image" class="hero-image">
    <div class="hero-content">
        <h1>Discover Our Products</h1>
        <p>Explore our carefully curated collection of premium products. From the latest trends to timeless classics, find exactly what you're looking for with our easy search and filter options.</p>
    </div>
</div>

<!-- Search Section -->
<div class="search-section">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="🔍 Search products...">
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <!-- Left Sidebar - Filters -->
    <div class="filter-sidebar">
        <h3>Filters</h3>
        
        <div class="price-filter">
            <h4>Price Range</h4>
            <div class="price-slider-container">
                <input type="range" id="minPrice" class="price-slider" min="0" max="50000" value="0" step="100">
                <div class="price-values">
                    <span>Min: ₱<span id="minPriceValue">0</span></span>
            </div>
            </div>
            <div class="price-slider-container">
                <input type="range" id="maxPrice" class="price-slider" min="0" max="50000" value="50000" step="100">
                <div class="price-values">
                    <span>Max: ₱<span id="maxPriceValue">50,000</span></span>
                </div>
            </div>
            <button class="reset-btn" onclick="resetFilters()">Reset Filters</button>
        </div>
    </div>
    
        <div class="products-grid" id="productsGrid">
    <?php
    $sql = "SELECT i.item_id AS itemId, i.description AS item_name, i.image_path, i.sell_price, s.quantity AS stock
            FROM item i
            INNER JOIN stock s USING(item_id)
            ORDER BY i.item_id ASC";
    $results = mysqli_query($conn, $sql);

    if ($results) {
        while ($row = mysqli_fetch_assoc($results)) {

            $item_name = htmlspecialchars($row['item_name']);
            $price = number_format($row['sell_price'], 2);
            $itemId = $row['itemId'];
            $stock = (int)$row['stock'];
            $raw_price = $row['sell_price'];

            // Handle multiple images
            $images = json_decode($row['image_path'], true) ?: ['uploads/default.png'];
            $images = array_map(function($img){ return file_exists($img) ? $img : 'uploads/default.png'; }, $images);

            $stockClass = ($stock > 0) ? '' : 'out-of-stock';
            $stockBadge = ($stock > 0) ? 'In Stock' : 'Out of Stock';
            $stockBadgeClass = ($stock > 0) ? '' : 'out';

            echo '<div class="product ' . $stockClass . '" data-price="' . $raw_price . '" data-name="' . strtolower($item_name) . '">';

            echo '<a href="product_details.php?id=' . $itemId . '">';

            // Image container with stock badge
            echo '<div class="product-thumb">';
            echo '<span class="stock-badge ' . $stockBadgeClass . '">' . $stockBadge . '</span>';

            foreach ($images as $index => $img) {
                $cacheBuster = file_exists($img) ? filemtime($img) : time();
                echo '<img src="' . $img . '?v=' . $cacheBuster . '" class="rotating-image" style="opacity:' . ($index === 0 ? 1 : 0) . ';">';
            }
            echo '</div>';

            // Product info
            echo '<div class="product-info">';
            echo '<div class="product-name">' . $item_name . '</div>';
            echo '<div class="product-price ' . $stockClass . '">';
            echo ($stock > 0) ? '₱' . $price : 'OUT OF STOCK';
            echo '</div>';
            echo '</div>';

            echo '</a>';
            echo '</div>';
        }
    }
    ?>
    </div>
        
        <div class="no-results" id="noResults">
            <h3>No products found</h3>
            <p>Try adjusting your search or filter criteria</p>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Rotating image script
document.querySelectorAll('.product-thumb').forEach(function(container) {
    const imgs = container.querySelectorAll('img.rotating-image');
    let current = 0;
    if(imgs.length > 1){
        setInterval(function() {
            imgs[current].style.opacity = 0;
            current = (current + 1) % imgs.length;
            imgs[current].style.opacity = 1;
        }, 5000);
    }
});

// Filter functionality
const searchInput = document.getElementById('searchInput');
const minPriceSlider = document.getElementById('minPrice');
const maxPriceSlider = document.getElementById('maxPrice');
const minPriceValue = document.getElementById('minPriceValue');
const maxPriceValue = document.getElementById('maxPriceValue');
const productsGrid = document.getElementById('productsGrid');
const noResults = document.getElementById('noResults');
const resultsCount = document.getElementById('resultsCount');

// Update price values display
minPriceSlider.addEventListener('input', function() {
    minPriceValue.textContent = parseInt(this.value).toLocaleString();
    if (parseInt(this.value) > parseInt(maxPriceSlider.value)) {
        this.value = maxPriceSlider.value;
        minPriceValue.textContent = parseInt(this.value).toLocaleString();
    }
    filterProducts();
});

maxPriceSlider.addEventListener('input', function() {
    maxPriceValue.textContent = parseInt(this.value).toLocaleString();
    if (parseInt(this.value) < parseInt(minPriceSlider.value)) {
        this.value = minPriceSlider.value;
        maxPriceValue.textContent = parseInt(this.value).toLocaleString();
    }
    filterProducts();
});

// Search functionality
searchInput.addEventListener('input', filterProducts);

function filterProducts() {
    const searchTerm = searchInput.value.toLowerCase();
    const minPrice = parseInt(minPriceSlider.value);
    const maxPrice = parseInt(maxPriceSlider.value);
    const products = document.querySelectorAll('.product');
    let visibleCount = 0;
    
    products.forEach(product => {
        const productName = product.getAttribute('data-name');
        const productPrice = parseFloat(product.getAttribute('data-price'));
        
        const matchesSearch = productName.includes(searchTerm);
        const matchesPrice = productPrice >= minPrice && productPrice <= maxPrice;
        
        if (matchesSearch && matchesPrice) {
            product.style.display = 'flex';
            visibleCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    if (visibleCount === 0) {
        productsGrid.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        productsGrid.style.display = 'flex';
        noResults.style.display = 'none';
    }
    
    // Update results count
    resultsCount.textContent = `Showing ${visibleCount} product${visibleCount !== 1 ? 's' : ''}`;
}

function resetFilters() {
    searchInput.value = '';
    minPriceSlider.value = 0;
    maxPriceSlider.value = 50000;
    minPriceValue.textContent = '0';
    maxPriceValue.textContent = '50,000';
    filterProducts();
}

// Initial count
filterProducts();
</script>

<?php
include('./includes/footer.php');
?>
