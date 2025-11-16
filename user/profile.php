<?php
ob_start();
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect if user is not a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /lensify/e-commerce2/admin/profile.php");
    } else {
        header("Location: /lensify/e-commerce2/user/login.php");
    }
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch username and email
$sqlUser = "SELECT username, email FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userRow = $resultUser->fetch_assoc();
$username = $userRow['username'] ?? '';
$email = $userRow['email'] ?? '';

// Fetch profile
$sql = "SELECT * FROM customer WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Insert if missing
if (!$profile) {
    $sqlInsert = "INSERT INTO customer 
                  (user_id, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, email, image_path)
                  VALUES (?, '', '', '', '', 'Philippines', 'Metro Manila', '', '', '', ?, '')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("is", $userId, $email);
    $stmtInsert->execute();
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
}

// Merge username/email
$profile['username'] = $username;
$profile['email'] = $email;

// Detect first-time
$firstTime = false;
if ($profile && empty($profile['fname']) && empty($profile['lname']) && empty($profile['addressline'])) {
    $firstTime = true;
}

// Get any previous errors from session
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// Handle form submission
if (isset($_POST['submit'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $usernameForm = trim($_POST['username']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $country = trim($_POST['country']);
    $state = trim($_POST['state']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['date_of_birth']);
    $imagePath = $profile['image_path'] ?? '';

    $errors = [];

    // Validate DOB
    if (!empty($dob)) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $dob);
        $errorsInDate = DateTime::getLastErrors();

        if (!$dateObj || $errorsInDate['warning_count'] > 0 || $errorsInDate['error_count'] > 0) {
            $errors['date_of_birth'] = "Please enter a valid date in YYYY-MM-DD format.";
        } elseif ($dob > date('Y-m-d')) {
            $errors['date_of_birth'] = "Date of Birth cannot be a future date.";
        }
    }

    // Validate required fields
    foreach (['fname','lname','username','address','town','country','state','zipcode','phone','date_of_birth'] as $field) {
        if (empty($_POST[$field])) $errors[$field] = ucfirst(str_replace('_',' ',$field)) . " is required.";
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg','jpeg','png','gif'];

        if (in_array($fileType, $allowedTypes) && $_FILES["image"]["size"] <= 5*1024*1024) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imagePath = "uploads/" . $fileName;
            } else {
                $errors['image'] = "Error uploading image.";
            }
        } else {
            $errors['image'] = "Invalid file type or too large (>5MB).";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Update customer profile
    $sqlUpdate = "UPDATE customer SET fname=?, lname=?, addressline=?, town=?, country=?, state=?, zipcode=?, phone=?, date_of_birth=?, image_path=?, email=? WHERE user_id=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sssssssssssi",$fname,$lname,$address,$town,$country,$state,$zipcode,$phone,$dob,$imagePath,$email,$userId);
    $stmtUpdate->execute();

    // Update username
    $sqlUserUpdate = "UPDATE users SET username=? WHERE id=?";
    $stmtUserUpdate = $conn->prepare($sqlUserUpdate);
    $stmtUserUpdate->bind_param("si",$usernameForm,$userId);
    $stmtUserUpdate->execute();

    $_SESSION['success'] = "Profile saved successfully!";
    header("Location: " . ($firstTime ? "/lensify/e-commerce2/index.php" : "/lensify/e-commerce2/user/profile.php"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Setup - Lensify</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-image: url('../uploads/homepage.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        min-height: 100vh;
        position: relative;
    }

    /* Fallback gradient if image doesn't exist */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -2;
    }

    /* Dark overlay for better readability */
    body::after {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: -1;
    }

    <?php if($firstTime): ?>
    nav.navbar { 
        display: none !important; 
    }
    <?php endif; ?>

    .profile-container {
        padding: 40px 20px;
        min-height: 100vh;
    }

    .welcome-banner {
        background: linear-gradient(135deg, rgba(20, 20, 20, 0.95) 0%, rgba(40, 40, 40, 0.95) 100%);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .welcome-banner h1 {
        color: white;
        font-size: 2.5em;
        font-weight: 700;
        margin-bottom: 15px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .welcome-banner p {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1.2em;
        margin: 0;
    }

    .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
    }

    .card-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        color: white;
        padding: 20px 30px;
        font-size: 1.3em;
        font-weight: 700;
        border: none;
        letter-spacing: 0.5px;
    }

    .card-body {
        padding: 30px;
    }

    .profile-image-container {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto 20px;
    }

    .img-account-profile {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #333;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s ease;
    }

    .img-account-profile:hover {
        transform: scale(1.05);
    }

    .upload-overlay {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        cursor: pointer;
    }

    .profile-image-container:hover .upload-overlay {
        opacity: 1;
    }

    .upload-overlay i {
        color: white;
        font-size: 2em;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: #333;
        width: 20px;
    }

    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        outline: none;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }

    .btn-primary {
        background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
        border: none;
        padding: 14px 40px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(139, 92, 246, 0.6);
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
    }

    .upload-btn {
        background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }

    .upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
    }

    .upload-info {
        color: #666;
        font-size: 13px;
        font-style: italic;
        margin: 15px 0;
    }

    .text-danger {
        display: block;
        margin-top: 5px;
        font-size: 13px;
        font-weight: 500;
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
    }

    .alert-success {
        background: rgba(76, 175, 80, 0.95);
        color: white;
    }

    .alert-danger {
        background: rgba(244, 67, 54, 0.95);
        color: white;
    }

    .input-group {
        margin-bottom: 20px;
    }

    .section-divider {
        border-top: 2px solid #e0e0e0;
        margin: 30px 0;
        position: relative;
    }

    .section-divider::after {
        content: '✦';
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 0 15px;
        color: #8b5cf6;
        font-size: 20px;
    }

    @media (max-width: 768px) {
        .welcome-banner h1 {
            font-size: 2em;
        }

        .welcome-banner {
            padding: 30px 20px;
        }

        .card-body {
            padding: 20px;
        }

        .profile-image-container,
        .img-account-profile,
        .upload-overlay {
            width: 150px;
            height: 150px;
        }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
        border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
    }
