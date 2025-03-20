<?php
session_start();
require_once 'connection.php';

// Check if session exists and validate user role
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Accountant') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

$sql = "SELECT CustomerPaymentID, Name1, Name2, Amount FROM CustomerPayment WHERE CustomerPaymentStatus = 'Paid'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode($payments);
$stmt->close();
$conn->close();
?>
