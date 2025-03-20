<?php
session_start();
require_once 'connection.php';

// Validate session and account status
if (!isset($_SESSION['UserID']) || $_SESSION['RegType'] !== 'Customer' || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$regID = $_SESSION['UserID'];

// Fetch customer details
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

// Get POST data
$serviceID = $_POST['ServiceID'];
$amount = $_POST['Amount'];
$phone = $_POST['PhoneNumber']; // Ensure phone is properly formatted for Safaricom
$accountRef = "BOOKING_" . time(); // Unique reference
$transactionDesc = "Payment for booking";
$date = date('Y-m-d');

// Format phone number (Safaricom expects 2547xxxxxxxx)
if (substr($phone, 0, 1) === "0") {
    $phone = "254" . substr($phone, 1);
} elseif (substr($phone, 0, 3) !== "254") {
    echo json_encode(["status" => "error", "message" => "Invalid phone number format"]);
    exit;
}

// M-Pesa API Credentials
$consumerKey = "YOUR_CONSUMER_KEY";
$consumerSecret = "YOUR_CONSUMER_SECRET";
$shortCode = "YOUR_SHORTCODE"; 
$passKey = "YOUR_PASSKEY";
$callbackURL = "https://yourdomain.com/mpesaCallback.php";

// Step 1: Get Access Token
$token_url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$credentials = base64_encode("$consumerKey:$consumerSecret");

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['access_token'])) {
    echo json_encode(["status" => "error", "message" => "Failed to get M-Pesa access token"]);
    exit;
}

$access_token = $response['access_token'];

// Step 2: Initiate STK Push
$timestamp = date("YmdHis");
$password = base64_encode($shortCode . $passKey . $timestamp);

$stk_url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
$payload = [
    "BusinessShortCode" => $shortCode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortCode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackURL,
    "AccountReference" => $accountRef,
    "TransactionDesc" => $transactionDesc
];

$ch = curl_init($stk_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

// Check if STK Push was successful
if (!isset($response["ResponseCode"]) || $response["ResponseCode"] !== "0") {
    echo json_encode(["status" => "error", "message" => "Failed to initiate STK Push", "details" => $response]);
    exit;
}

// STK Push Sent Successfully - Await callback confirmation
$checkoutRequestID = $response["CheckoutRequestID"];

echo json_encode(["status" => "pending", "message" => "STK Push sent successfully", "CheckoutRequestID" => $checkoutRequestID]);

?>
