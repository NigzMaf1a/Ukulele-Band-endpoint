<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Storeman') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$query = "INSERT INTO Orders (SupplyName, Description, Brand, OrderStatus) VALUES (?, ?, ?, 'Undelivered')";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $_POST['SupplyName'], $_POST['Description'], $_POST['Brand']);
$stmt->execute();

echo json_encode(["status" => "success"]);
$stmt->close();
$conn->close();
?>
