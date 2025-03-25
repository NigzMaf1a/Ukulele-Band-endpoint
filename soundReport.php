<?php
session_start();
require 'connection.php';

// Ensure session is active and user is approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

header('Content-Type: application/json');

$sql = "SELECT Genre, LendingType, Cost, Hours 
        FROM Lending 
        WHERE Approved = 'Yes' AND LendingStatus = 'Done'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$lendingData = [];
while ($row = $result->fetch_assoc()) {
    $lendingData[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $lendingData]);
?>
