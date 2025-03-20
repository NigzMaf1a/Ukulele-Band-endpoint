<?php
session_start();
require_once 'connection.php';

// Ensure user is logged in
if (!isset($_SESSION['UserID']) || $_SESSION['RegType'] !== 'Customer' || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$customerID = $_SESSION['UserID'];
$regID = $_SESSION['RegID'];
$name1 = $_SESSION['Name1'];
$name2 = $_SESSION['Name2'];

try {
    $conn->begin_transaction();
    
    // Fetch latest payment entry for this customer
    $stmt = $conn->prepare("SELECT ServiceID, TransactionID, ServiceType, Amount FROM CustomerPayment WHERE CustomerID = ? ORDER BY CustomerPaymentID DESC LIMIT 1");
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "No payment record found"]);
        exit;
    }
    
    $paymentData = $result->fetch_assoc();
    $serviceID = $paymentData['ServiceID'];
    $transactionID = $paymentData['TransactionID'];
    $serviceType = $paymentData['ServiceType'];
    $amount = $paymentData['Amount'];
    $receiptDate = date('Y-m-d');
    
    // Insert into Receipt table
    $stmt = $conn->prepare("INSERT INTO Receipt (CustomerID, RegID, ServiceID, Name1, Name2, ServiceType, Amount, TransactionID, ReceiptDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssiis", $customerID, $regID, $serviceID, $name1, $name2, $serviceType, $amount, $transactionID, $receiptDate);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Receipt generated successfully"]);
    } else {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Failed to generate receipt"]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
