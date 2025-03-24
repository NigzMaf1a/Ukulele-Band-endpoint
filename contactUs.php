<?php
session_start();
require_once 'connection.php';

// Check if a session is started and accStatus is 'Approved'
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// Fetch the latest record from the Contact table
$sql = "SELECT PhoneNo, EmailAddress, Instagram, Facebook, POBox FROM Contact ORDER BY ROWID DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $contact = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $contact]);
} else {
    echo json_encode(["status" => "error", "message" => "No contact information found."]);
}

$stmt->close();
$conn->close();
?>
