<?php
include('../admin/header.php'); // Admin header
include('../includes/config.php');

// Determine if a category filter is applied (you can add this later if needed)
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

// Check for search keyword
if (isset($_GET['search'])) {
    $keyword = strtolower(trim($_GET['search']));
} else {
    $keyword = '';
}

// Query items
if ($keyword) {
    $sql = "SELECT * FROM item LEFT JOIN stock USING (item_id) WHERE description LIKE '%{$keyword}%'";
} else {
    $sql = "SELECT * FROM item LEFT JOIN stock USING (item_id)";
}
$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);
?>

<style>
/* Custom button colors - Black and Lavender palette */
.btn-success {
    background-color: black !important;
    border-color: black !important;
    color: white !important;
}

.btn-success:hover {
    background-color: gray !important;
    border-color: gray !important;
}

.btn-warning {
    background-color: #2d2d2d !important;
    border-color: #2d2d2d !important;
    color: white !important;
}

.btn-warning:hover {
    background-color: #1a1a1a !important;
    border-color: #1a1a1a !important;
}

.btn-danger {
    background-color: #4a4a4a !important;
    border-color: #4a4a4a !important;
    color: white !important;
}

.btn-danger:hover {
    background-color: #363636 !important;
    border-color: #363636 !important;
}

.category-btns .category-btn {
    background-color: white;
    color: #333;
    border: none;
    border-radius: 0;
    margin-right: 10px;
    padding: 8px 16px;
    position: relative;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.category-btns .category-btn.active {
    color: gray;
    border-bottom: 2px solid gray;
}

table th, table td {
    vertical-align: middle;
    text-align: center;
}

table th:first-child, table td:first-child {
    width: 80px;
}

table th:nth-child(2), table td:nth-child(2) {
    width: 180px;
}

table {
    table-layout: fixed;
    width: 100%;
}

/* Full width container with background image */
.header-container {
    width: 100%;
    background-image: url('https://i.pinimg.com/736x/f6/4f/65/f64f65ff3bb28459e934fa38db43dd99.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 60px 50px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

/* Dark overlay for better text readability */
.header-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1;
}

.header-container .text-content {
    position: relative;
    z-index: 2;
    color: white;
    max-width: 800px;
}

.header-container .text-content h1 {
    margin: 0 0 15px 0;
    font-size: 42px;
    font-weight: bold;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.header-container .text-content p {
    margin: 0;
    font-size: 17px;
    color: rgba(255, 255, 255, 0.95);
    line-height: 1.6;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

/* Search bar styling */
#searchInput {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 15px;
    font-size: 15px;
    transition: all 0.3s ease;
}

#searchInput:focus {
    border-color: #9b87f5;
    box-shadow: 0 0 0 0.2rem rgba(155, 135, 245, 0.25);
    outline: none;
}

/* Action icon styling */
.action-icon {
    font-size: 18px;
    transition: all 0.2s ease;
    margin: 0 5px;
}

.action-icon:hover {
    transform: scale(1.2);
}

.stats-badge {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 20px;
    display: inline-block;
}

.stats-badge strong {
    color: #333;
    font-size: 16px;
}

/* Product image styling */
.product-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.product-image:hover {
    transform: scale(1.05);
}
</style>

<script>
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.querySelector('.table');
    const rows = table.getElementsByTagName('tr');
    
    // Start from 1 to skip the header row
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        // Search through Description column (index 2)
        if (cells[2]) {
            const cellText = cells[2].textContent || cells[2].innerText;
            if (cellText.toLowerCase().indexOf(filter) > -1) {
                found = true;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}
</script>

<div class="container-fluid px-4">
    <!-- Full Width Header with Background Image -->
    <div class="header-container">
        <div class="text-content">
            <h1>Product Management</h1>
            <p>Manage your product inventory, add new items, update pricing, and track stock levels. Keep your catalog organized and up-to-date.</p>
        </div>
    </div>

    <?php include("../includes/alert.php"); ?>

    <!-- Header Row with Search and Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="search-container" style="flex: 1; max-width: 400px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Search products..." onkeyup="searchTable()">
        </div>
        <a href="create.php" class="btn btn-success">Add New Item</a>
    </div>
    <br><br>

    <!-- Category Filter Buttons (Optional - can be activated later) -->
    <div class="mb-3 category-btns">
        <a href="index.php" class="category-btn <?= $categoryFilter == '' ? 'active' : '' ?>">All Products</a>
        <!-- Add more category filters here if needed -->
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Selling Price</th>
                <th>Cost Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['item_id'] ?></td>
                <td>
                    <?php
                    $imagePath = $row['image_path'];
                    
                    if (!empty($imagePath)) {
                        // Handle both absolute and relative paths
                        if (file_exists("../" . $imagePath)) {
                            echo "<img src='../{$imagePath}' class='product-image' alt='Product Image' />";
                        } elseif (file_exists($imagePath)) {
                            echo "<img src='{$imagePath}' class='product-image' alt='Product Image' />";
                        } else {
                            echo "<img src='../uploads/default.png' class='product-image' alt='Default Image' />";
                        }
                    } else {
                        echo "<img src='../uploads/default.png' class='product-image' alt='Default Image' />";
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>₱<?= number_format($row['sell_price'], 2) ?></td>
                <td>₱<?= number_format($row['cost_price'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $row['item_id'] ?>" title="Edit Item">
                        <i class="fa-regular fa-pen-to-square action-icon" style="color: #c8b6ff"></i>
                    </a>
                    <a href="delete.php?id=<?= $row['item_id'] ?>" 
                       onclick="return confirm('Are you sure you want to delete this item?');"
                       title="Delete Item">
                        <i class="fa-solid fa-trash action-icon" style="color: #dc3545"></i>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div class="stats-badge mt-3">
        <strong>Total Items: <?= $itemCount ?></strong>
    </div>
</div>

<?php include('../includes/footer.php'); ?>