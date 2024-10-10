<?php
$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Pridobimo uporabniško ime in geslo iz POST zahtevka
$usernameInput = $_POST['username'];
$passwordInput = $_POST['password'];

// Ustvarimo povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preverimo povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Pripravimo SQL stavek za preverjanje uporabnika in gesla
$sql = "SELECT * FROM users WHERE username = '$usernameInput' AND password = '$passwordInput'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Uporabnik najden, geslo je pravilno
    $response = array("status" => "success", "message" => "Pravilno geslo.");
} else {
    // Uporabnik ni najden ali geslo ni pravilno
    $response = array("status" => "error", "message" => "Napačno uporabniško ime ali geslo.");
}

$conn->close();

// Vrnemo odgovor kot JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
