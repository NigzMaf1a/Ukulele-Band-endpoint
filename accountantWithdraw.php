<?php
session_start();
require 'connection.php'; // Database connection

// Check if session is started and user is an approved accountant
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Account') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$RegID = $_SESSION['UserID'];
$Name = $_SESSION['Name1'] . " " . $_SESSION['Name2'];
$Amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
$TransactionDate = date("Y-m-d");

if ($Amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid amount"]);
    exit();
}

// Insert into Finance table
$financeQuery = "INSERT INTO Finance (RegID, Name, TransactionDate, Amount, TransactType) VALUES (?, ?, ?, ?, 'Withdraw')";
$stmt = $conn->prepare($financeQuery);
$stmt->bind_param("issi", $RegID, $Name, $TransactionDate, $Amount);
if ($stmt->execute()) {
    $TransactionID = $stmt->insert_id;

    // Insert into Withdraw table
    $withdrawQuery = "INSERT INTO Withdraw (TransactionID, RegID, Amount, TransactionDate) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($withdrawQuery);
    $stmt->bind_param("iiis", $TransactionID, $RegID, $Amount, $TransactionDate);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Transaction recorded successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to record withdrawal"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to record transaction"]);
}

$stmt->close();
$conn->close();
?>
