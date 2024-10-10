<?php
$servername = "localhost";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch books and users for the dropdowns
$books = $conn->query("SELECT idHex, bookname FROM book_names");
$users = $conn->query("SELECT id, username, CONCAT(Ime, ' ', Priimek) AS full_name FROM users");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? 'add';
    $book_id = $_POST['book_id'];
    $user_id = $_POST['user_id'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $reservation_id = $_POST['reservation_id'] ?? null;

    // Validate date range
    $start_date = new DateTime($start_datetime);
    $end_date = new DateTime($end_datetime);
    $interval = $start_date->diff($end_date);
    if ($interval->days > 14 || $end_date < $start_date) {
        $message = "Rezervacija ne sme trajati dlje kot 14 dni ali ni veljavna.";
    } else {
        // Check if the book exists
        $sql_check_book = "SELECT 1 FROM book_names WHERE idHex = ?";
        $stmt_check_book = $conn->prepare($sql_check_book);
        $stmt_check_book->bind_param("s", $book_id);
        $stmt_check_book->execute();
        $result_check_book = $stmt_check_book->get_result();
        if ($result_check_book->num_rows === 0) {
            $message = "Knjiga z izbranim ID-jem ne obstaja.";
        } else {
            // Check if the user exists
            $sql_check_user = "SELECT 1 FROM users WHERE id = ?";
            $stmt_check_user = $conn->prepare($sql_check_user);
            $stmt_check_user->bind_param("i", $user_id);
            $stmt_check_user->execute();
            $result_check_user = $stmt_check_user->get_result();
            if ($result_check_user->num_rows === 0) {
                $message = "Uporabnik z izbranim ID-jem ne obstaja.";
            } else {
                // Check if the book is already reserved in the given time range
                $sql_check = "SELECT * FROM rentals WHERE book_id = ? AND 
                              (start_datetime < ? AND end_datetime > ?)" . 
                              ($action == 'update' && $reservation_id ? " AND id != ?" : "");
                $stmt_check = $conn->prepare($sql_check);
                if ($action == 'update' && $reservation_id) {
                    $stmt_check->bind_param("issi", $book_id, $end_datetime, $start_datetime, $reservation_id);
                } else {
                    $stmt_check->bind_param("iss", $book_id, $end_datetime, $start_datetime);
                }
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows > 0) {
                    $message = "Knjiga je že rezervirana v tem časovnem obdobju.";
                } else {
                    if ($action == 'update' && $reservation_id) {
                        // Update reservation
                        $sql_update = "UPDATE rentals SET book_id = ?, user_id = ?, start_datetime = ?, end_datetime = ? WHERE id = ?";
                        $stmt_update = $conn->prepare($sql_update);
                        $stmt_update->bind_param("iissi", $book_id, $user_id, $start_datetime, $end_datetime, $reservation_id);

                        if ($stmt_update->execute()) {
                            $message = "Rezervacija uspešno posodobljena.";
                        } else {
                            $message = "Napaka pri posodabljanju rezervacije: " . $conn->error;
                        }
                        $stmt_update->close();
                    } else {
                        // Insert reservation
                        $sql_insert = "INSERT INTO rentals (book_id, user_id, start_datetime, end_datetime) 
                                       VALUES (?, ?, ?, ?)";
                        $stmt_insert = $conn->prepare($sql_insert);
                        $stmt_insert->bind_param("iiss", $book_id, $user_id, $start_datetime, $end_datetime);

                        if ($stmt_insert->execute()) {
                            $message = "Rezervacija uspešno dodana.";
                        } else {
                            $message = "Napaka pri dodajanju rezervacije: " . $conn->error;
                        }
                        $stmt_insert->close();
                    }
                }
                $stmt_check->close();
            }
            $stmt_check_user->close();
        }
        $stmt_check_book->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervacija Knjige</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<link rel="stylesheet" href="style.css">

    <style>
        /* General Body Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #495057;
        }

        /* Container Styles */
       
        /* Header Styles */
        h1, h2 {
            color: #007bff;
        }

        /* Form Styles */
        .form-group label {
            font-weight: bold;
        }

        .form-group input, .form-group select {
            border-radius: 4px;
            border: 1px solid #ced4da;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        /* Calendar Styles */
        #calendar {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
 <?php include 'sidebar.php'; ?>
    <div class="container">
        <h1 class="mt-4">Rezervacija Knjige</h1>

        <!-- Reservation Form -->
        <form method="post" id="reservation-form">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="reservation_id" id="reservation_id">
            <div class="form-group">
                <label for="book_id">Knjiga:</label>
                <select id="book_id" name="book_id" class="form-control" required>
                    <option value="">Izberi knjigo</option>
                    <?php while ($book = $books->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($book['idHex']); ?>"><?php echo htmlspecialchars($book['bookname']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="user_id">Uporabnik:</label>
                <select id="user_id" name="user_id" class="form-control" required>
                    <option value="">Izberi uporabnika</option>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['full_name']); ?> (Username: <?php echo htmlspecialchars($user['username']); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start_datetime">Začetek rezervacije:</label>
                <input type="datetime-local" id="start_datetime" name="start_datetime" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_datetime">Konec rezervacije:</label>
                <input type="datetime-local" id="end_datetime" name="end_datetime" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" id="submit-button">Rezerviraj</button>
        </form>

        <!-- Message Display -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info mt-4" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Calendar Display -->
        <h2 class="mt-4">Pregled Rezervacij</h2>
        <div id='calendar'></div>
    </div>

    <!-- FullCalendar JavaScript -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                editable: true,
                events: 'get_reservations.php',
                eventClick: function(info) {
                    fetch('get_reservation_details.php?id=' + info.event.id)
                        .then(response => response.json())
                        .then(data => {
                            document.querySelector('input[name="action"]').value = 'update';
                            document.querySelector('input[name="reservation_id"]').value = data.id;
                            document.querySelector('select[name="book_id"]').value = data.book_id;
                            document.querySelector('select[name="user_id"]').value = data.user_id;
                            document.querySelector('input[name="start_datetime"]').value = new Date(data.start_datetime).toISOString().slice(0, 16);
                            document.querySelector('input[name="end_datetime"]').value = new Date(data.end_datetime).toISOString().slice(0, 16);
                            document.getElementById('submit-button').textContent = 'Posodobi';
                        });
                }
            });

            calendar.render();
        });
    </script>
</body>
</html>
