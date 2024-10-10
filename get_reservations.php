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

// Fetch reservations
$sql = "SELECT r.id, b.bookname AS title, r.start_datetime AS start, r.end_datetime AS end, CONCAT(u.Ime, ' ', u.Priimek) AS description
        FROM rentals r
        JOIN book_names b ON r.book_id = b.idHex
        JOIN users u ON r.user_id = u.id";
$result = $conn->query($sql);

$events = array();
while ($row = $result->fetch_assoc()) {
    $events[] = array(
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => $row['end'],
        'description' => $row['description']
    );
}

header('Content-Type: application/json');
echo json_encode($events);

$conn->close();
?>
