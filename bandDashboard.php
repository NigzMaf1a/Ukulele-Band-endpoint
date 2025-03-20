<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php";

$response = ["success" => false, "message" => ""];

if (!isset($_SESSION["user_id"], $_SESSION["role"], $_SESSION["accStatus"])) {
    $response["message"] = "Unauthorized access. Please log in.";
    echo json_encode($response);
    exit;
}

if ($_SESSION["accStatus"] !== "Approved" || $_SESSION["role"] !== "Band") {
    $response["message"] = "Access denied. Only approved band members can access this dashboard.";
    echo json_encode($response);
    exit;
}

try {
    // Fetch the latest booking (Tick or Untick) and its customer location
    $query = "SELECT r.dLocation, b.Genre, b.Cost 
              FROM Booking b
              JOIN Services s ON b.ServiceID = s.ServiceID
              JOIN Registration r ON s.CustomerID = r.RegID
              WHERE b.BookStatus IN ('Tick', 'Untick')
              ORDER BY b.BookingDate DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->bind_result($location, $genre, $cost);
    $stmt->fetch();
    $stmt->close();
    
    // Fetch the latest communication where the recipient is a Band
    $query = "SELECT c.SenderName 
              FROM Comms c
              JOIN Registration r ON c.RecipientID = r.RegID
              WHERE r.RegType = 'Band'
              ORDER BY c.CommsDate DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->bind_result($senderName);
    $stmt->fetch();
    $stmt->close();
    
    // Fetch total number of bookings with BookStatus = 'Untick'
    $query = "SELECT COUNT(*) FROM Booking WHERE BookStatus = 'Untick'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stmt->bind_result($untickCount);
    $stmt->fetch();
    $stmt->close();
    
    $response["success"] = true;
    $response["message"] = "Dashboard data retrieved successfully";
    $response["location"] = $location;
    $response["genre"] = $genre;
    $response["cost"] = $cost;
    $response["latest_comms_sender"] = $senderName;
    $response["untick_bookings_count"] = $untickCount;
} catch (Exception $e) {
    $response["message"] = "Error retrieving dashboard data: " . $e->getMessage();
}

echo json_encode($response);
?>
