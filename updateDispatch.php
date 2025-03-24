<?php
session_start();
require_once 'connection.php';

// Ensure session is active and user is approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerID = $_POST['CustomerID'] ?? '';

    if (empty($customerID)) {
        echo json_encode(["status" => "error", "message" => "CustomerID is required"]);
        exit();
    }

    // Update the Dispatched status to 'Yes'
    $stmt = $conn->prepare("UPDATE Dispatch SET Dispatched = 'Yes' WHERE CustomerID = ?");
    $stmt->bind_param("i", $customerID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Dispatch status updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update dispatch status"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>
