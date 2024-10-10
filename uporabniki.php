<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Data</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="10">
    <style>
        @media (max-width: 767px) {
            .navbar-nav {
                flex-direction: column;
                width: 100%;
            }

            .navbar-nav .nav-item + .nav-item {
                margin-left: 0;
            }

            .navbar-brand,
            .mx-auto,
            .d-flex {
                flex: 0 !important;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <nav class="navbar sticky-top navbar-light bg-light">
            <div class="container-fluid d-flex flex-column flex-md-row justify-content-between align-items-center">

                <div class="mx-auto mb-2 mb-md-0">

                    <div class="btn-group mb-2 mb-md-0" role="group">
                        <button type="button" id="all-bottom" class="btn btn-secondary mb-2 mb-md-0" onclick="resetFilters()">Vse</button>
                        <button type="button" id="inLibrary-bottom" class="btn btn-secondary mb-2 mb-md-0" onclick="filterLocation('Prihod')">Prihod</button>
                        <button type="button" id="outside-bottom" class="btn btn-secondary mb-2 mb-md-0" onclick="filterLocation('Odhod')">Odhod</button>
                    </div>

                </div>

            </div>
        </nav>

        <div class="container py-4 table-container">
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" onkeyup="searchTable()" placeholder="Iskanje...">
            </div>
            <div class="table-responsive">
                <table id="data-table" class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th class="d-none d-md-table-cell">ID userja
                                <span class="sort-arrow sort-up" onclick="sortTable('ID userja', 'asc')"></span>
                                <span class="sort-arrow sort-down" onclick="sortTable('ID userja', 'desc')"></span>
                            </th>
                            <th>Username</th>
                            <th>Dogodek</th>
                            <th>Datum
                                <span class="sort-arrow sort-up" onclick="sortTable('Èas', 'asc')"></span>
                                <span class="sort-arrow sort-down" onclick="sortTable('Èas', 'desc')"></span>
                            </th>
                            <th>Antena</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
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

                        // SQL query to fetch data from user_attendance joined with attendance and users
                        $sql = "
                            SELECT ua.user_id, u.username, ua.timestamp, ua.antenna, a.location
                            FROM user_attendance ua
                            LEFT JOIN users u ON ua.user_id = u.id
                            LEFT JOIN attendance a ON ua.antenna = a.antenna
                            ORDER BY ua.timestamp DESC
                        ";

                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            // Define the time zone
                            $timezone = new DateTimeZone('Europe/Ljubljana'); // Change to your desired time zone

                            // Output data of each row
                            while ($row = $result->fetch_assoc()) {
                                // Parse the timestamp with time zone offset
                                $timestamp = new DateTime($row['timestamp']);
                                $timestamp->setTimezone($timezone);
                                $formatted_timestamp = $timestamp->format('d.m.Y H:i:s');

                                echo "<tr>";
                                echo "<td class='d-none d-md-table-cell'>" . htmlspecialchars($row['user_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                echo "<td>" . htmlspecialchars($formatted_timestamp) . "</td>";
                                echo "<td>" . htmlspecialchars($row['antenna']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>0 results</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer>
            <div class="container">
            </div>
        </footer>
    </div>
    <!-- JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterLocation(location) {
            var rows = document.getElementById("data-table").getElementsByTagName("tbody")[0].getElementsByTagName("tr");

            for (var i = 0; i < rows.length; i++) {
                var locationCell = rows[i].getElementsByTagName("td")[2]; // assuming location column is the third column (index 2)
                if (location === 'all' || locationCell.textContent.trim() === location) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function resetFilters() {
            var rows = document.getElementById("data-table").getElementsByTagName("tbody")[0].getElementsByTagName("tr");

            for (var i = 0; i < rows.length; i++) {
                rows[i].style.display = "";
            }
        }

        function sortTable(column, order) {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById("data-table");
            switching = true;

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("td")[getColumnIndex(column)].textContent.trim();
                    y = rows[i + 1].getElementsByTagName("td")[getColumnIndex(column)].textContent.trim();

                    if (order === 'asc') {
                        if (new Date(x) > new Date(y)) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (order === 'desc') {
                        if (new Date(x) < new Date(y)) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }

                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }

            resetSortArrows();
            var header = document.getElementById("data-table").getElementsByTagName("th")[getColumnIndex(column)];
            if (order === 'asc') {
                header.getElementsByClassName("sort-up")[0].style.display = "inline-block";
            } else if (order === 'desc') {
                header.getElementsByClassName("sort-down")[0].style.display = "inline-block";
            }
        }

        function getColumnIndex(columnName) {
            var headers = document.getElementById("data-table").getElementsByTagName("th");
            for (var i = 0; i < headers.length; i++) {
                if (headers[i].textContent.trim() === columnName) {
                    return i;
                }
            }
            return -1;
        }

        function resetSortArrows() {
            var arrowsUp = document.getElementsByClassName("sort-up");
            var arrowsDown = document.getElementsByClassName("sort-down");
            for (var i = 0; i < arrowsUp.length; i++) {
                arrowsUp[i].style.display = "none";
            }
            for (var i = 0; i < arrowsDown.length; i++) {
                arrowsDown[i].style.display = "none";
            }
        }

        function searchTable() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("data-table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }
    </script>
</body>

</html>
