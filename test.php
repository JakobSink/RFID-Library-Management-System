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

// Poizvedba
$sql = "SELECT idHex, antenna, eventNum, format, hostName, peakRssi, timestamp, type FROM test";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Izpišemo podatke vsake vrstice
    echo "<table border='1'>";
    echo "<tr><th>idHex</th><th>antenna</th><th>eventNum</th><th>format</th><th>hostName</th><th>peakRssi</th><th>timestamp</th><th>type</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['idHex']}</td>
                <td>{$row['antenna']}</td>
                <td>{$row['eventNum']}</td>
                <td>{$row['format']}</td>
                <td>{$row['hostName']}</td>
                <td>{$row['peakRssi']}</td>
                <td>{$row['timestamp']}</td>
                <td>{$row['type']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 rezultatov";
}

$conn->close();
?>
