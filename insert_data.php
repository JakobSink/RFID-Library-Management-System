<?php
$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Pridobimo podatke iz POST zahtevka
$idHex = $_POST['idHex'];
$bookName = $_POST['bookName'];
$ime = $_POST['ime'];
$priimek = $_POST['priimek'];

// Ustvarimo povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preverimo povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Pripravimo SQL stavek za vstavljanje ali posodabljanje
$sql_check = "SELECT * FROM book_names WHERE idHex = '$idHex'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    // Če že obstaja, posodobimo podatke
    $sql_update = "UPDATE book_names SET bookName = '$bookName', ime = '$ime', priimek = '$priimek' WHERE idHex = '$idHex'";
    if ($conn->query($sql_update) === TRUE) {
        $response = array("status" => "success", "message" => "Vnos posodobljen.");
    } else {
        $response = array("status" => "error", "message" => "Napaka pri posodabljanju: " . $conn->error);
    }
} else {
    // Če ne obstaja, vstavimo nov vnos
    $sql_insert = "INSERT INTO book_names (idHex, bookName, ime, priimek) VALUES ('$idHex', '$bookName', '$ime', '$priimek')";
    if ($conn->query($sql_insert) === TRUE) {
        $response = array("status" => "success", "message" => "Podatki uspešno vstavljeni.");
    } else {
        $response = array("status" => "error", "message" => "Napaka pri vstavljanju: " . $conn->error);
    }
}

$conn->close();

// Vrnemo odgovor kot JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
