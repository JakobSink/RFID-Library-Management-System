<?php
$servername = "127.0.0.1";
$username_db = "root";
$password_db = "Spica2024!";
$dbname = "rfid";

// Pridobimo idHex in uporabniško ime iz POST zahtevka
$idHex = $_POST['idHex'];
$username = $_POST['username'];

// Ustvarimo povezavo
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Preverimo povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Preverimo vlogo uporabnika
$sql_check_role = "SELECT role FROM users WHERE username = '$username'";
$result_role = $conn->query($sql_check_role);

if ($result_role->num_rows > 0) {
    $row = $result_role->fetch_assoc();
    $role = $row['role'];
    
    // Preverimo, ali je uporabnik administrator
    if ($role === 'admin') {
        // Izvedemo brisanje
        $sql_delete = "DELETE FROM test WHERE idHex = '$idHex'";
        if ($conn->query($sql_delete) === TRUE) {
            $response = array("status" => "success", "message" => "Podatek uspešno izbrisan.");
        } else {
            $response = array("status" => "error", "message" => "Napaka pri brisanju: " . $conn->error);
        }
    } else {
        $response = array("status" => "error", "message" => "Nimate ustreznih pravic za izbris podatkov.");
    }
} else {
    $response = array("status" => "error", "message" => "Uporabnik ne obstaja ali nima dodeljene vloge.");
}

$conn->close();

// Vrnemo odgovor kot JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
