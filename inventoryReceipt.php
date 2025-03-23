<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php";

$response = ["success" => false, "message" => "", "data" => null];

if (!isset($_SESSION['UserID'])) {
    $response["message"] = "Unauthorized access. Please log in.";
    echo json_encode($response);
    exit;
}

$sql = "SELECT Price, SupplierName, Description, Brand, SupplyDate FROM `Order` ORDER BY SupplyDate DESC LIMIT 1";

if ($stmt = $conn->prepare($sql)) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $response["success"] = true;
            $response["data"] = $row;
        } else {
            $response["message"] = "No records found.";
        }
    } else {
        $response["message"] = "Database error: " . $stmt->error;
    }
    $stmt->close();
} else {
    $response["message"] = "Database error: " . $conn->error;
}

$conn->close();
echo json_encode($response);
?>
