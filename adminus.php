<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje uporabnikov</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1500px; /* Povečana širina kontejnerja */
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            display: none; /* Skrijemo začetno */
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
        .options-header {
            text-align: center; /* Sredinsko poravnavanje besedila */
        }
        .options-column {
            text-align: center; /* Sredinsko poravnavanje besedila */
            vertical-align: middle; /* Sredinsko poravnavanje vsebine v celici */
            width: 10%; /* Širina stolpca Možnosti */
        }
        .table th, .table td {
            padding: .75rem; /* Dodan padding za boljši razmik */
        }
        .role-select, .rfid-input {
            width: 100%; /* Polna širina za select in input */
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body class="bg-light">
    <nav class="navbar sticky-top navbar-light bg-light justify-content-between">
        <a class="navbar-brand" href="index.php">
            <img src="Spica-logo-web.png" width="auto" height="30" class="d-inline-block align-top" alt="">
        </a>
        <div>
            <a href="vstavljanje.php" class="btn btn-outline-primary mr-2 mb-2 mb-md-0">Vnos</a>
            <a href="ustvariracun.php" class="btn btn-outline-primary mr-2 mb-2 mb-md-0">Ustvari račun</a>
            <a href="logout.php" class="btn btn-outline-danger mb-2 mb-md-0">Odjava</a>
        </div>
    </nav>
    <div class="container py-4">
        <h1>Upravljanje uporabnikov</h1>

        <!-- Sporočila o uspehu ali napaki -->
        <div id="message" class="message mt-3"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-3">
                <thead>
                    <tr>
                        <th style="width: 15%;">Ime</th>
                        <th style="width: 15%;">Priimek</th>
                        <th style="width: 20%;">Uporabniško ime</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Vloga</th>
                        <th style="width: 15%;">RFID tag</th>
                        <th colspan="2" class="options-header">Možnosti</th> <!-- Združen header za Možnosti -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    session_start();
                    if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
                        header("Location: login.php");
                        exit();
                    }

                    // Povezava na podatkovno bazo
                    $servername = "127.0.0.1";
                    $username_db = "root";
                    $password_db = "Spica2024!";
                    $dbname = "rfid";

                    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
                    if ($conn->connect_error) {
                        die("Povezava ni uspela: " . $conn->connect_error);
                    }

                    // Poizvedba za pridobitev vseh uporabnikov
                    $sql = "SELECT * FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['ime']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['priimek']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                            echo '<td>';
                            echo '<select class="form-control role-select" data-username="' . $row['username'] . '">';
                            echo '<option value="admin" ' . ($row['role'] === 'admin' ? 'selected' : '') . '>Admin</option>';
                            echo '<option value="user" ' . ($row['role'] === 'user' ? 'selected' : '') . '>User</option>';
                            echo '</select>';
                            echo '</td>';
                            echo '<td>';
                            echo '<input type="text" class="form-control rfid-input" value="' . htmlspecialchars($row['rfid_tag']) . '" data-username="' . $row['username'] . '">';
                            echo '</td>';
                            echo '<td class="options-column">'; // Prvi stolpec Možnosti
                            echo '<button class="btn btn-sm btn-primary mb-1" onclick="editRFID(\'' . $row['username'] . '\')">Shrani</button>';
                            echo '</td>';
                            echo '<td class="options-column">'; // Drugi stolpec Možnosti
                            echo '<button class="btn btn-sm btn-danger" onclick="confirmDelete(\'' . $row['username'] . '\')">Izbriši</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8">Ni rezultatov.</td></tr>';
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal za potrditev brisanja -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Potrditev brisanja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Ste prepričani, da želite izbrisati uporabnika: <span id="deleteUsername"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Prekliči</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Izbriši</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editRFID(username) {
            var newRFID = $('.rfid-input[data-username="' + username + '"]').val();

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "userji/update_rfid.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    var messageDiv = document.getElementById("message");
                    messageDiv.className = "message alert " + response.status;
                    messageDiv.innerHTML = response.message;
                    messageDiv.style.display = "block"; // Prikažemo sporočilo
                    // Refresh page after successful operation
                    if (response.status === "success") {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                }
            };
            var data = "username=" + encodeURIComponent(username) + "&rfid=" + encodeURIComponent(newRFID);
            xhr.send(data);
        }

        function confirmDelete(username) {
            $('#deleteModal').modal('show');
            $('#deleteUsername').text(username);
            $('#confirmDeleteButton').off('click').on('click', function() {
                deleteUser(username);
            });
        }

        function deleteUser(username) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "userji/delete_user.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    var messageDiv = document.getElementById("message");
                    messageDiv.className = "message alert " + response.status;
                    messageDiv.innerHTML = response.message;
                    messageDiv.style.display = "block"; // Prikažemo sporočilo
                    // Ponovno naložimo stran po uspešnem brisanju
                    if (response.status === "success") {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                }
            };
            var data = "username=" + encodeURIComponent(username);
            xhr.send(data);
            $('#deleteModal').modal('hide');
        }

        // Ob spremembi vloge shranimo novo vlogo v bazo
        $('.role-select').change(function() {
            var username = $(this).data('username');
            var role = $(this).val();

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "userji/update_role.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    var messageDiv = document.getElementById("message");
                    messageDiv.className = "message alert " + response.status;
                    messageDiv.innerHTML = response.message;
                    messageDiv.style.display = "block"; // Prikažemo sporočilo
                    // Refresh page after successful operation
                    if (response.status === "success") {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                }
            };
            var data = "username=" + encodeURIComponent(username) + "&role=" + encodeURIComponent(role);
            xhr.send(data);
        });
    </script>
</body>
</html>
