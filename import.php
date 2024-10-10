<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet library autoload file

use PhpOffice\PhpSpreadsheet\IOFactory;

// Handle file upload and processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["import"])) {
    if (isset($_FILES["import_file"]) && $_FILES["import_file"]["error"] == UPLOAD_ERR_OK) {
        $inputFileName = $_FILES["import_file"]["tmp_name"];

        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($inputFileName);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // Example: Database connection parameters
            $servername = "localhost";
            $username = "root";
            $password = "Spica2024!";
            $dbname = "rfid";

            // Connect to database
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
            }

            // Prepare and execute SQL statements
            $stmt = $conn->prepare("INSERT INTO test (idHex, antenna, eventNum, format, hostName, peakRssi, timestamp, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                die(json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]));
            }

            // Prepare statement for inserting into 'booknames' table
            $stmtBookName = $conn->prepare("INSERT INTO book_names (idHex, bookName, Ime, Priimek) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE bookName = VALUES(bookName), Ime = VALUES(Ime), Priimek = VALUES(Priimek)");
            if ($stmtBookName === false) {
                die(json_encode(["status" => "error", "message" => "Prepare failed for booknames table: " . $conn->error]));
            }

            foreach ($sheetData as $row) {
                // Skip header row
                if ($row === $sheetData[1]) {
                    continue;
                }

                // Extract data from Excel row
                $idHex = isset($row['A']) ? $row['A'] : '';
                $bookName = isset($row['B']) ? $row['B'] : '';
                $ime = isset($row['C']) ? $row['C'] : '';
                $priimek = isset($row['D']) ? $row['D'] : '';
                $antenna = isset($row['E']) ? $row['E'] : '';
                $timestamp = isset($row['F']) ? $row['F'] : '';
                $eventNum = isset($row['G']) ? $row['G'] : '0'; // Default to 0 if empty
                $format = isset($row['H']) ? $row['H'] : '';
                $hostName = isset($row['I']) ? $row['I'] : '';
                $peakRssi = isset($row['J']) ? $row['J'] : '0'; // Default to 0 if empty
                $type = isset($row['K']) ? $row['K'] : '';

                // Insert or update bookName in 'book_names' table
                $stmtBookName->bind_param("ssss", $idHex, $bookName, $ime, $priimek);
                if (!$stmtBookName->execute()) {
                    die(json_encode(["status" => "error", "message" => "Execution failed for book_names table: " . $stmtBookName->error]));
                }

                // Insert into 'test' table
                $stmt->bind_param("ssssssss", $idHex, $antenna, $eventNum, $format, $hostName, $peakRssi, $timestamp, $type);
                if (!$stmt->execute()) {
                    die(json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]));
                }
            }

            // Close statements and connection
            $stmt->close();
            $stmtBookName->close();
            $conn->close();

            // Return success message
            echo json_encode(["status" => "success", "message" => "Import successful. Number of records imported: " . (count($sheetData) - 1)]);
            exit();
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Import error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "File upload error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
