<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Ensure database connection

$response = ["success" => false, "message" => ""];

// Check if the user is logged in
if (!isset($_SESSION["UserID"]) || !isset($_SESSION["RegType"])) {
    $response["message"] = "Unauthorized access.";
    echo json_encode($response);
    exit;
}

// Restrict access to Customers only
if ($_SESSION["RegType"] !== "Customer") {
    $response["message"] = "Access denied. Customers only.";
    echo json_encode($response);
    exit;
}

$UserID = $_SESSION["UserID"];

// Query to get the latest record for the customer
$sql = "SELECT Genre, Cost, Hours, ServiceType 
        FROM Services
        WHERE CustomerID = ?
        ORDER BY ServiceID DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $response["success"] = true;
    $response["receipt"] = $result->fetch_assoc();
} else {
    $response["message"] = "No service records found.";
}

echo json_encode($response);
?>
