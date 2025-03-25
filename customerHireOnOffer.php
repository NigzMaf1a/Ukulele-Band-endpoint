<?php
session_start();
require 'connection.php';

// Ensure the user is logged in, approved, and a customer
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Customer') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$sql = "SELECT OnOfferName, OnOfferDescription, OnOfferCostPerHour FROM OnOffer WHERE ServiceType = 'Lending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $data]);
?>
