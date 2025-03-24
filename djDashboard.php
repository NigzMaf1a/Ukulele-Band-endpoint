<?php
session_start();
require_once "connection.php"; // Ensure this connects to your database

// Check if the session is set and user is a DJ with approved account
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized. Please log in."]);
    exit();
}

$UserID = $_SESSION['UserID'];

$sql = "SELECT RegType, accStatus FROM Registration WHERE RegID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['RegType'] !== 'DJ' || $user['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Access denied. Only approved DJs can access this."]);
    exit();
}

// Fetch latest lending details and total count of completed lendings
$query = "
    SELECT 
        c.Location,
        c.Name AS CustomerName,
        l.Genre,
        l.LendingDate,
        (SELECT COUNT(*) FROM Lending WHERE LendingStatus = 'Done') AS TotalCompletedLendings
    FROM Lending l
    JOIN Services s ON l.ServiceID = s.ServiceID
    JOIN Customer c ON s.CustomerID = c.CustomerID
    ORDER BY l.LendingDate DESC
    LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "status" => "success",
        "location" => $row['Location'],
        "customer_name" => $row['CustomerName'],
        "genre" => $row['Genre'],
        "lending_date" => $row['LendingDate'],
        "total_completed_lendings" => $row['TotalCompletedLendings']
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "No lending records found."]);
}

$stmt->close();
$conn->close();
?>
