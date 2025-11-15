<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('No product selected.'); window.location.href='index.php';</script>";
    exit;
}

$itemId = intval($_GET['id']);

// Fetch product with stock
$sql = "
    SELECT i.*, s.quantity AS stock 
    FROM item i
    LEFT JOIN stock s ON i.item_id = s.item_id
    WHERE i.item_id = $itemId
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Product not found.'); window.location.href='index.php';</script>";
    exit;
}

$row = mysqli_fetch_assoc($result);

// Debug: Show raw image_path from database
echo "<!-- DEBUG: Raw image_path from DB: " . htmlspecialchars($row['image_path']) . " -->";

// Handle images - Simple approach
$images = [];

if (!empty($row['image_path'])) {
    // Remove escaped slashes that might cause JSON decode issues
    $cleanPath = stripslashes($row['image_path']);
    echo "<!-- DEBUG: After stripslashes: " . htmlspecialchars($cleanPath) . " -->";
    
    // Try to decode as JSON
    $decoded = json_decode($cleanPath, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $images = $decoded;
        echo "<!-- DEBUG: Successfully decoded JSON, found " . count($images) . " images -->";
    } else {
        // Not JSON, treat as single path
        $images = [$cleanPath];
        echo "<!-- DEBUG: Not JSON, treating as single image. JSON Error: " . json_last_error_msg() . " -->";
    }
}

// If no images found, use placeholder
if (empty($images)) {
    $images = ['https://via.placeholder.com/400x300?text=No+Image'];
    echo "<!-- DEBUG: No images, using placeholder -->";
}

// Process each image to create full URLs
$processedImages = [];
foreach ($images as $img) {
    // Skip empty values
    if (empty(trim($img))) {
        continue;
    }
    
    // If already full URL, use as-is
    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
        $processedImages[] = $img;
        echo "<!-- DEBUG: Full URL: $img -->";
        continue;
    }
    
    // Remove any leading slashes
    $img = ltrim($img, '/');
    
    // Build full URL - uploads folder is inside e-commerce2 folder
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fullUrl = $scheme . '://' . $host . '/e-commerce2/' . $img;
    
    $processedImages[] = $fullUrl;
    echo "<!-- DEBUG: Converted '$img' to '$fullUrl' -->";
    
    // Check if file exists on server
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/e-commerce2/' . $img;
    if (file_exists($filePath)) {
        echo "<!-- DEBUG: ✓ File EXISTS at: $filePath -->";
    } else {
        echo "<!-- DEBUG: ✗ File NOT FOUND at: $filePath -->";
    }
}

// If no valid images after processing, use placeholder
if (empty($processedImages)) {
    $processedImages = ['https://via.placeholder.com/400x300?text=Image+Not+Found'];
    echo "<!-- DEBUG: No valid images after processing, using placeholder -->";
}

$images = $processedImages;
echo "<!-- DEBUG: Final image URLs: " . json_encode($images) . " -->";

$stock = (int)$row['stock'];
$inStock = $stock > 0;
?>

