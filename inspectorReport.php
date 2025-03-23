<?php
session_start();
require_once 'connection.php';

// Check if session is started in login.php and user is approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

$searchQuery = isset($_GET['query']) ? '%' . $_GET['query'] . '%' : '%';

$sql = "SELECT 
            Inventory.EquipmentName, 
            Inventory.Description, 
            Inspector.Condition 
        FROM Inspector 
        JOIN Inventory ON Inspector.EquipmentID = Inventory.EquipmentID 
        WHERE Inventory.EquipmentName LIKE ? 
        OR Inventory.Description LIKE ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$inspections = [];
while ($row = $result->fetch_assoc()) {
    $inspections[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($inspections);
