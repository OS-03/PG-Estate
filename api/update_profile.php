<?php



session_start();
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require 'cloudinary_config.php';
require "../includes/database_connect.php";
use Cloudinary\Uploader;

// $cloudinary = new Cloudinary();
// $uploadApi = new UploadApi($cloudinary);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "is_logged_in" => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$profile = $_FILES['profile_picture'];


$target_file = $profile["tmp_name"];
$imageFileType = strtolower(pathinfo($profile["name"], PATHINFO_EXTENSION));

// Upload to Cloudinary
try {
    $upload_result = Uploader::upload($target_file, [
        "folder" => "profile_pictures/",
        "public_id" => pathinfo($profile["name"], PATHINFO_FILENAME),
        "overwrite" => true,
        "resource_type" => "image"
    ]);
    $target_file = $upload_result['secure_url'];
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Cloudinary upload failed: " . $e->getMessage()]);
    exit();
}

// Check if image file is a valid image
$check = getimagesize($profile["tmp_name"]);
if ($check === false) {
    echo json_encode(["success" => false, "message" => "File is not an image."]);
    exit();
}

// Check file size (limit to 2MB)
if ($profile["size"] > 2000000) {
    echo json_encode(["success" => false, "message" => "File is too large."]);
    exit();
}

// Allow certain file formats
if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
    echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG & GIF files are allowed."]);
    exit();
}

// Attempt to upload file
if (!move_uploaded_file($profile["tmp_name"], $target_file)) {
    echo json_encode(["success" => false, "message" => "There was an error uploading your file."]);
    exit();
}

$profile_picture = $target_file;

$result = mysqli_query($conn, $sql);
if (!$result) {
    $response = array("success" => false, "message" => "Something went wrong!");
    echo json_encode($response);
    return;
}
$row_count = mysqli_num_rows($result);
if ($row_count != 0) {
    $response = array("success" => false, "message" => "This email id is already registered with us!");
    echo json_encode($response);
    return;
}

$sql = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone', profile_picture = '$profile_picture' WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $response = array("success" => false, "message" => "Something went wrong!");
    echo json_encode($response);
    return;
}

$response = array("success" => true, "message" => "Profile updated successfully!");
echo json_encode($response);
mysqli_close($conn);

// Redirect to dashboard
header("Location: /dashboard.php");
exit();