<div class="container mt-5">
    <div class="row">
        <!-- Image slider -->
        <div class="col-md-5 d-flex align-items-end justify-content-center">
            <div class="image-slider position-relative" style="width:100%; max-height:450px; min-height:450px; overflow:hidden; border-radius:10px; border:1px solid #ddd; background:#f5f5f5;">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" 
                             class="slider-image" 
                             alt="Product Image <?= $index + 1 ?>"
                             style="width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; transition: opacity 0.5s ease; opacity:<?= $index === 0 ? 1 : 0 ?>;"
                             onerror="console.error('❌ Failed to load:', '<?= htmlspecialchars($img, ENT_QUOTES) ?>'); this.style.display='none';"
                             onload="console.log('✓ Loaded:', '<?= htmlspecialchars($img, ENT_QUOTES) ?>');">
                    <?php endforeach; ?>
                    
                    <!-- Navigation Buttons -->
                    <?php if (count($images) > 1): ?>
                        <button class="slider-btn prev-btn" onclick="changeSlide(-1)" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:18px; z-index:10;">
                            ‹
                        </button>
                        <button class="slider-btn next-btn" onclick="changeSlide(1)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:18px; z-index:10;">
                            ›
                        </button>
                        
                        <!-- Image Counter -->
                        <div class="image-counter" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:white; padding:5px 15px; border-radius:20px; font-size:14px; z-index:10;">
                            <span id="currentImageNum">1</span> / <?= count($images) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Fallback text if all images fail -->
                    <div class="no-image-text" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); display:none; text-align:center; color:#999;">
                        <p>Image not available</p>
                    </div>
                <?php else: ?>
                    <p style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">No images available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product details -->
        <div class="col-md-7">
            <h2><?= htmlspecialchars($row['description']) ?></h2>

            <?php if (!empty($row['short_description'])): ?>
                <p><strong>Summary:</strong> <?= htmlspecialchars($row['short_description']) ?></p>
            <?php endif; ?>

            <?php if (!empty($row['specifications'])): ?>
                <p><strong>Specifications:</strong><br><?= nl2br(htmlspecialchars($row['specifications'])) ?></p>
            <?php endif; ?>

            <h4 class="text-success">₱<?= number_format($row['sell_price'], 2) ?></h4>

            <p><strong>Available Stock:</strong>
                <?php if ($inStock): ?>
                    <span id="availableStock"><?= $stock ?></span>
                <?php else: ?>
                    <span style="color:red; font-size:24px; font-weight:bold;">OUT OF STOCK</span>
                <?php endif; ?>
            </p>

            <?php if ($inStock): ?>
            <form id="addToCartForm" class="mt-3">
                <input type="hidden" name="type" value="add">
                <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
                <input type="hidden" name="item_name" value="<?= htmlspecialchars($row['description']) ?>">
                <input type="hidden" name="item_price" value="<?= $row['sell_price'] ?>">

                <div class="mb-3">
                    <label>Quantity:</label>
                    <input type="number" name="item_qty" id="quantity"
                           value="1" min="1" max="<?= $stock ?>"
                           class="form-control" style="width:100px;">
                </div>

                <button type="submit" class="btn btn-success btn-lg">Add to Cart</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($inStock): ?>
<script>
// Image slider with manual controls only (no auto-slide)
let currentSlide = 0;
const imgs = document.querySelectorAll('.slider-image');
const visibleImages = Array.from(imgs).filter(img => img.style.display !== 'none');

function showSlide(n) {
    if (visibleImages.length === 0) {
        const fallback = document.querySelector('.no-image-text');
        if (fallback) fallback.style.display = 'block';
        return;
    }
    
    // Hide all images
    visibleImages.forEach(img => img.style.opacity = 0);
    
    // Wrap around
    if (n >= visibleImages.length) currentSlide = 0;
    if (n < 0) currentSlide = visibleImages.length - 1;
    
    // Show current image
    visibleImages[currentSlide].style.opacity = 1;
    
    // Update counter
    const counter = document.getElementById('currentImageNum');
    if (counter) counter.textContent = currentSlide + 1;
}

function changeSlide(direction) {
    currentSlide += direction;
    showSlide(currentSlide);
}

// Initialize slider
if (visibleImages.length > 0) {
    showSlide(0);
}

// Add hover effect to buttons
document.querySelectorAll('.slider-btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.background = 'rgba(0,0,0,0.8)';
    });
    btn.addEventListener('mouseleave', function() {
        this.style.background = 'rgba(0,0,0,0.5)';
    });
});

// Add to cart
document.getElementById("addToCartForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("cart/cart_update.php", { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Item added to cart!");
            const stockSpan = document.getElementById("availableStock");
            stockSpan.textContent = data.newStock;
            document.getElementById("quantity").max = data.newStock;
            if (data.newStock == 0) {
                document.getElementById("quantity").remove();
                document.querySelector("#addToCartForm button").remove();
                stockSpan.outerHTML = '<span style="color:red; font-size:24px; font-weight:bold;">OUT OF STOCK</span>';
            }
        } else {
            alert(data.message || "Failed to add item.");
        }
    }).catch(err => { console.error(err); alert("Error adding to cart."); });
});
</script>
<?php endif; ?>

<?php include('./includes/footer.php'); ?>