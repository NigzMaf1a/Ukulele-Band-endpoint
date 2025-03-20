<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Ensure database connection

$response = ["success" => false, "message" => "", "bookings" => []];

// Check if the user is logged in
if (!isset($_SESSION["UserID"])) {
    http_response_code(401); // Unauthorized
    $response["message"] = "Unauthorized access.";
    echo json_encode($response);
    exit;
}

// Get search parameter if provided
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";

// Base SQL query
$sql = "SELECT Genre, BookingDate, Cost, Hours FROM Booking WHERE BookStatus = ?";

// Modify query if searching
$params = ["Tick"];
$types = "s";

if (!empty($search)) {
    $sql .= " AND Genre LIKE ?";
    $params[] = "%" . $search . "%"; // Wildcard search for partial matches
    $types .= "s";
}

// Prepare and execute statement
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response["bookings"][] = $row;
    }
    $response["success"] = true;
} else {
    http_response_code(404); // Not Found
    $response["message"] = "No matching bookings found.";
}

// Close statement and database connection
$stmt->close();
$conn->close();

echo json_encode($response);
?>
