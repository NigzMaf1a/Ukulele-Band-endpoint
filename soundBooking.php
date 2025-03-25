<?php
session_start();
require_once 'connection.php'; // Ensure this connects to your database

// Check if the user is logged in and has the correct permissions
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

// Query to fetch lending records
$sql = "SELECT Genre, LendingType, Cost, Hours FROM Lending WHERE Approved = 'Yes' AND LendingStatus = 'Yet'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);

$stmt->close();
$conn->close();
?>
