<?php
session_start();
header("Content-Type: application/json");
require_once "connection.php"; 

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["Email"], $data["Password"])) {
        $response["message"] = "Missing email or password";
        echo json_encode($response);
        exit;
    }

    $email = trim($data["Email"]);
    $password = trim($data["Password"]);

    $stmt = $conn->prepare("SELECT id, Name1, Name2, Password, RegType FROM registration WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $name1, $name2, $hashedPassword, $role);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION["user_id"] = $userId;
            $_SESSION["email"] = $email;
            $_SESSION["name"] = $name1 . " " . $name2;
            $_SESSION["role"] = $role; // Store role

            $response["success"] = true;
            $response["message"] = "Login successful";
            $response["user"] = [
                "id" => $userId,
                "name" => $name1 . " " . $name2,
                "email" => $email,
                "role" => $role
            ];
        } else {
            $response["message"] = "Invalid email or password";
        }
    } else {
        $response["message"] = "Invalid email or password";
    }

    $stmt->close();
} else {
    $response["message"] = "Invalid request method";
}

echo json_encode($response);
?>
