<?php
session_start();
require_once 'connection.php'; // Ensure this file establishes the database connection

// Check if a session is active and the user is a Supplier with an Approved account
if (!isset($_SESSION['UserID']) || $_SESSION['RegType'] !== 'Supplier' || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$supplierName = $_SESSION['Username']; // Assuming Username is the supplier's name

$response = [];

// Get total number of undelivered orders
$query1 = "SELECT COUNT(*) AS undelivered_count FROM Orders WHERE OrderStatus = 'Undelivered' AND SupplierName = ?";
$stmt1 = $conn->prepare($query1);
$stmt1->bind_param("s", $supplierName);
$stmt1->execute();
$result1 = $stmt1->get_result()->fetch_assoc();
$response['undelivered_count'] = $result1['undelivered_count'] ?? 0;
$stmt1->close();

// Get latest order details
$query2 = "SELECT Price, Description, Brand FROM Orders WHERE SupplierName = ? ORDER BY SupplyDate DESC LIMIT 1";
$stmt2 = $conn->prepare($query2);
$stmt2->bind_param("s", $supplierName);
$stmt2->execute();
$result2 = $stmt2->get_result()->fetch_assoc();
$response['latest_order_price'] = $result2['Price'] ?? 0;
$response['latest_order_description'] = $result2['Description'] ?? null;
$response['latest_order_brand'] = $result2['Brand'] ?? null;
$stmt2->close();

// Get total number of delivered orders
$query3 = "SELECT COUNT(*) AS delivered_count FROM Orders WHERE OrderStatus = 'Delivered' AND SupplierName = ?";
$stmt3 = $conn->prepare($query3);
$stmt3->bind_param("s", $supplierName);
$stmt3->execute();
$result3 = $stmt3->get_result()->fetch_assoc();
$response['delivered_count'] = $result3['delivered_count'] ?? 0;
$stmt3->close();

// Return the JSON response
echo json_encode($response);
?>
