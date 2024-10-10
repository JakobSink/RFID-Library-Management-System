<?php
$servername = "localhost";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$id = $_POST['id'];
$start = $_POST['start'];
$end = $_POST['end'];

// Update reservation
$sql = "UPDATE rentals SET start_datetime = ?, end_datetime = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $start, $end, $id);

if ($stmt->execute()) {
    echo "Event updated successfully";
} else {
    echo "Error updating event: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
