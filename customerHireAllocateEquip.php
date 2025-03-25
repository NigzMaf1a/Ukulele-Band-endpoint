<?php
session_start();
require 'connection.php';

// Ensure the user is logged in, approved, and a customer
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Customer') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

// Check if OnOfferName is provided
if (!isset($_POST['OnOfferName'])) {
    echo json_encode(["status" => "error", "message" => "Missing OnOfferName"]);
    exit;
}

$onOfferName = $_POST['OnOfferName'];

// Define required equipment based on OnOfferName
$equipmentRequirements = [
    "Sound System Mini" => [
        'Speaker' => 5, 'Microphone' => 1, 'Mixer' => 1, 'CDJ' => 2, 'Cable' => 5, 'Wireless' => 1
    ],
    "Sound System Midi" => [
        'Speaker' => 8, 'Microphone' => 2, 'Mixer' => 1, 'CDJ' => 2, 'Cable' => 8, 'Wireless' => 2
    ],
    "Sound System Maxi" => [
        'Speaker' => 12, 'Microphone' => 3, 'Mixer' => 1, 'CDJ' => 2, 'Cable' => 12, 'Wireless' => 3
    ]
];

if (!isset($equipmentRequirements[$onOfferName])) {
    echo json_encode(["status" => "error", "message" => "Invalid OnOfferName"]);
    exit;
}

$requirements = $equipmentRequirements[$onOfferName];
$success = true;
$conn->begin_transaction();

foreach ($requirements as $description => $quantity) {
    // Fetch available equipment matching the description
    $sql = "SELECT EquipmentID FROM Inventory WHERE Description = ? AND Availability = 'Available' LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $description, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < $quantity) {
        $success = false;
        break;
    }

    // Update selected equipment to Unavailable
    while ($row = $result->fetch_assoc()) {
        $updateSql = "UPDATE Inventory SET Availability = 'Unavailable' WHERE EquipmentID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $row['EquipmentID']);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();
}

if ($success) {
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Equipment allocated successfully"]);
} else {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Not enough available equipment"]);
}

$conn->close();
?>
