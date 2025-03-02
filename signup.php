<?php
header("Content-Type: application/json");
require_once "connection.php"; // Use the database connection

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["Name1"], $data["Name2"], $data["PhoneNo"], $data["Email"], $data["Password"], $data["Gender"], $data["RegType"], $data["dLocation"])) {
        $response["message"] = "Missing required fields";
        echo json_encode($response);
        exit;
    }

    // Sanitize & Assign Variables
    $name1 = trim($data["Name1"]);
    $name2 = trim($data["Name2"]);
    $phoneNo = trim($data["PhoneNo"]);
    $email = trim($data["Email"]);
    $password = password_hash(trim($data["Password"]), PASSWORD_BCRYPT); // Secure password
    $gender = trim($data["Gender"]);
    $regType = trim($data["RegType"]);
    $dLocation = trim($data["dLocation"]);
    $accStatus = "Pending"; // Default status

    // Validate ENUM values
    $validGenders = ["Male", "Female"];
    $validRegTypes = ["Customer", "DJ", "Mcee", "Storeman", "Accountant", "Dispatchman", "Inspector", "Band", "Admin", "Supplier"];
    $validLocations = ["Nairobi CBD", "Westlands", "Karen", "Langata", "Kilimani", "Eastleigh", "Umoja", "Parklands", "Ruiru", "Ruai", "Gikambura", "Kitengela", "Nairobi West", "Nairobi East"];

    if (!in_array($gender, $validGenders) || !in_array($regType, $validRegTypes) || !in_array($dLocation, $validLocations)) {
        $response["message"] = "Invalid Gender, RegType, or dLocation value";
        echo json_encode($response);
        exit;
    }

    // Check if email or phone already exists
    $stmt = $conn->prepare("SELECT id FROM registration WHERE Email = ? OR PhoneNo = ?");
    $stmt->bind_param("ss", $email, $phoneNo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response["message"] = "Email or Phone number already exists";
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO registration (Name1, Name2, PhoneNo, Email, Password, Gender, RegType, dLocation, accStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name1, $name2, $phoneNo, $email, $password, $gender, $regType, $dLocation, $accStatus);

    if ($stmt->execute()) {
        $response["success"] = true;
        $response["message"] = "Registration successful! Awaiting approval.";
    } else {
        $response["message"] = "Database error: " . $stmt->error;
    }

    $stmt->close();
} else {
    $response["message"] = "Invalid request method";
}

echo json_encode($response);
?>