</style>
</head>
<body>
<div class="container-xl profile-container">
    <?php include("../includes/alert.php"); ?>

    <?php if($firstTime): ?>
    <div class="welcome-banner">
        <h1><i class="fas fa-sparkles"></i> Welcome to Lensify!</h1>
        <p>Complete your profile to start shopping</p>
    </div>
    <?php else: ?>
    <div class="welcome-banner">
        <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>
        <p>Update your account information</p>
    </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Profile Picture Card -->
            <div class="col-xl-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-image"></i> Profile Picture
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-image-container">
                            <img id="profilePreview" class="img-account-profile"
                                 src="<?php echo !empty($profile['image_path']) ? '../'.htmlspecialchars($profile['image_path']) : '../uploads/default-profile.png'; ?>"
                                 alt="Profile Image">
                            <div class="upload-overlay" id="uploadOverlay">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <?php if(isset($errors['image'])): ?>
                            <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['image']; ?></small>
                        <?php endif; ?>
                        <div class="upload-info">
                            <i class="fas fa-info-circle"></i> JPG or PNG • Max 5MB
                        </div>
                        <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;">
                        <button type="button" class="upload-btn" id="uploadButton">
                            <i class="fas fa-upload"></i> Upload Image
                        </button>
                    </div>
                </div>
            </div>

            <!-- Account Details Card -->
            <div class="col-xl-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i> Account Details
                    </div>
                    <div class="card-body">
                        <!-- Name -->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> First Name
                                </label>
                                <input class="form-control" type="text" name="fname" 
                                       value="<?php echo htmlspecialchars($profile['fname'] ?? ''); ?>" 
                                       placeholder="Enter first name">
                                <?php if(isset($errors['fname'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['fname']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Last Name
                                </label>
                                <input class="form-control" type="text" name="lname" 
                                       value="<?php echo htmlspecialchars($profile['lname'] ?? ''); ?>"
                                       placeholder="Enter last name">
                                <?php if(isset($errors['lname'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['lname']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Username & Email -->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-at"></i> Username
                                </label>
                                <input class="form-control" type="text" name="username" 
                                       value="<?php echo htmlspecialchars($profile['username'] ?? ''); ?>"
                                       placeholder="Enter username">
                                <?php if(isset($errors['username'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['username']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input class="form-control" type="text" name="email" 
                                       value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <!-- Address & Town -->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Address
                                </label>
                                <input class="form-control" type="text" name="address" 
                                       value="<?php echo htmlspecialchars($profile['addressline'] ?? ''); ?>"
                                       placeholder="Enter street address">
                                <?php if(isset($errors['address'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['address']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-city"></i> Town/City
                                </label>
                                <input class="form-control" type="text" name="town" 
                                       value="<?php echo htmlspecialchars($profile['town'] ?? ''); ?>"
                                       placeholder="Enter town or city">
                                <?php if(isset($errors['town'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['town']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Country & State -->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-flag"></i> Country
                                </label>
                                <input class="form-control" type="text" name="country" 
                                       value="<?php echo htmlspecialchars($profile['country'] ?? 'Philippines'); ?>"
                                       placeholder="Enter country">
                                <?php if(isset($errors['country'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['country']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-map"></i> State/Province
                                </label>
                                <input class="form-control" type="text" name="state" 
                                       value="<?php echo htmlspecialchars($profile['state'] ?? 'Metro Manila'); ?>"
                                       placeholder="Enter state or province">
                                <?php if(isset($errors['state'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['state']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Phone & Zipcode -->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i> Phone Number
                                </label>
                                <input class="form-control" type="text" name="phone" 
                                       value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>"
                                       placeholder="Enter phone number">
                                <?php if(isset($errors['phone'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['phone']; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-mail-bulk"></i> Zip Code
                                </label>
                                <input class="form-control" type="text" name="zipcode" 
                                       value="<?php echo htmlspecialchars($profile['zipcode'] ?? ''); ?>"
                                       placeholder="Enter zip code">
                                <?php if(isset($errors['zipcode'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['zipcode']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div class="row gx-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-birthday-cake"></i> Date of Birth
                                </label>
                                <input class="form-control" type="date" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
                                <?php if(isset($errors['date_of_birth'])): ?>
                                    <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['date_of_birth']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary" type="submit" name="submit">
                                <i class="fas fa-save"></i> Save Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('uploadButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

document.getElementById('uploadOverlay').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

document.getElementById('imageInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(ev){ 
            document.getElementById('profilePreview').src = ev.target.result; 
        }
        reader.readAsDataURL(file);
    }
});

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>