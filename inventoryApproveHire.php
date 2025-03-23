<?php
session_start();
require_once 'connection.php'; // Ensures DB connection

// Check if user is logged in
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Storeman') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

// Handle GET request - Fetch records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT LendID, Genre, LendingType, Cost, Hours 
              FROM Lending 
              WHERE Approved = 'No' AND LendingStatus = 'Yet'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(["status" => "success", "data" => $data]);
    exit;
}

// Handle POST request - Update Approved & LendingStatus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure required fields are provided
    if (!isset($_POST['LendID'], $_POST['Approved'], $_POST['LendingStatus'])) {
        echo json_encode(["status" => "error", "message" => "Missing parameters"]);
        exit;
    }

    $LendID = intval($_POST['LendID']);
    $Approved = ($_POST['Approved'] === 'Yes') ? 'Yes' : 'No';
    $LendingStatus = ($_POST['LendingStatus'] === 'Done') ? 'Done' : 'Yet';

    // Update query
    $updateQuery = "UPDATE Lending SET Approved = ?, LendingStatus = ? WHERE LendID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $Approved, $LendingStatus, $LendID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Lending record updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit;
}

// Invalid request method
echo json_encode(["status" => "error", "message" => "Invalid request"]);
?>
