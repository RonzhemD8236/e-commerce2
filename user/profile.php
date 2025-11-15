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
        // Redirect admin to their own edit profile page
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
    // ✅ Validate DOB
if (!empty($dob)) {
    // Check valid date format (YYYY-MM-DD)
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
<title>Profile Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
<?php if($firstTime): ?>
nav.navbar { display: none !important; }
<?php endif; ?>
.img-account-profile { object-fit: cover; }
</style>
</head>
<body>
<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>

    <?php if($firstTime): ?>
    <div class="text-center mb-4" style="background-color: black; color: #333; padding: 20px; border-radius: 10px;">
        <h4 style="color: white;">Welcome to Lensify!</h4>
        <p style="color: white;">Set up your profile to get started.</p>
    </div>
    <?php endif; ?>


    <form action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-xl-4">
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <img id="profilePreview" class="img-account-profile rounded-circle mb-2"
                             src="<?php echo !empty($profile['image_path']) ? '../'.htmlspecialchars($profile['image_path']) : '../uploads/default-profile.png'; ?>"
                             alt="Profile Image" width="200" height="200">
                        <?php if(isset($errors['image'])): ?><small class="text-danger"><?php echo $errors['image']; ?></small><?php endif; ?>
                        <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                        <input type="file" id="imageInput" name="image" style="display:none;">
                        <button type="button" class="btn btn-primary" id="uploadButton">Upload new image</button>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">Account Details</div>
                    <div class="card-body">

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">First name</label>
                                <input class="form-control" type="text" name="fname" value="<?php echo htmlspecialchars($profile['fname'] ?? ''); ?>">
                                <?php if(isset($errors['fname'])): ?><small class="text-danger"><?php echo $errors['fname']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Last name</label>
                                <input class="form-control" type="text" name="lname" value="<?php echo htmlspecialchars($profile['lname'] ?? ''); ?>">
                                <?php if(isset($errors['lname'])): ?><small class="text-danger"><?php echo $errors['lname']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Username</label>
                                <input class="form-control" type="text" name="username" value="<?php echo htmlspecialchars($profile['username'] ?? ''); ?>">
                                <?php if(isset($errors['username'])): ?><small class="text-danger"><?php echo $errors['username']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Email</label>
                                <input class="form-control" type="text" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Address</label>
                                <input class="form-control" type="text" name="address" value="<?php echo htmlspecialchars($profile['addressline'] ?? ''); ?>">
                                <?php if(isset($errors['address'])): ?><small class="text-danger"><?php echo $errors['address']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Town</label>
                                <input class="form-control" type="text" name="town" value="<?php echo htmlspecialchars($profile['town'] ?? ''); ?>">
                                <?php if(isset($errors['town'])): ?><small class="text-danger"><?php echo $errors['town']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Country</label>
                                <input class="form-control" type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? 'Philippines'); ?>">
                                <?php if(isset($errors['country'])): ?><small class="text-danger"><?php echo $errors['country']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">State</label>
                                <input class="form-control" type="text" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? 'Metro Manila'); ?>">
                                <?php if(isset($errors['state'])): ?><small class="text-danger"><?php echo $errors['state']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Phone number</label>
                                <input class="form-control" type="text" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                                <?php if(isset($errors['phone'])): ?><small class="text-danger"><?php echo $errors['phone']; ?></small><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Zip code</label>
                                <input class="form-control" type="text" name="zipcode" value="<?php echo htmlspecialchars($profile['zipcode'] ?? ''); ?>">
                                <?php if(isset($errors['zipcode'])): ?><small class="text-danger"><?php echo $errors['zipcode']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Date of Birth</label>
                                <input class="form-control" type="text" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" placeholder="YYYY-MM-DD">
                                <?php if(isset($errors['date_of_birth'])): ?><small class="text-danger"><?php echo $errors['date_of_birth']; ?></small><?php endif; ?>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" name="submit">Save</button>
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
document.getElementById('imageInput').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(ev){ document.getElementById('profilePreview').src = ev.target.result; }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
ob_end_flush();
?>
