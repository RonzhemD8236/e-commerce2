<?php
session_start();

// Include database connection
include('../includes/config.php');
include('../includes/header.php'); // for navbar

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect if the logged-in user is not a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /shop/admin/index.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch username and email from users table
$sqlUser = "SELECT username, email FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userRow = $resultUser->fetch_assoc();
$username = $userRow['username'] ?? '';
$email = $userRow['email'] ?? '';

// Fetch existing profile from customer table
$sql = "SELECT * FROM customer WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// If profile doesn't exist, insert a new one (after getting username & email)
if (!$profile) {
    $sqlInsert = "INSERT INTO customer 
                  (user_id, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, email, image_path)
                  VALUES (?, '', '', '', '', 'Philippines', 'Metro Manila', '', '', '', ?, '')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("is", $userId, $email);
    $stmtInsert->execute();

    // Refetch the profile
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
}

// Merge username from users table so it populates the form
$profile['username'] = $username;
$profile['email'] = $email;

// Detect if this is the first time the user is filling the profile
$firstTime = false;
if ($profile && empty($profile['fname']) && empty($profile['lname']) && empty($profile['addressline'])) {
    $firstTime = true;
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Get customer profile fields
    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $country = trim($_POST['country']);
    $state = trim($_POST['state']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $imagePath = $profile['image_path'] ?? '';

    // Get username from form
    $usernameForm = trim($_POST['username']);

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes) && $_FILES["image"]["size"] <= 5 * 1024 * 1024) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imagePath = "uploads/" . $fileName;
            } else {
                $_SESSION['error'] = "Error uploading image.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type or file too large.";
        }
    }

    // Update customer table
    $sqlUpdateProfile = "UPDATE customer 
                         SET lname=?, fname=?, addressline=?, town=?, country=?, state=?, zipcode=?, phone=?, date_of_birth=?, image_path=?, email=?
                         WHERE user_id=?";
    $stmtUpdate = $conn->prepare($sqlUpdateProfile);
    $stmtUpdate->bind_param(
        "sssssssssssi",
        $lname,
        $fname,
        $address,
        $town,
        $country,
        $state,
        $zipcode,
        $phone,
        $date_of_birth,
        $imagePath,
        $email,
        $userId
    );
    $stmtUpdate->execute();

    // Update username in users table
    $sqlUpdateUser = "UPDATE users SET username=? WHERE id=?";
    $stmtUserUpdate = $conn->prepare($sqlUpdateUser);
    $stmtUserUpdate->bind_param("si", $usernameForm, $userId);
    $stmtUserUpdate->execute();

    $_SESSION['success'] = 'Profile saved successfully!';
    if ($firstTime) {
        header("Location: /lensify/e-commerce2/index.php");
    } else {
        header("Location: /lensify/e-commerce2/user/profile.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>

    <nav class="nav nav-borders">
        <a class="nav-link active ms-0">Profile</a>
    </nav>
    <hr class="mt-0 mb-4">

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Profile Picture -->
            <div class="col-xl-4">
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <img id="profilePreview" class="img-account-profile rounded-circle mb-2"
                             src="<?php echo !empty($profile['image_path']) ? '../' . htmlspecialchars($profile['image_path']) : 'http://bootdey.com/img/Content/avatar/avatar1.png'; ?>"
                             alt="Profile Image" width="200" height="200">
                        <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>

                        <input type="file" id="imageInput" name="image" accept=".jpg,.jpeg,.png,.gif" style="display: none;">
                        <button class="btn btn-primary" type="button" id="uploadButton">Upload new image</button>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">Account Details</div>
                    <div class="card-body">

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">First name</label>
                                <input class="form-control" type="text" name="fname" value="<?php echo htmlspecialchars($profile['fname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Last name</label>
                                <input class="form-control" type="text" name="lname" value="<?php echo htmlspecialchars($profile['lname'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Username</label>
                                <input class="form-control" type="text" name="username" value="<?php echo htmlspecialchars($profile['username'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Email</label>
                                <input class="form-control" type="email" name="email" 
                                    value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Address</label>
                                <input class="form-control" type="text" name="address" value="<?php echo htmlspecialchars($profile['addressline'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Town</label>
                                <input class="form-control" type="text" name="town" value="<?php echo htmlspecialchars($profile['town'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Country</label>
                                <input class="form-control" type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? 'Philippines'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">State</label>
                                <input class="form-control" type="text" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? 'Metro Manila'); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Phone number</label>
                                <input class="form-control" type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Zip code</label>
                                <input class="form-control" type="text" name="zipcode" value="<?php echo htmlspecialchars($profile['zipcode'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Date of Birth</label>
                                <input class="form-control" type="date" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" name="submit">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Open file picker when button is clicked
document.getElementById('uploadButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

// Show preview immediately after selecting a file
document.getElementById('imageInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
