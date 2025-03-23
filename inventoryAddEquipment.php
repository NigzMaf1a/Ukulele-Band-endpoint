<?php
session_start();
require 'connection.php'; // Ensure this file connects to the database

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized: No active session."]);
    exit();
}

$userID = $_SESSION['UserID'];

// Fetch user details from the database
$sql = "SELECT Name1, accStatus, RegType FROM Registration WHERE RegID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit();
}

$user = $result->fetch_assoc();

// Check account status and role
if ($user['accStatus'] !== 'Approved' || $user['RegType'] !== 'Storeman') {
    echo json_encode(["status" => "error", "message" => "Unauthorized: Only approved storemen can add equipment."]);
    exit();
}

// Get data from request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['EquipmentName'], $data['Description'], $data['Brand'], $data['Condition'], $data['Price'])) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit();
}

$equipmentName = $data['EquipmentName'];
$description = $data['Description'];
$brand = $data['Brand'];
$condition = $data['Condition'];
$price = (int) $data['Price'];

// Validate ENUM values
$validDescriptions = ['Speaker', 'Microphone', 'Mixer', 'CDJ', 'Cable', 'Wireless'];
$validBrands = ['Yamaha', 'Pioneer', 'Serato', 'Rekordbox', 'Sennheiser', 'Xplod'];
$validConditions = ['CAT1', 'CAT2', 'CAT3', 'CAT4'];

if (!in_array($description, $validDescriptions) || !in_array($brand, $validBrands) || !in_array($condition, $validConditions)) {
    echo json_encode(["status" => "error", "message" => "Invalid description, brand, or condition value."]);
    exit();
}

// Insert into Inventory table
$sql = "INSERT INTO Inventory (EquipmentName, Description, Brand, Condition, Price, PurchaseDate, Availability) VALUES (?, ?, ?, ?, ?, NOW(), 'Available')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $equipmentName, $description, $brand, $condition, $price);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Equipment added successfully", "storeman" => $user['Name1']]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
