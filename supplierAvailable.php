<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Ensure database connection

$response = ["success" => false, "data" => [], "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // SQL query to retrieve required fields
    $query = "SELECT Price, SupplierName, Description, Brand FROM Supply";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response["data"][] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Supply records retrieved successfully.";
    } else {
        $response["message"] = "No supply records found.";
    }
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
?>
