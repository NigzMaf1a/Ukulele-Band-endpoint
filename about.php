<?php
header("Content-Type: application/json");
require_once "connection.php"; // Use the database connection

$response = ["success" => false, "message" => ""];

$sql = "SELECT Detail FROM About ORDER BY id DESC LIMIT 1"; // Get the latest entry

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response["success"] = true;
    $response["Detail"] = $row["Detail"];
} else {
    $response["message"] = "No About Us info found.";
}

echo json_encode($response);
?>
