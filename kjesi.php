<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "127.0.0.1";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$bookname = '';
$authorName = '';
$idHex = '';
$bookSearchType = 'contains'; // Default search type for bookname
$authorSearchType = 'contains'; // Default search type for author name
$idHexSearchType = 'contains'; // Default search type for idHex
$results = [];
$noResults = false;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookname = $_POST['bookname'] ?? '';
    $authorName = $_POST['authorName'] ?? '';
    $idHex = $_POST['idHex'] ?? '';
    $bookSearchType = $_POST['bookSearchType'] ?? 'contains';
    $authorSearchType = $_POST['authorSearchType'] ?? 'contains';
    $idHexSearchType = $_POST['idHexSearchType'] ?? 'contains';

    // Prepare the LIKE clause based on search type
    $getSearchOperator = function($searchType) {
        return ($searchType == 'contains') ? 'LIKE' : '=';
    };
    $wildcard = function($searchType) {
        return ($searchType == 'contains') ? '%' : '';
    };

    // Split authorName into first and last name
    list($authorFirstName, $authorLastName) = array_pad(explode(' ', $authorName, 2), 2, '');

    // Prepare search query based on provided criteria
   $sql = "
    SELECT
        bua.book_id,
        bua.bookname,
        bua.author_first_name,
        bua.author_last_name,
        bua.book_timestamp,
        bua.book_antenna,
        bl.location AS book_antenna_location,
        bua.user_timestamp,
        bua.user_antenna,
        ul.location AS user_antenna_location,
        bua.username
    FROM book_user_attendance bua
    LEFT JOIN location_data bl ON bua.book_antenna = bl.antenna
    LEFT JOIN location_data ul ON bua.user_antenna = ul.antenna
    WHERE bua.bookname {$getSearchOperator($bookSearchType)} ?
        AND bua.author_first_name {$getSearchOperator($authorSearchType)} ?
        AND bua.author_last_name {$getSearchOperator($authorSearchType)} ?
        AND bua.book_id {$getSearchOperator($idHexSearchType)} ?
        AND EXISTS (
            SELECT 1 
            FROM book_user_attendance AS sub
            WHERE sub.book_id = bua.book_id
            AND ABS(TIMESTAMPDIFF(SECOND, sub.book_timestamp, bua.book_timestamp)) <= 120
        )
    ORDER BY bua.book_timestamp DESC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }
    $likeBookname = $wildcard($bookSearchType) . $bookname . $wildcard($bookSearchType);
    $likeAuthorFirstName = $wildcard($authorSearchType) . $authorFirstName . $wildcard($authorSearchType);
    $likeAuthorLastName = $wildcard($authorSearchType) . $authorLastName . $wildcard($authorSearchType);
    $likeIdHex = $wildcard($idHexSearchType) . $idHex . $wildcard($idHexSearchType);
    $stmt->bind_param('ssss', $likeBookname, $likeAuthorFirstName, $likeAuthorLastName, $likeIdHex);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Formatirajte timestamp brez spreminjanja časovnega pasu
        // Predpostavimo, da so podatki že v lokalnem času, samo formatiramo
        $row['book_timestamp'] = (new DateTime($row['book_timestamp']))->format('d.m.Y H:i:s');

        $row['user_timestamp'] = $row['user_timestamp']
                                ? (new DateTime($row['user_timestamp']))->format('d.m.Y H:i:s')
                                : 'Neznano';
        
        $results[] = $row;
	    }
	} else {
	    $noResults = true;
	}
}

?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iskanje Podrobnosti Knjige</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
	
              .table thead th {
            background-color: #f8f9fa; /* Svetlejši barvni odtenek za glavo tabele */
            color: #343a40; /* Temnejša barva besedila za boljši kontrast */
        }
 	/* Form Container */
        .search-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #f8f9fa;
           box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Input and Select Styling */
        .search-form .form-control {
            border-radius: 0px;
            border: 1px solid #e0e0e0;
            margin-right: 5px;
            min-width: 100px;
            max-width: 200px;
        }

        /* Search Button */
        .search-form .btn-search {
            background-color: #24a0ed;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        /* Dropdown Icon Adjustment */
        .input-group-append .form-control {
            margin-left: -10px;
            border-radius: 0 20px 20px 0;
        }

        /* Input Group Custom Styling */
        .input-group {
            display: flex;
            align-items: center;
        }

        .form-group {
            margin-bottom: 0;
            flex: 1;
        }
	.table-responsive {
            max-height: 550px; /* Adjust the height as per your requirement */
            overflow-y: scroll;
        }
	.sredina{
		align-items: center;
}
    </style>
</head>
<body>
 
 <?php include 'sidebar.php'; ?>
     <div class="container py-4">

        <h1 class="mb-4 text-center">Iskanje Podrobnosti Knjige</h1>
	<div class="sredina"><?php include 'fetch2.php'; ?></div>
        <form method="post" class="search-form">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" id="bookname" name="bookname" class="form-control" placeholder="Ime Knjige" value="<?php echo htmlspecialchars($bookname ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="input-group-append">
                        <select id="bookSearchType" name="bookSearchType" class="form-control">
                            <option value="contains" <?php if ($bookSearchType == 'contains') echo 'selected'; ?>>Vsebuje</option>
                            <option value="exact" <?php if ($bookSearchType == 'exact') echo 'selected'; ?>>Točno</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <input type="text" id="authorName" name="authorName" class="form-control" placeholder="Avtor (Ime in Priimek)" value="<?php echo htmlspecialchars($authorName ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="input-group-append">
                        <select id="authorSearchType" name="authorSearchType" class="form-control">
                            <option value="contains" <?php if ($authorSearchType == 'contains') echo 'selected'; ?>>Vsebuje</option>
                            <option value="exact" <?php if ($authorSearchType == 'exact') echo 'selected'; ?>>Točno</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <input type="text" id="idHex" name="idHex" class="form-control" placeholder="ID Hex" value="<?php echo htmlspecialchars($idHex ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="input-group-append">
                        <select id="idHexSearchType" name="idHexSearchType" class="form-control">
                            <option value="contains" <?php if ($idHexSearchType == 'contains') echo 'selected'; ?>>Vsebuje</option>
                            <option value="exact" <?php if ($idHexSearchType == 'exact') echo 'selected'; ?>>Točno</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-search">Search</button>
        </form>
 <br>

        <?php if ($noResults): ?>
            <div class="alert alert-warning">Ni rezultatov za vaše iskanje.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>ID Hex</th>
                            <th>Ime Knjige</th>
                            <th>Avtor</th>
                            <th>Čas premika  Knjige</th>
                            <th>Antena Knjige</th>
                            <th>Lokacija Antene</th>
                            <th>Uporabnik</th>
                            <th>Čas premika Uporabnika</th>
                            <th>Antenna Uporabnika</th>
			    <th>Lokacija antene uporabnika</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['book_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['bookname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(($row['author_first_name'] ?? '') . ' ' . ($row['author_last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['book_timestamp'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['book_antenna'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['book_antenna_location'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['username'] ?? 'Neznano', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['user_timestamp'] ?? 'Neznano', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['user_antenna'] ?? 'Neznano', ENT_QUOTES, 'UTF-8'); ?></td>
				<td><?php echo htmlspecialchars($row['user_antenna_location'] ?? 'Neznano', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
<footer>
        <div class="container">
                    </div>
    </footer
        <?php endif; ?>

    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 

</body>


</html>