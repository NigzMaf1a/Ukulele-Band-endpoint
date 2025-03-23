<?php
session_start();
require_once 'connection.php'; // Ensure this file connects to your database

// Check if the session exists and the user is a Storeman with an Approved status
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Storeman') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

$sql = "SELECT o.Description, o.Brand, s.SupplierName 
        FROM Orders o
        JOIN Supply s ON o.SupplierName = s.SupplierName
        WHERE o.OrderStatus = 'Undelivered'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders);

$stmt->close();
$conn->close();
?>
