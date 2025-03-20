<?php
session_start();
require_once '../connection.php'; // Ensure this file properly connects to the database

// Check if session is active and user is logged in
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$UserID = $_SESSION['UserID'];

// Check if user is an approved band
$sql = "SELECT Regtype, accStatus FROM Customer WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

// If user does not exist
if ($result->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}

$user = $result->fetch_assoc();

// Ensure user is a Band and has an approved account
if ($user['Regtype'] !== 'Band') {
    echo json_encode(["status" => "error", "message" => "Access denied: Not a Band account"]);
    exit();
}

if ($user['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Access denied: Account not approved"]);
    exit();
}

// Handle BookStatus update (Triggered via UI button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['BookingID']) && isset($_POST['NewStatus'])) {
    $BookingID = $_POST['BookingID'];
    $NewStatus = $_POST['NewStatus'];

    // Validate NewStatus input
    if (!in_array($NewStatus, ['Tick', 'Untick'])) {
        echo json_encode(["status" => "error", "message" => "Invalid status"]);
        exit();
    }

    // Update BookStatus in the database
    $updateQuery = "UPDATE Booking SET BookStatus = ? WHERE BookingID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $NewStatus, $BookingID);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "BookStatus updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update"]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Fetch Bookings with 'Untick' status
$query = "SELECT Booking.BookingID, Booking.Genre, Booking.BookingDate, Booking.Hours, Booking.BookStatus
          FROM Booking 
          JOIN Services ON Booking.ServiceID = Services.ServiceID
          WHERE Booking.BookStatus = 'Untick'";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Return JSON response
echo json_encode(["status" => "success", "data" => $bookings]);

$stmt->close();
$conn->close();
?>
