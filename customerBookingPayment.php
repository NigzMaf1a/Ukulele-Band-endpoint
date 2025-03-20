<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$regID = $_SESSION['UserID'];

// Validate customer and account status
$sql = "SELECT R.RegID, R.Name1, R.Name2, C.CustomerID 
        FROM Registration R 
        JOIN Customer C ON R.RegID = C.RegID 
        WHERE R.RegID = ? AND R.accStatus = 'Approved' AND R.RegType = 'Customer'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or inactive account.']);
    exit;
}

$customer = $result->fetch_assoc();
$customerID = $customer['CustomerID'];
$name1 = $customer['Name1'];
$name2 = $customer['Name2'];
$name = $name1 . " " . $name2;

// Get service details from customerBooking.php (assume passed via POST request)
$serviceID = $_POST['ServiceID'];
$amount = $_POST['Amount'];
$date = date('Y-m-d');

// Process payment with c2b.php (Simulating successful transaction)
$transactionSuccess = true; // Change this based on actual c2b.php response

if (!$transactionSuccess) {
    echo json_encode(['status' => 'error', 'message' => 'Payment failed.']);
    exit;
}

// Insert transaction into Finance table
//TransactionID auto increments
$sql = "INSERT INTO Finance (RegID, Name, TransactionID,TransactionDate, Amount, TransactType) VALUES (?, ?, ?, ?, ?, 'Deposit')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issi", $regID, $name, $date, $amount);
$stmt->execute();
$transactionID = $stmt->insert_id;

// Generate unique CustomerPaymentName (CP001...)
$sql = "SELECT COUNT(*) AS count FROM CustomerPayment";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$customerPaymentName = "CP" . str_pad($row['count'] + 1, 3, '0', STR_PAD_LEFT);

// Insert into CustomerPayment table
$sql = "INSERT INTO CustomerPayment (ServiceID, RegID, CustomerID, TransactionID, Name1, Name2, CustomerPaymentName, ServiceType, Amount, CustomerPaymentStatus) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Booking', ?, 'Paid')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiissssi", $serviceID, $regID, $customerID, $transactionID, $name1, $name2, $customerPaymentName, $amount);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Payment recorded successfully.']);
?>
