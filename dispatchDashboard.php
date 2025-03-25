<?php
session_start();
require 'connection.php'; // Ensure this file contains your DB connection setup

// Check if a session is active and the user is logged in
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$UserID = $_SESSION['UserID'];

// Validate that the logged-in user is an approved Dispatchman
$sql = "SELECT RegID FROM Registration WHERE RegID = ? AND accStatus = 'Approved' AND RegType = 'Dispatchman'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit();
}

// Query to get the latest dispatch details
$query = "
    SELECT 
        c.dLocation, 
        c.Name AS Name1, 
        c.PhoneNo, 
        d.DispatchDate
    FROM Dispatch d
    INNER JOIN Customer c ON d.CustomerID = c.CustomerID
    WHERE d.DispatchDate = (SELECT MAX(DispatchDate) FROM Dispatch)
    LIMIT 1
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$response = ["status" => "error", "message" => "No dispatch records found"];

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $response = [
        "status" => "success",
        "location" => $data['dLocation'],
        "name" => $data['Name1'],
        "phone" => $data['PhoneNo'],
        "dispatchDate" => $data['DispatchDate']
    ];
}

// Query to get the total number of dispatched entries
$totalQuery = "SELECT COUNT(*) AS totalDispatched FROM Dispatch WHERE Dispatched = 'Yes'";
$totalStmt = $conn->prepare($totalQuery);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();

if ($totalResult->num_rows > 0) {
    $totalData = $totalResult->fetch_assoc();
    $response["totalDispatched"] = $totalData['totalDispatched'];
} else {
    $response["totalDispatched"] = 0;
}

echo json_encode($response);

$stmt->close();
$totalStmt->close();
$conn->close();
?>
