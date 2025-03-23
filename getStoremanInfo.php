<?php
session_start();
require_once "connection.php"; // Ensure this file connects to your database

// Check if a session is active and the user is logged in
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

$UserID = $_SESSION['UserID'];

// Query to fetch storeman details
$sql = "SELECT Name1 FROM Registration WHERE RegID = ? AND accStatus = 'Approved' AND RegType = 'Storeman'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "success", "Name1" => $row['Name1']]);
} else {
    echo json_encode(["status" => "error", "message" => "Unauthorized access or storeman not found"]);
}

$stmt->close();
$conn->close();
?>
