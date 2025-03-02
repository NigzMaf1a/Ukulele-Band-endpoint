<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Ensure database connection

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize input data
    $equipmentID = $_POST["EquipmentID"] ?? null;
    $price = $_POST["Price"] ?? null;
    $supplierName = $_POST["SupplierName"] ?? null;
    $description = $_POST["Description"] ?? null;
    $brand = $_POST["Brand"] ?? null;
    $supplyDate = $_POST["SupplyDate"] ?? null;
    $phoneNo = $_POST["PhoneNo"] ?? null;
    $supplyStatus = $_POST["SupplyStatus"] ?? "Undelivered"; // Default to 'Undelivered'

    if ($equipmentID && $price && $supplierName && $description && $brand && $supplyDate && $phoneNo) {
        // Prepare SQL query
        $insertSQL = "INSERT INTO Supply (EquipmentID, Price, SupplierName, Description, Brand, SupplyDate, PhoneNo, SupplyStatus) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insertSQL);
        $stmt->bind_param("iissssss", $equipmentID, $price, $supplierName, $description, $brand, $supplyDate, $phoneNo, $supplyStatus);

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Supply record added successfully.";
        } else {
            $response["message"] = "Failed to add supply record.";
        }
        $stmt->close();
    } else {
        $response["message"] = "Missing required fields.";
    }
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
?>
