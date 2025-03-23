<?php
session_start();
require_once '../connection.php'; // Ensure this path is correct

// Ensure user is logged in and has the correct permissions
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Storeman') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

try {
    // Prepare SQL statement to fetch required data
    $query = "SELECT SupplyName, Description, Brand 
              FROM Supply 
              WHERE EquipAvailable = 'Yes'";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch data
    $supplyData = [];
    while ($row = $result->fetch_assoc()) {
        $supplyData[] = $row;
    }

    // Return JSON response
    echo json_encode(["status" => "success", "data" => $supplyData]);

    // Close statement and connection
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error", "error" => $e->getMessage()]);
}
?>
