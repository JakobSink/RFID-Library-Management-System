<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet library autoload file

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Database connection
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

// SQL query
$sql = "SELECT t.idHex, t.antenna, t.eventNum, t.format, t.hostName, t.peakRssi, t.timestamp, t.type, 
               COALESCE(ld.location, '') AS location,
               COALESCE(b.bookName, 'Neznana knjiga') AS bookName,
               COALESCE(b.ime, 'Neznano Ime') AS authorName,
               COALESCE(b.priimek, 'Neznan Priimek') AS authorSur
        FROM test t
        LEFT JOIN location_data ld ON t.antenna = ld.antenna
        LEFT JOIN book_names b ON t.idHex = b.idHex
        ORDER BY t.timestamp desc
        ";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Initialize PhpSpreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Exported Data');

    // Set headers
    $headers = ['RFID', 'Naslov knjige', 'Ime Avtorja', 'Priimek Avtorja', 'Zadnjiè videno', 'Antena', 'Èas zadnjega premika', 'Event Num', 'Format', 'Host Name', 'Peak RSSI', 'Type'];
    foreach ($headers as $index => $header) {
        $sheet->setCellValue(chr(65 + $index) . '1', $header);
    }

    // Fetching data from database
    $row = 2; // Start row for data
    while ($row_data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $row_data['idHex']);
        $sheet->setCellValue('B' . $row, $row_data['bookName']);
        $sheet->setCellValue('C' . $row, $row_data['authorName']);
        $sheet->setCellValue('D' . $row, $row_data['authorSur']);
        $sheet->setCellValue('E' . $row, $row_data['location']);
        $sheet->setCellValue('F' . $row, $row_data['antenna']);
        $sheet->setCellValue('G' . $row, date('Y.m.d', strtotime($row_data['timestamp'])));
        $sheet->setCellValue('H' . $row, $row_data['eventNum']);
        $sheet->setCellValue('I' . $row, $row_data['format']);
        $sheet->setCellValue('J' . $row, $row_data['hostName']);
        $sheet->setCellValue('K' . $row, $row_data['peakRssi']);
        $sheet->setCellValue('L' . $row, $row_data['type']);

        $row++;
    }

    // Set column widths
    $columnWidths = [25, 25, 20, 20, 25, 15, 15, 20, 15, 15, 15];
    foreach ($columnWidths as $columnKey => $width) {
        $sheet->getColumnDimensionByColumn($columnKey + 1)->setWidth($width);
    }

    // Style header row
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '000000'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'dedede'],
        ],
    ];
    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

    // Output the file to browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="RFID_podatki.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    echo "0 results";
}

$conn->close();
?>
