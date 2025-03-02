<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; // Use the database connection

$response = ["success" => false, "message" => ""];

// Handle POST request (Adding feedback)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["CustomerID"]) || !isset($_SESSION["Name"])) {
        $response["message"] = "Unauthorized: Please log in.";
        echo json_encode($response);
        exit;
    }

    $customerID = $_SESSION["CustomerID"];
    $name = $_SESSION["Name"];
    $comments = isset($_POST["comments"]) ? trim($_POST["comments"]) : "";
    $rating = isset($_POST["rating"]) ? (int)$_POST["rating"] : 0;

    // Validate inputs
    if (empty($comments) || $rating < 1 || $rating > 5) {
        $response["message"] = "Invalid input. Ensure comments are not empty and rating is between 1 and 5.";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Feedback (CustomerID, Name, Comments, Rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $customerID, $name, $comments, $rating);

    if ($stmt->execute()) {
        $response["success"] = true;
        $response["message"] = "Feedback submitted successfully!";
    } else {
        $response["message"] = "Failed to submit feedback.";
    }

    $stmt->close();
    echo json_encode($response);
    exit;
}

// Handle GET request (Fetching all feedback)
$sql = "SELECT Name, Comments, Response, Rating FROM Feedback ORDER BY FeedbackID DESC";
$result = $conn->query($sql);

$feedbackList = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbackList[] = [
            "Name" => $row["Name"],
            "Comments" => $row["Comments"],
            "Response" => $row["Response"] ?? "No response yet",
            "Rating" => $row["Rating"]
        ];
    }
    $response["success"] = true;
    $response["feedback"] = $feedbackList;
} else {
    $response["message"] = "No feedback available.";
}

echo json_encode($response);
?>
