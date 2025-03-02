<?php
session_start();

function checkAccess($allowedRoles) {
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["success" => false, "message" => "Unauthorized access"]);
        exit;
    }

    $userRole = $_SESSION["role"];

    if (!in_array($userRole, $allowedRoles)) {
        echo json_encode(["success" => false, "message" => "Permission denied"]);
        exit;
    }
}
?>
