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

// Get CustomerID from session
$UserID = $_SESSION["UserID"];

// Validate input
data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["EquipmentID"], $data["LendingDate"], $data["Cost"], $data["Hours"], $data["PhoneNo"])) {
    $response["message"] = "Missing required fields.";
    echo json_encode($response);
    exit;
}

// Extract values
$EquipmentID = $data["EquipmentID"];
$LendingDate = $data["LendingDate"];
$Cost = $data["Cost"];
$Hours = $data["Hours"];
$PhoneNo = $data["PhoneNo"];
$ServiceType = "Lending"; // Fixed service type
$CustomerPaymentStatus = "Not Paid";

// Insert into Services table
$sqlService = "INSERT INTO Services (CustomerID, PhoneNo, Cost, Hours, ServiceType) 
               VALUES (?, ?, ?, ?, ?)";
$stmtService = $conn->prepare($sqlService);
$stmtService->bind_param("issis", $UserID, $PhoneNo, $Cost, $Hours, $ServiceType);

if (!$stmtService->execute()) {
    $response["message"] = "Error inserting into Services table: " . $stmtService->error;
    echo json_encode($response);
    exit;
}

// Get the last inserted ServiceID
$ServiceID = $conn->insert_id;

// Insert into Lending table
$sqlLending = "INSERT INTO Lending (EquipmentID, LendingDate, LendingType, Cost, Hours, ServiceID, Approved, LendingStatus) 
               VALUES (?, ?, 'Mini', ?, ?, ?, 'No', 'Yet')";
$stmtLending = $conn->prepare($sqlLending);
$stmtLending->bind_param("isiii", $EquipmentID, $LendingDate, $Cost, $Hours, $ServiceID);

if (!$stmtLending->execute()) {
    $response["message"] = "Error inserting into Lending table: " . $stmtLending->error;
    echo json_encode($response);
    exit;
}

// Insert into CustomerPayment table
$sqlPayment = "INSERT INTO CustomerPayment (ServiceID, RegID, CustomerID, TransactionID, CustomerPaymentName, ServiceType, Amount, CustomerPaymentStatus) 
               VALUES (?, ?, ?, NULL, ?, ?, ?, ?)";
$stmtPayment = $conn->prepare($sqlPayment);
$stmtPayment->bind_param("iiissi", $ServiceID, $_SESSION["RegID"], $UserID, $PhoneNo, $ServiceType, $Cost, $CustomerPaymentStatus);

if ($stmtPayment->execute()) {
    $response["success"] = true;
    $response["message"] = "Lending process completed successfully.";
} else {
    $response["message"] = "Error inserting into CustomerPayment table: " . $stmtPayment->error;
}

// Return JSON response
echo json_encode($response);
?>
