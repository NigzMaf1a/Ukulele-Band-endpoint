<?php
header('Content-Type: application/json');
require 'connection.php'; // Ensure this connects to your DB
require 'login.php'; // Ensure session is started

if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Inspector') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$response = [];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['equipment_name'])) {
    // Fetch Data from Inventory
    $equipmentName = $_GET['equipment_name'];
    
    $stmt = $conn->prepare("SELECT EquipmentID, Price, EquipmentName, Description, Brand, Condition FROM Inventory WHERE EquipmentName = ?");
    $stmt->bind_param("s", $equipmentName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = $result->fetch_assoc();
    } else {
        $response["error"] = "No matching equipment found.";
    }
    $stmt->close();
}

if ($method === 'POST') {
    // Insert Data into Inspector and Update Inventory
    $data = json_decode(file_get_contents("php://input"), true);
    
    $equipmentID = $data['EquipmentID'];
    $equipmentName = $data['EquipmentName'];
    $inspectionDate = date('Y-m-d');
    $inspectorName = $_SESSION['Name1'] . ' ' . $_SESSION['Name2'];
    $condition = $data['Condition'];
    
    $conn->begin_transaction();
    try {
        // Insert into Inspector Table
        $stmt = $conn->prepare("INSERT INTO Inspector (EquipmentID, EquipmentName, InspectionDate, InspectorName, Condition) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $equipmentID, $equipmentName, $inspectionDate, $inspectorName, $condition);
        $stmt->execute();
        $stmt->close();

        // Update Inventory Condition
        $stmt = $conn->prepare("UPDATE Inventory SET Condition = ? WHERE EquipmentID = ?");
        $stmt->bind_param("si", $condition, $equipmentID);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $response["success"] = "Inspection recorded successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $response["error"] = "Error: " . $e->getMessage();
    }
}

$conn->close();
echo json_encode($response);

// File: inspectorInspection.php
