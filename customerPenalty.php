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

// Query to get all penalty records for the customer
$sql = "SELECT EquipmentID, Description, Condition, Penalty 
        FROM Penalty
        WHERE CustomerID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $penalties = [];
    while ($row = $result->fetch_assoc()) {
        $penalties[] = $row;
    }
    $response["success"] = true;
    $response["penalties"] = $penalties;
} else {
    $response["message"] = "No penalty records found.";
}

echo json_encode($response);
?>
