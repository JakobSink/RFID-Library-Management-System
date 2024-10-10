<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustvari Račun</title>
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
            display: none; /* Initially hidden */
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
    <!-- Bootstrap JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body class="bg-light">
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="container py-4">
        <h1 class="mb-4">Ustvari Račun</h1>
        <form>
            <div class="form-group">
                <label for="ime">Ime:</label>
                <input type="text" class="form-control" id="ime" name="ime" required>
            </div>
            <div class="form-group">
                <label for="priimek">Priimek:</label>
                <input type="text" class="form-control" id="priimek" name="priimek" required>
            </div>
            <div class="form-group">
                <label for="username">Uporabniško ime:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Geslo:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="submitForm('insert_user.php')">Ustvari račun</button>
        </form>

        <!-- Message display area -->
        <div id="message" class="message mt-3"></div>
    </div>
    <footer>
        <div class="container">
            <!-- Footer content -->
        </div>
    </footer>
</div>
<script>
    function submitForm(action) {
        var ime = document.getElementById("ime").value;
        var priimek = document.getElementById("priimek").value;
        var username = document.getElementById("username").value;
        var password = document.getElementById("password").value;
        var email = document.getElementById("email").value;

        // Validate fields
        if (!ime || !priimek || !username || !password || !email) {
            var messageDiv = document.getElementById("message");
            messageDiv.className = "message alert error";
            messageDiv.innerHTML = "Prosim, izpolnite vsa polja.";
            messageDiv.style.display = "block";
            return;
        }

        // Email validation
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            var messageDiv = document.getElementById("message");
            messageDiv.className = "message alert error";
            messageDiv.innerHTML = "Prosim, vnesite veljaven email naslov.";
            messageDiv.style.display = "block";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", action, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                var messageDiv = document.getElementById("message");
                messageDiv.className = "message alert " + response.status;
                messageDiv.innerHTML = response.message;
                messageDiv.style.display = "block"; // Show message

                // Clear inputs after successful operation
                if (response.status === "success") {
                    document.getElementById("ime").value = "";
                    document.getElementById("priimek").value = "";
                    document.getElementById("username").value = "";
                    document.getElementById("password").value = "";
                    document.getElementById("email").value = "";
                }
            }
        };

        var data = "ime=" + encodeURIComponent(ime) + "&priimek=" + encodeURIComponent(priimek) + "&username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password) + "&email=" + encodeURIComponent(email);
        xhr.send(data);
    }
</script>
</body>
</html>
