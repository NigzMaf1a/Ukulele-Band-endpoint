<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Ensure database connection

$response = ["success" => false, "data" => [], "message" => ""];

// Fetch records where Dispatched is 'No'
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $sql = "SELECT CustomerID, Name, Location, EquipmentID, PhoneNo FROM Dispatch WHERE Dispatched = 'No'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response["data"][] = $row;
        }
        $response["success"] = true;
    } else {
        $response["message"] = "No pending dispatch records found.";
    }
}

// Approve dispatch by updating Dispatched to 'Yes'
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $dispatchID = $_POST["DispatchID"] ?? null;

    if ($dispatchID) {
        $updateSQL = "UPDATE Dispatch SET Dispatched = 'Yes' WHERE DispatchID = ?";
        $stmt = $conn->prepare($updateSQL);
        $stmt->bind_param("i", $dispatchID);

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Dispatch approved successfully.";
        } else {
            $response["message"] = "Failed to update dispatch status.";
        }
        $stmt->close();
    } else {
        $response["message"] = "Invalid DispatchID.";
    }
}

echo json_encode($response);
?>
