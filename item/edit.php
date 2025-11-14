<?php
session_start();
include('../includes/config.php');
include('../admin/header.php'); // Admin header

// Check if item_id is provided
if (!isset($_GET['id'])) {
    echo "<script>
            alert('No item selected.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$item_id = intval($_GET['id']);

// Fetch item details
$sql = "SELECT item.*, stock.quantity 
        FROM item 
        LEFT JOIN stock USING (item_id) 
        WHERE item.item_id = $item_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "<script>
            alert('Item not found.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$row = mysqli_fetch_assoc($result);

// Determine the current image path
$currentImagePath = '';
if (!empty($row['image_path'])) {
    if (file_exists("../" . $row['image_path'])) {
        $currentImagePath = "../" . $row['image_path'];
    } elseif (file_exists($row['image_path'])) {
        $currentImagePath = $row['image_path'];
    } else {
        $currentImagePath = "../uploads/default.png";
    }
} else {
    $currentImagePath = "../uploads/default.png";
}
?>

<style>
.preview-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.preview-image:hover {
    transform: scale(1.05);
}

.image-preview-container {
    position: relative;
    display: inline-block;
}

.btn-success {
    background-color: black !important;
    border-color: black !important;
    color: white !important;
}

.btn-success:hover {
    background-color: gray !important;
    border-color: gray !important;
}

.btn-secondary {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.btn-secondary:hover {
    background-color: #5a6268 !important;
    border-color: #5a6268 !important;
}
</style>

<script>
// Live image preview when file is selected
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(file);
    }
}
</script>

<div class="container mt-4">
    <h2 class="mb-4">Edit Item</h2>
    
    <form action="update.php" method="POST" enctype="multipart/form-data" style="max-width: 800px;">
        <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">

        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="desc" class="form-control" value="<?= htmlspecialchars($row['description']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Cost Price</label>
            <input type="number" step="0.01" name="cost" class="form-control" value="<?= $row['cost_price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Sell Price</label>
            <input type="number" step="0.01" name="sell" class="form-control" value="<?= $row['sell_price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="qty" class="form-control" value="<?= $row['quantity'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Product Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
            
            <div class="image-preview-container mt-3">
                <img id="imagePreview" src="<?= htmlspecialchars($currentImagePath) ?>" class="preview-image" alt="Item Image">
            </div>
            
            <small class="text-muted d-block mt-2">Leave blank to keep the current image. Select a new file to see preview.</small>
        </div>

        <div class="text-left">
            <button type="submit" class="btn btn-success">Update Item</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<br>

<?php include('../includes/footer.php'); ?>