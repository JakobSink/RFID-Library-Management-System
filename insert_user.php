<?php
$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Check if all fields are filled
if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['email'])) {
    $response = array(
        "status" => "error",
        "message" => "Prosim, izpolnite vsa polja."
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare data from POST
$username = $_POST['username'];
$password = $_POST['password']; // Note: In a real application, use password_hash() for security
$email = $_POST['email'];
$ime = $_POST['ime'];
$priimek = $_POST['priimek'];

// Check if username already exists
$sql_check_username = "SELECT * FROM users WHERE username = '$username'";
$result_check_username = $conn->query($sql_check_username);

if ($result_check_username->num_rows > 0) {
    $response = array(
        "status" => "error",
        "message" => "Uporabnik s tem uporabniškim imenom že obstaja."
    );
} else {
    // Insert user into database
    $sql = "INSERT INTO users (username, password, email, ime , priimek) VALUES ('$username', '$password', '$email','$ime', '$priimek')";

    if ($conn->query($sql) === TRUE) {
        $response = array(
            "status" => "success",
            "message" => "Uporabnik uspešno ustvarjen."
        );
    } else {
        $response = array(
            "status" => "error",
            "message" => "Napaka pri ustvarjanju uporabnika: " . $conn->error
        );
    }
}

$conn->close();

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
