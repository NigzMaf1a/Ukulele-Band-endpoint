<?php
session_start();
require_once 'connection.php'; // Ensure this connects to your database

// Check if session is active and accStatus is 'Approved'
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit();
}

$UserID = $_SESSION['UserID'];
$query = "SELECT accStatus FROM Registration WHERE RegID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || $row['accStatus'] !== 'Approved') {
    echo json_encode(["success" => false, "message" => "Access denied."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch existing feedback
    $query = "SELECT CONCAT(r.Name1, ' ', r.Name2) AS Name, f.Comments, f.Response 
              FROM Feedback f
              JOIN Customer c ON f.CustomerID = c.CustomerID
              JOIN Registration r ON c.RegID = r.RegID";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(["success" => true, "feedback" => $feedback]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new feedback
    $data = json_decode(file_get_contents("php://input"), true);
    $comments = trim($data['Comments'] ?? '');

    if (empty($comments)) {
        echo json_encode(["success" => false, "message" => "Comments cannot be empty."]);
        exit();
    }

    // Fetch CustomerID
    $query = "SELECT CustomerID, CONCAT(Name1, ' ', Name2) AS Name FROM Customer JOIN Registration USING (RegID) WHERE RegID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    
    if (!$customer) {
        echo json_encode(["success" => false, "message" => "Customer record not found."]);
        exit();
    }

    $CustomerID = $customer['CustomerID'];
    $Name = $customer['Name'];

    $query = "INSERT INTO Feedback (CustomerID, Name, Comments, Response, Rating) VALUES (?, ?, ?, '', NULL)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $CustomerID, $Name, $comments);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Feedback submitted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error submitting feedback."]);
    }
}
?>
