<?php
session_start();
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require __DIR__ . '/../vendor/autoload.php';
require 'cloudinary_config.php';

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

// Ensure $cloudinary_config is loaded
if (!isset($cloudinary_config['cloud_name'], $cloudinary_config['api_key'], $cloudinary_config['api_secret'])) {
    echo json_encode(["success" => false, "message" => "Cloudinary config missing"]);
    exit();
}

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $cloudinary_config['cloud_name'],
        'api_key'    => $cloudinary_config['api_key'],
        'api_secret' => $cloudinary_config['api_secret'],
    ],
    'url' => [
        'secure' => true
    ]
]);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Collect user details from POST
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$college_name = isset($_POST['college_name']) ? trim($_POST['college_name']) : '';
$profile_picture = null;

// Handle profile picture upload if present
if (
    isset($_FILES['profile_picture']) &&
    $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE
) {
    if (
        isset($_FILES['profile_picture']['tmp_name']) &&
        $_FILES['profile_picture']['tmp_name'] !== '' &&
        $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK &&
        is_uploaded_file($_FILES['profile_picture']['tmp_name'])
    ) {
        $profile = $_FILES['profile_picture'];
        $imageFileType = strtolower(pathinfo($profile["name"], PATHINFO_EXTENSION));
        $check = getimagesize($profile["tmp_name"]);
        if ($check === false) {
            echo json_encode(["success" => false, "message" => "File is not an image."]);
            exit();
        }
        if ($profile["size"] > 2000000) {
            echo json_encode(["success" => false, "message" => "File is too large."]);
            exit();
        }
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG & GIF files are allowed."]);
            exit();
        }
        // Upload to Cloudinary
        try {
            $uploadApi = new UploadApi();
            $upload_result = $uploadApi->upload($profile["tmp_name"], [
                "folder" => "profile_pictures/",
                "public_id" => pathinfo($profile["name"], PATHINFO_FILENAME) . "_" . $user_id,
                "overwrite" => true
            ]);
            $profile_picture = $upload_result['secure_url'];
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Cloudinary upload failed: " . $e->getMessage()]);
            exit();
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No profile image uploaded or upload error. Make sure your form uses enctype=\"multipart/form-data\" and PHP upload limits are sufficient."
        ]);
        exit();
    }
}

// Update user's details in DB
require "../includes/database_connect.php";
$name_escaped = mysqli_real_escape_string($conn, $full_name);
$email_escaped = mysqli_real_escape_string($conn, $email);
$phone_escaped = mysqli_real_escape_string($conn, $phone);
$gender_escaped = mysqli_real_escape_string($conn, $gender);
$college_escaped = mysqli_real_escape_string($conn, $college_name);

// Only update fields that are not empty (prevents overwriting with empty values)
$update_fields_arr = [];
if ($full_name !== '') $update_fields_arr[] = "full_name='$name_escaped'";
if ($email !== '') $update_fields_arr[] = "email='$email_escaped'";
if ($phone !== '') $update_fields_arr[] = "phone='$phone_escaped'";
if ($gender !== '') $update_fields_arr[] = "gender='$gender_escaped'";
if ($college_name !== '') $update_fields_arr[] = "college_name='$college_escaped'";
if ($profile_picture) {
    $profile_picture_escaped = mysqli_real_escape_string($conn, $profile_picture);
    $update_fields_arr[] = "profile_picture='$profile_picture_escaped'";
}

if (count($update_fields_arr) === 0) {
    echo json_encode(["success" => false, "message" => "No data to update."]);
    exit();
}

$update_fields = implode(", ", $update_fields_arr);
$sql = "UPDATE users SET $update_fields WHERE id=$user_id";
$result = mysqli_query($conn, $sql);

if ($result) {
    header("Location: /PGLife/dashboard.php");
    exit();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update user details in database. Error: " . mysqli_error($conn)
    ]);
}
exit();
