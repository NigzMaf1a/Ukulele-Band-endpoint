<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Accountant') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

$sql = "
    SELECT 
        f.TransactionID, 
        f.TransactType, 
        f.TransactionDate, 
        f.Amount
    FROM Finance f
    LEFT JOIN CustomerPayment cp ON f.TransactionID = cp.TransactionID
    LEFT JOIN Withdraw w ON f.TransactionID = w.TransactionID
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$stmt->close();
$conn->close();
?>
