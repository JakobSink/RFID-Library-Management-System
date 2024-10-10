<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vstavljanje in Brisanje podatkov</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
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
        #export-import {
            text-align: center;
            margin-bottom: 20px;
        }
        .form {
            display: inline-block;
            margin-right: 10px;
        }

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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function submitForm(action) {
            var idHex = document.getElementById("idHex").value;
            var bookName = document.getElementById("bookName").value;
            var ime = document.getElementById("ime").value;
            var priimek = document.getElementById("priimek").value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", action, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    var messageDiv = document.getElementById("message");
                    messageDiv.className = "message alert " + response.status;
                    messageDiv.innerHTML = response.message;
                    messageDiv.style.display = "block"; // Prikažemo sporočilo
                    // Clear inputs after successful operation
                    if (response.status === "success") {
                        document.getElementById("idHex").value = "";
                        document.getElementById("bookName").value = "";
                        document.getElementById("ime").value = "";
                        document.getElementById("priimek").value = "";
                    }
                }
            };

            if (action === 'delete_data.php') {
                // Open password modal for delete confirmation
                $('#passwordModal').modal('show');
                document.getElementById('deleteConfirmButton').addEventListener('click', function() {
                    var username = document.getElementById('username').value;
                    var password = document.getElementById('password').value;

                    var verifyXHR = new XMLHttpRequest();
                    verifyXHR.open("POST", "verify_password.php", true);
                    verifyXHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    verifyXHR.onreadystatechange = function() {
                        if (verifyXHR.readyState == 4 && verifyXHR.status == 200) {
                            var verifyResponse = JSON.parse(verifyXHR.responseText);
                            if (verifyResponse.status === "success") {
                                // Proceed with deletion
                                var data = "idHex=" + encodeURIComponent(idHex) + "&username=" + encodeURIComponent(username);
                                xhr.send(data);
                                
                            } else {
                                // Show error message
                                var messageDiv = document.getElementById("message");
                                messageDiv.className = "message alert error";
                                messageDiv.innerHTML = verifyResponse.message;
                                messageDiv.style.display = "block";
                                $('#passwordModal').modal('hide');
                            }
                        }
                    };
                    var verifyData = "username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password);
                    verifyXHR.send(verifyData);
                });
            } else {
                var data = "idHex=" + encodeURIComponent(idHex);
                if (action === 'insert_data.php') {
                    data += "&bookName=" + encodeURIComponent(bookName) + "&ime=" + encodeURIComponent(ime) + "&priimek=" + encodeURIComponent(priimek);
                }
                xhr.send(data);
            }
        }

        function fetchBookName(idHex) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_book_name.php?idHex=" + encodeURIComponent(idHex), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    document.getElementById("bookName").value = response.bookName;
                    document.getElementById("ime").value = response.ime;
                    document.getElementById("priimek").value = response.priimek;
                }
            };
            xhr.send();
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("idHex").addEventListener("input", function() {
                var idHex = this.value;
                if (idHex) {
                    fetchBookName(idHex);
                }
            });
        });
    </script>
</head>


<body class="bg-light">

<?php include 'sidebar.php'; ?>
<div class="content">
   

    

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Uvoz podatkov iz Excela</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" enctype="multipart/form-data" action="import.php">
                        <div class="form-group">
                            <label for="import_file">Izberite Excel datoteko:</label>
                            <input type="file" name="import_file" id="import_file" class="form-control-file" required>
                        </div>
                        <button type="submit" name="import" class="btn btn-primary">Uvoz podatkov</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <h1 class="mb-4">Vstavljanje in Brisanje podatkov</h1>
        <form>
            <div class="form-group">
                <label for="idHex">RFID koda:</label>
                <input type="text" class="form-control" id="idHex" name="idHex" list="idHexList" required>
                <datalist id="idHexList">
                    <?php
                    $servername = "127.0.0.1";
                    $username = "root";
                    $password = "Spica2024!";
                    $dbname = "rfid";

                    // Ustvarimo povezavo
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Preverimo povezavo
                    if ($conn->connect_error) {
                        die("Povezava ni uspela: " . $conn->connect_error);
                    }
                    // Pripravimo SQL stavek za pridobitev idHex, ki niso uporabljeni v tabeli users
                    $sql = "SELECT DISTINCT idHex FROM test WHERE idHex NOT IN (SELECT rfid_tag FROM users WHERE rfid_tag IS NOT NULL)";
                    $result = $conn->query($sql);

                    // Izpišemo možnosti v padajočem meniju
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row["idHex"] . '">' . $row["idHex"] . '</option>';
                        }
                    }

                    $conn->close();

                    
                    ?>
                </datalist>


            </div>
            <div class="form-group">
                <label for="bookName">NASLOV KNJIGE:</label>
                <input type="text" class="form-control" id="bookName" name="bookName" required>
            </div>
            <div class="form-group">
                <label for="ime">IME:</label>
                <input type="text" class="form-control" id="ime" name="ime" required>
            </div>
            <div class="form-group">
                <label for="priimek">PRIIMEK:</label>
                <input type="text" class="form-control" id="priimek" name="priimek" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="submitForm('insert_data.php')">Shrani</button>
            <button type="button" class="btn btn-danger" onclick="submitForm('delete_data.php')">Izbriši</button>
        </form>

        <!-- Sporočila o uspehu ali napaki -->
        <div id="message" class="message mt-3"></div>
    </div>

    <footer>
        <div class="container">
             
     </div>
    </footer>
</div>
    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Potrditev brisanja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="passwordForm">
                        <div class="form-group">
                            <label for="username">Uporabniško ime:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Geslo:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Prekliči</button>
                    <button type="button" class="btn btn-danger" id="deleteConfirmButton">Potrdi brisanje</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
