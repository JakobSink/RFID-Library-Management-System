<?php
$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Pridobimo idHex iz GET parametra
$idHex = $_GET['idHex'];

// Ustvarimo povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preverimo povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Pripravimo SQL stavek za poizvedbo
$sql = "SELECT bookName, ime, priimek FROM book_names WHERE idHex = '$idHex'";
$result = $conn->query($sql);

// Preverimo ali obstaja rezultat
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['bookName' => $row["bookName"], 'ime' => $row["ime"], 'priimek' => $row["priimek"]]);
} else {
    echo json_encode(['bookName' => '', 'ime' => '', 'priimek' => '']); // Če ni rezultata, vrnemo prazno vrednost
}

$conn->close();
?>
