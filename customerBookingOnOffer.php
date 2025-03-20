<?php
session_start();
require 'connection.php'; // Ensure this file correctly establishes your DB connection

header('Content-Type: application/json'); // Set JSON header

// Check if a session is active and user has the correct role
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Customer') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

// Ensure database connection is established
if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Prepare and execute SQL statement
$sql = "SELECT OnOfferName, OnOfferDescription, OnOfferCostPerHour FROM OnOffer WHERE ServiceType = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $serviceType = 'Booking';
    $stmt->bind_param("s", $serviceType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $data]);
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Database query failed"]);
}

$conn->close();
?>
