<?php
session_start();
require 'connection.php'; // Ensures database connection is included
require 'login.php'; // Ensures session is checked

// Check if user is logged in and approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Fetch inventory records
$sql = "SELECT EquipmentName, Description, Brand, `Condition`, Availability FROM Inventory";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$inventory = [];
while ($row = $result->fetch_assoc()) {
    $inventory[] = $row;
}

// Return JSON response
echo json_encode($inventory);
$stmt->close();
$conn->close();
?>
