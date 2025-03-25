<?php
session_start();
require_once '../connection.php'; // Ensure the correct path to connection.php

// Check if the user is logged in and approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$dispatchmanID = $_SESSION['UserID'];
$response = [];

$query = "
    SELECT 
        r.Name1 AS Name, 
        c.PhoneNo, 
        d.Location
    FROM Dispatch d
    INNER JOIN Customer c ON d.CustomerID = c.CustomerID
    INNER JOIN Registration r ON c.RegID = r.RegID
    WHERE d.Dispatched = 'No'
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $response]);
} else {
    echo json_encode(["status" => "empty", "message" => "No pending dispatches"]);
}

$stmt->close();
$conn->close();
?>
