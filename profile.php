<?php
session_start();
require "includes/database_connect.php";
if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    die();
}
include "includes/header.php";
$user_id = $_SESSION['user_id'];

$sql_1 = "SELECT * FROM users WHERE id = $user_id";
$result_1 = mysqli_query($conn, $sql_1);
if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$user = mysqli_fetch_assoc($result_1);
if (!$user) {
    echo "Something went wrong!";
    return;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Your Profile | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>

</head>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb py-2">
        <li class="breadcrumb-item">
            <a href="index.php">Home</a>
        </li>
        <li class="breadcrumb-item">
            <a href="dashboard.php">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Profile
        </li>
    </ol>
</nav>

<div class="container mt-5">
    <h2>Edit Your Profile</h2>
    <form action="api/update_profile.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter your full name" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile Picture:</label>
            <input type="file" class="form-control" id="profile_picture" name="profile_picture">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>