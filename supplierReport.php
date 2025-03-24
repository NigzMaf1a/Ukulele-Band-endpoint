<?php
session_start();
require 'connection.php';

// Ensure session is set and accStatus is 'Approved'
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check for search query parameter
$search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';

$sql = "SELECT Description, Brand, SupplyDate FROM Supply 
        WHERE SupplyStatus = 'Delivered' 
        AND (Description LIKE ? OR Brand LIKE ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$supplies = [];
while ($row = $result->fetch_assoc()) {
    $supplies[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($supplies, JSON_PRETTY_PRINT);
