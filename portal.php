<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Povezava z bazo
$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// Poizvedba za podatke
$sql = "SELECT t.idHex, t.antenna, t.eventNum, t.format, t.hostName, t.peakRssi, t.timestamp, t.type, 
               COALESCE(ld.location, '') AS location,
               COALESCE(b.bookName, 'Neznana knjiga') AS bookName
        FROM test t
        LEFT JOIN location_data ld ON t.antenna = ld.antenna
        LEFT JOIN book_names b ON t.idHex = b.idHex";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="sl"> <!-- Spremenjeno v slovenski jezik -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal za grafe in izvoz/uvoz podatkov</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa; /* Spremenjena barva ozadja */
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        .container {
            max-width: 800px; /* Prilagojeno maksimalno širino */
            margin: 0 auto;
        }
        #export-import {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: inline-block;
            margin-right: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background-color: #fff; /* Spremenjena barva ozadja tabele */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            cursor: pointer;
        }
        .btn {
            width: 200px; /* Prilagojena širina gumba */
        }
        #chart-container {
            margin: 20px auto;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            display: none; /* Začetno skrito */
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar sticky-top navbar-light bg-light justify-content-between">
        <a class="navbar-brand" href="index.php">
            <img src="Spica-logo-web.png" width="auto" height="30" class="d-inline-block align-top" alt="">
        </a>
        <div>
            <a href="vstavljanje.php" class="btn btn-outline-primary mr-2 mb-2 mb-md-0">Vnos</a>
            <a href="portal.php" class="btn btn-outline-primary mr-2 mb-2 mb-md-0">Izvoz/Uvoz</a>
            <a href="ustvariracun.php" class="btn btn-outline-primary mr-2 mb-2 mb-md-0">Ustvari račun</a>
        </div>
    </nav>
    <div class="container">
        <h1>Portal za grafe in izvoz/uvoz podatkov</h1>

        <!-- GUMB ZA IZVOZ PODATKOV -->
        <div id="export-import">
            <form method="post" action="export.php">
                <button type="submit" name="export" class="btn btn-primary">Izvoz podatkov v Excel</button>
            </form>
            <form method="post" enctype="multipart/form-data" action="import.php">
                <input type="file" name="import_file" class="btn btn-secondary">
                <button type="submit" name="import" class="btn btn-primary">Uvoz podatkov iz Excela</button>
            </form>
        </div>

        
    </div>
    </br>
    </br>
    <footer>
        <div class="container">
                    </div>
    </footer>
</body>
</html>
