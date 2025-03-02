<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Ensure database connection

$response = ["success" => false, "data" => [], "message" => ""];

// Fetch records where Dispatched is 'Yes'
$sql = "SELECT CustomerID, Name, Location, EquipmentID, PhoneNo, Dispatched 
        FROM Dispatch WHERE Dispatched = 'Yes'";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response["data"][] = $row;
    }
    $response["success"] = true;
} else {
    $response["message"] = "No dispatched records found.";
}

// Return JSON response
echo json_encode($response);
?>
