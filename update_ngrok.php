<?php
$ngrok_api_url = "http://localhost:4040/api/tunnels"; // Ngrok API URL
$database_host = "localhost";
$database_user = "root"; // Change if needed
$database_pass = "Itz3ree!"; // Change if needed
$database_name = "ukulele"; // Change to your DB name

// Connect to MySQL
$conn = new mysqli($database_host, $database_user, $database_pass, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Ngrok URL from API
$response = file_get_contents($ngrok_api_url);
$data = json_decode($response, true);

if (isset($data['tunnels'][0]['public_url'])) {
    $ngrok_url = $data['tunnels'][0]['public_url'];

    // Insert or Update the URL
    $sql = "INSERT INTO ngrok_urls (ngrok_url) VALUES ('$ngrok_url')
            ON DUPLICATE KEY UPDATE ngrok_url='$ngrok_url', updated_at=CURRENT_TIMESTAMP";

    if ($conn->query($sql) === TRUE) {
        echo "Ngrok URL updated: $ngrok_url";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
