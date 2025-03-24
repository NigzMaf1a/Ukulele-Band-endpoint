<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in and approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commsID = $_POST['commsID'] ?? null;
    $messageResponse = $_POST['messageResponse'] ?? null;

    if (!$commsID || !$messageResponse) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    // Prepare the SQL statement to update the response
    $sql = "UPDATE Comms SET MessageResponse = ? WHERE CommsID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $messageResponse, $commsID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Response updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update response"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
