<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['UserID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$regID = $_SESSION['UserID'];

// Verify user is an approved customer
$sql = "SELECT RegID FROM Registration WHERE RegID = ? AND accStatus = 'Approved' AND RegType = 'Customer'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit();
}

// Get CustomerID associated with RegID
$sql = "SELECT CustomerID FROM Customer WHERE RegID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regID);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
if (!$customer) {
    echo json_encode(["status" => "error", "message" => "Customer not found"]);
    exit();
}
$customerID = $customer['CustomerID'];

// Count pending Lending or Booking
$sql = "SELECT COUNT(*) AS pendingServices FROM Services 
        LEFT JOIN Lending ON Services.ServiceID = Lending.ServiceID 
        LEFT JOIN Booking ON Services.ServiceID = Booking.ServiceID 
        WHERE Services.CustomerID = ? AND ((Lending.Approved = 'Yes' AND Lending.LendingStatus = 'Yet') 
        OR (Booking.BookStatus = 'Untick'))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$pendingServices = $stmt->get_result()->fetch_assoc()['pendingServices'];

// Count feedback with a response
$sql = "SELECT COUNT(*) AS feedbackCount FROM Feedback WHERE CustomerID = ? AND Response IS NOT NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$feedbackCount = $stmt->get_result()->fetch_assoc()['feedbackCount'];

// Count unread messages
$sql = "SELECT COUNT(*) AS newMessages FROM Comms WHERE RecipientID = ? AND MessageResponse IS NOT NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regID);
$stmt->execute();
$newMessages = $stmt->get_result()->fetch_assoc()['newMessages'];

// Count new penalties (assuming Penalty table exists)
$sql = "SELECT COUNT(*) AS newPenalties FROM Penalty WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$newPenalties = $stmt->get_result()->fetch_assoc()['newPenalties'];

// Count latest receipts
$sql = "SELECT COUNT(*) AS recentReceipts FROM Receipt WHERE RegID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regID);
$stmt->execute();
$recentReceipts = $stmt->get_result()->fetch_assoc()['recentReceipts'];

// Return data as JSON
$response = [
    "status" => "success",
    "pendingServices" => $pendingServices,
    "feedbackCount" => $feedbackCount,
    "newMessages" => $newMessages,
    "newPenalties" => $newPenalties,
    "recentReceipts" => $recentReceipts
];

echo json_encode($response);
?>
