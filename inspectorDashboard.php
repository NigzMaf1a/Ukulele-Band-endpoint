<?php
session_start();
require_once 'connection.php';

// Check if session is active and user has required privileges
if (!isset($_SESSION['UserID']) || $_SESSION['accStatus'] !== 'Approved' || $_SESSION['RegType'] !== 'Inspector') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$response = [];

// Get the latest customer from Services table
$sql_latest_customer = "
    SELECT r.Name1, r.PhoneNo 
    FROM Services s
    JOIN Customer c ON s.CustomerID = c.CustomerID
    JOIN Registration r ON c.RegID = r.RegID
    ORDER BY s.ServiceID DESC LIMIT 1
";
$result_customer = $conn->query($sql_latest_customer);
if ($result_customer && $result_customer->num_rows > 0) {
    $response['latest_customer'] = $result_customer->fetch_assoc();
} else {
    $response['latest_customer'] = null;
}

// Get the latest penalty amount
$sql_latest_penalty = "
    SELECT Penalty 
    FROM Penalty
    ORDER BY PenaltyID DESC LIMIT 1
";
$result_penalty = $conn->query($sql_latest_penalty);
if ($result_penalty && $result_penalty->num_rows > 0) {
    $row_penalty = $result_penalty->fetch_assoc();
    $response['latest_penalty'] = $row_penalty['Penalty'];
} else {
    $response['latest_penalty'] = null;
}

// Get total number of inspection records
$sql_total_inspections = "SELECT COUNT(*) AS total FROM Inspector";
$result_inspections = $conn->query($sql_total_inspections);
if ($result_inspections && $result_inspections->num_rows > 0) {
    $row_inspections = $result_inspections->fetch_assoc();
    $response['total_inspections'] = $row_inspections['total'];
} else {
    $response['total_inspections'] = 0;
}

// Get equipment conditions excluding CAT1 and count worst case CAT4
$sql_conditions = "
    SELECT Condition, COUNT(*) AS count 
    FROM Inspector 
    WHERE Condition != 'CAT1' 
    GROUP BY Condition
";
$result_conditions = $conn->query($sql_conditions);
$response['conditions'] = [];
$response['worst_case_count'] = 0;
if ($result_conditions && $result_conditions->num_rows > 0) {
    while ($row = $result_conditions->fetch_assoc()) {
        $response['conditions'][] = $row;
        if ($row['Condition'] == 'CAT4') {
            $response['worst_case_count'] = $row['count'];
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
