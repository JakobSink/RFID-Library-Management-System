<?php
header('Content-Type: application/json');

// Podatki za povezavo z bazo
$servername = "localhost";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Ustvarjanje povezave
$conn = new mysqli($servername, $username, $password, $dbname);

// Preverjanje povezave
if ($conn->connect_error) {
    die("Povezava je spodletela: " . $conn->connect_error);
}

// SQL poizvedba za pridobitev podatkov
$sql = "SELECT t.idHex, t.antenna, t.eventNum, t.format, t.hostName, t.peakRssi, t.timestamp, t.type, 
       COALESCE(ld.location, '') AS location,
       COALESCE(b.bookName, 'Neznana knjiga') AS bookName,
       CONCAT(COALESCE(b.ime, ''), ' ', COALESCE(b.priimek, '')) AS ime_in_priimek
FROM test t
LEFT JOIN location_data ld ON t.antenna = ld.antenna
LEFT JOIN book_names b ON t.idHex = b.idHex
LEFT JOIN users u ON t.idHex = u.rfid_tag
WHERE u.rfid_tag IS NULL
AND (b.bookName IS NOT NULL AND b.bookName != '' 
     OR (b.ime IS NOT NULL AND b.ime != '') 
     OR (b.priimek IS NOT NULL AND b.priimek != ''))
ORDER BY t.timestamp DESC
LIMIT 200;";

// Izvajanje poizvedbe
$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Pošiljanje podatkov v JSON formatu
echo json_encode($data);

// Zapiranje povezave
$conn->close();
?>
