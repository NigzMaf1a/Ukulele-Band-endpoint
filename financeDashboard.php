<?php
session_start();
require 'connection.php'; // Ensure database connection is included

// Check if session is active and user is an approved accountant
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Accountant') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$response = [];

// Fetch latest Price from Orders
$query = "SELECT Price FROM Orders ORDER BY OrderID DESC LIMIT 1";
$result = $conn->query($query);
$response['latest_order_price'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['Price'] : null;

// Fetch latest Amount from CustomerPayment
$query = "SELECT Amount FROM CustomerPayment ORDER BY CustomerPaymentID DESC LIMIT 1";
$result = $conn->query($query);
$response['latest_customer_payment_amount'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['Amount'] : null;

// Fetch latest Amount where TransactType = 'Withdraw' from Finance
$query = "SELECT Amount FROM Finance WHERE TransactType = 'Withdraw' ORDER BY TransactionID DESC LIMIT 1";
$result = $conn->query($query);
$response['latest_withdraw_amount'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['Amount'] : null;

// Fetch latest TransactType from Finance
$query = "SELECT TransactType FROM Finance ORDER BY TransactionID DESC LIMIT 1";
$result = $conn->query($query);
$response['latest_transaction_type'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['TransactType'] : null;

// Fetch sum of all Amount in CustomerPayment
$query = "SELECT SUM(Amount) AS total_customer_payments FROM CustomerPayment";
$result = $conn->query($query);
$response['total_customer_payments'] = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total_customer_payments'] : 0;

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
