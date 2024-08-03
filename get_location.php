<?php
include 'Connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the coordinates for the base with ID 1
$base_id = 1;
$sql = "SELECT base_location FROM base WHERE base_id = $base_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $coordinates = explode(', ', $row['base_location']);
    $response = array('latitude' => $coordinates[0], 'longitude' => $coordinates[1]);
    echo json_encode($response);
} else {
    echo json_encode(array('error' => 'No coordinates found'));
}

$conn->close();
?>
