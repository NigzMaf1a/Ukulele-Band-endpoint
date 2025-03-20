<?php
session_start();
require 'connection.php'; // Database connection

// Check if session exists and user is logged in
if (!isset($_SESSION['UserID']) || $_SESSION['RegType'] !== 'Customer' || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$customerID = $_SESSION['UserID']; // Customer ID from session

// Fetch customer details
$sql = "SELECT PhoneNo FROM Customer WHERE CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Customer not found"]);
    exit;
}

$customer = $result->fetch_assoc();
$phoneNo = $customer['PhoneNo'];

// Get input data
$data = json_decode(file_get_contents("php://input"), true);
$genreSelection = $data['genreSelection'] ?? '';
$cost = $data['cost'] ?? 0;
$hours = $data['hours'] ?? 0;
$bookingDate = date('Y-m-d');

// Map genre selection to ENUM values
$genreMap = [
    "Live Band Reggae" => "Reggae",
    "Live Band Rhumba" => "Rhumba",
    "Live Band Zilizopendwa" => "Zilizopendwa",
    "Live Band Benga" => "Benga",
    "Live Band Soul" => "Soul",
    "Live Band RnB" => "RnB"
];

if (!isset($genreMap[$genreSelection])) {
    echo json_encode(["status" => "error", "message" => "Invalid genre selection"]);
    exit;
}

$genre = $genreMap[$genreSelection];

// Insert into Services table
$serviceType = "Booking";
$insertService = "INSERT INTO Services (CustomerID, PhoneNo, Cost, Hours, ServiceType) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertService);
$stmt->bind_param("isdis", $customerID, $phoneNo, $cost, $hours, $serviceType);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Failed to book service"]);
    exit;
}

$serviceID = $stmt->insert_id; // Get last inserted ID for Service

// Insert into Booking table
$bookStatus = "Untick";
$insertBooking = "INSERT INTO Booking (Genre, BookingDate, Cost, Hours, ServiceID, BookStatus) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertBooking);
$stmt->bind_param("ssdiis", $genre, $bookingDate, $cost, $hours, $serviceID, $bookStatus);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Booking successful"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to book"]);
}

$stmt->close();
$conn->close();
?>