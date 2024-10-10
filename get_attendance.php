<?php
$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Ustvarimo povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preverimo povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Poizvedba za združitev podatkov iz tabel test, location_data in book_names
$sql = "SELECT t.idHex, t.antenna, t.eventNum, t.format, t.hostName, t.peakRssi, t.timestamp, t.type, 
               COALESCE(ld.location, '') AS location,
               COALESCE(b.bookName, 'Neznana knjiga') AS bookName
        FROM test t
        LEFT JOIN location_data ld ON t.antenna = ld.antenna
        LEFT JOIN book_names b ON t.idHex = b.idHex
        INNER JOIN users u ON t.idHex = u.rfid_tag"; // Izberemo samo vrstice, kjer je idHex enak rfid_tag-u v tabeli users

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} 

echo json_encode($data);

$conn->close();
?>
