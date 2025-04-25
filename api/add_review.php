<?php
session_start();

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require "../includes/database_connect.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "is_logged_in" => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$content = $_POST['review'] ?? null;
$property_id = $_POST['property_id'] ?? null;

if (!$content || !$property_id) {
    echo json_encode(["success" => false, "message" => "Invalid input!"]);
    exit();
}


$sql_latest_id = "SELECT MAX(id) AS latest_id FROM testimonials";
$result = $conn->query($sql_latest_id);
$latest_id = ($result && $row = $result->fetch_assoc()) ? $row['latest_id'] + 1 : 1;

$sql = "INSERT INTO testimonials (id, property_id, user_name, content) 
    VALUES ($latest_id, $property_id, (SELECT full_name FROM users WHERE id = $user_id), '$content')";

if ($conn->query($sql) === TRUE) {
    $response = ["success" => true, "message" => "Review added successfully!"];
} else {
    $response = ["success" => false, "message" => "Failed to add review."];
}

echo json_encode($response);
$conn->close();

header("Location: /PGLIFE/property_detail.php?property_id=$property_id");
exit();
