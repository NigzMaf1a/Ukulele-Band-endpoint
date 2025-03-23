<?php
session_start();
require_once 'connection.php'; // Ensures database connection

// Check session and permissions
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Storeman') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$response = [];

// Query for the latest order’s brand (Yamaha, Pioneer, Serato, Rekordbox)
$query1 = "SELECT Brand FROM Orders WHERE Brand IN ('Yamaha', 'Pioneer', 'Serato', 'Rekordbox') ORDER BY SupplyDate DESC LIMIT 1";
$result1 = $conn->query($query1);
$response['latest_order_brand'] = ($result1 && $result1->num_rows > 0) ? $result1->fetch_assoc()['Brand'] : "No orders found";

// Count unavailable inventory
$query2 = "SELECT COUNT(*) AS total_unavailable FROM Inventory WHERE Availability = 'Unavailable'";
$result2 = $conn->query($query2);
$response['total_unavailable_inventory'] = ($result2) ? $result2->fetch_assoc()['total_unavailable'] : 0;

// Count delivered orders
$query3 = "SELECT COUNT(*) AS total_delivered FROM Orders WHERE OrderStatus = 'Delivered'";
$result3 = $conn->query($query3);
$response['total_delivered_orders'] = ($result3) ? $result3->fetch_assoc()['total_delivered'] : 0;

// Count available inventory
$query4 = "SELECT COUNT(*) AS total_available FROM Inventory WHERE Availability = 'Available'";
$result4 = $conn->query($query4);
$response['total_available_inventory'] = ($result4) ? $result4->fetch_assoc()['total_available'] : 0;

// Count inventory where condition is NOT CAT1
$query5 = "SELECT COUNT(*) AS not_CAT1 FROM Inventory WHERE Condition NOT IN ('CAT1')";
$result5 = $conn->query($query5);
$response['total_not_CAT1'] = ($result5) ? $result5->fetch_assoc()['not_CAT1'] : 0;

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
