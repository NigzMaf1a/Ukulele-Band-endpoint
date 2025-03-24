<?php
session_start();
require 'connection.php';

header('Content-Type: application/json');
$response = [];

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

$UserID = $_SESSION['UserID'];
$targetDir = "uploads/";

// Ensure the directory exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Check if file is uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['image']['name']);
    $fileTmpName = $_FILES['image']['tmp_name'];
    $fileSize = $_FILES['image']['size'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(["error" => "Invalid file type. Only JPG, JPEG, PNG & GIF are allowed."]);
        exit;
    }

    if ($fileSize > 2 * 1024 * 1024) { // Limit file size to 2MB
        echo json_encode(["error" => "File size exceeds 2MB."]);
        exit;
    }

    $newFileName = "profile_" . $UserID . "_" . time() . ".$fileType";
    $targetFilePath = $targetDir . $newFileName;

    if (move_uploaded_file($fileTmpName, $targetFilePath)) {
        $updateQuery = "UPDATE Registration SET PhotoPath = ? WHERE RegID = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $targetFilePath, $UserID);
        
        if ($stmt->execute()) {
            $response = ["success" => "Profile picture updated successfully.", "PhotoPath" => $targetFilePath];
        } else {
            $response = ["error" => "Database update failed."];
        }
        $stmt->close();
    } else {
        $response = ["error" => "Failed to upload image."];
    }
} else {
    $response = ["error" => "No file uploaded or an error occurred."];
}

$conn->close();
echo json_encode($response);
?>
