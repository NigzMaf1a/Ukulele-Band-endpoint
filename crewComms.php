<?php
session_start();
require 'connection.php'; // Ensures DB connection is available

// Check if session exists and user is approved
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$UserID = $_SESSION['UserID'];
$response = [];

// Fetch all users' Name1 and Name2 as ReceiverName for the spinner where RegType is Customer, DJ, Mcee, or Band Only
$receiverQuery = "SELECT RegID, CONCAT(Name1, ' ', Name2) AS ReceiverName FROM Registration 
                  WHERE accStatus = 'Approved' AND RegType IN ('Customer', 'DJ', 'Mcee', 'Band')";
$receiverStmt = $conn->prepare($receiverQuery);
$receiverStmt->execute();
$receivers = $receiverStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$receiverStmt->close();

// Fetch SenderName as Name1 and Name2 of the user in session
$senderQuery = "SELECT CONCAT(Name1, ' ', Name2) AS SenderName FROM Registration WHERE RegID = ?";
$senderStmt = $conn->prepare($senderQuery);
$senderStmt->bind_param("i", $UserID);
$senderStmt->execute();
$senderResult = $senderStmt->get_result()->fetch_assoc();
$SenderName = $senderResult ? $senderResult['SenderName'] : "";
$senderStmt->close();

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient_id'], $_POST['message_body'])) {
    $recipientID = $_POST['recipient_id'];
    $messageBody = trim($_POST['message_body']); // Trim spaces to prevent empty input
    $commsDate = date('Y-m-d'); // Set CommsDate to the current date

    if (empty($messageBody)) {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
        exit();
    }

    // Fetch ReceiverName
    $receiverQuery = "SELECT CONCAT(Name1, ' ', Name2) AS ReceiverName FROM Registration WHERE RegID = ?";
    $receiverStmt = $conn->prepare($receiverQuery);
    $receiverStmt->bind_param("i", $recipientID);
    $receiverStmt->execute();
    $receiverResult = $receiverStmt->get_result()->fetch_assoc();
    $ReceiverName = $receiverResult ? $receiverResult['ReceiverName'] : "";
    $receiverStmt->close();

    // Insert message with auto-generated CommsID
    $insertQuery = "INSERT INTO Comms (SenderID, RecipientID, SenderName, ReceiverName, Messagebody, CommsDate) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iissss", $UserID, $recipientID, $SenderName, $ReceiverName, $messageBody, $commsDate);
    
    if ($insertStmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Message sent successfully';
        $response['comms_id'] = $conn->insert_id; // Return the inserted CommsID
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to send message';
    }
    $insertStmt->close();
}

// Handle message response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comms_id'], $_POST['message_response'])) {
    $commsID = $_POST['comms_id'];
    $messageResponse = trim($_POST['message_response']); // Trim spaces to prevent empty response

    if (empty($messageResponse)) {
        echo json_encode(["status" => "error", "message" => "Response cannot be empty"]);
        exit();
    }

    $updateQuery = "UPDATE Comms SET MessageResponse = ? WHERE CommsID = ? AND RecipientID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sii", $messageResponse, $commsID, $UserID);
    
    if ($updateStmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Response added successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add response';
    }
    $updateStmt->close();
}

// Fetch messages sent or received by the user
$messagesQuery = "SELECT c.CommsID, c.SenderID, c.RecipientID, 
                         s.Name1 AS SenderName1, s.Name2 AS SenderName2, 
                         r.Name1 AS ReceiverName1, r.Name2 AS ReceiverName2, 
                         c.Messagebody, c.MessageResponse, c.CommsDate 
                  FROM Comms c
                  JOIN Registration s ON c.SenderID = s.RegID
                  JOIN Registration r ON c.RecipientID = r.RegID
                  WHERE c.SenderID = ? OR c.RecipientID = ?
                  ORDER BY c.CommsDate DESC";
$messagesStmt = $conn->prepare($messagesQuery);
$messagesStmt->bind_param("ii", $UserID, $UserID);
$messagesStmt->execute();
$messages = $messagesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$messagesStmt->close();

// Prepare final response
$response["receivers"] = $receivers;
$response["sender_name"] = $SenderName;
$response["messages"] = $messages;

// Output JSON response
header("Content-Type: application/json");
echo json_encode($response);
?>
