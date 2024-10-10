<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Data</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }

        body {
            background-color: #f8f9fa;
        }

        th {
            background-color: #f2f2f2;
            cursor: pointer; /* Add cursor pointer to indicate sortable columns */
        }

        .table-danger td {
            background-color: #ffcccc;
        }

       

        footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
        }

        .mx-auto {
            padding-top: 1%;
        }
        .search-box {
            margin-bottom: 10px;
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
                flex: 0;
                text-align: center;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                margin-bottom: 5px;
                width: 100%;
            }

            /* Hide RFID column on small screens */
            .rfid-column {
                display: none;
            }

            /* Hide Author column on small screens */
            .author-column {
                display: none;
            }

            .btn-danger {
                width: 100%;
            }

            .btn-primary {
                width: 100%;
            }
        }
    </style>
    <script>
        var allData = []; // Globalna spremenljivka za shranjevanje vseh podatkov
        var currentFilter = 'all'; // Trenutni filter za lokacijo
        var sortDirection = {}; // Objekt za sledenje smeri razvrščanja za vsak stolpec

        // Inicializacija smeri razvrščanja za vsak stolpec
        sortDirection['0'] = 'none'; // Stolpec RFID
        sortDirection['1'] = 'none'; // Stolpec NASLOV KNJIGE
        sortDirection['2'] = 'none'; // Stolpec Avtor (Priimek)
        sortDirection['3'] = 'none'; // Stolpec Zadnjič Videno
        sortDirection['4'] = 'desc'; // Stolpec ČAS ZADNJEGA PREMIKA (novejši datum zgoraj)

        // Funkcija za pridobivanje podatkov
        function fetchData() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_data.php", true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    allData = JSON.parse(xhr.responseText); // Shranimo vse podatke globalno
                    applyFiltersAndSort(); // Uporabimo trenutne filtre in razvrščanje
                }
            };
            xhr.send();
        }

        // Osvežimo podatke ob prvotnem nalaganju strani
        fetchData();

        // Funkcija za prikaz tabele podatkov
        function renderTable(data) {
            var table = document.getElementById("data-table");
            table.innerHTML = "<thead class='thead-light'><tr>" +
                "<th class='d-none d-md-table-cell rfid-column' onclick='sortTable(0)'>RFID <span id='rfid-sort-icon'></span></th>" +
                "<th onclick='sortTable(1)'>NASLOV KNJIGE <span id='book-sort-icon'></span></th>" +
                "<th class='author-column' onclick='sortTable(2)'>AVTOR <span id='name-sort-icon'></span></th>" +
                "<th onclick='sortTable(3)'>ZADNJIČ VIDENO <span id='location-sort-icon'></span></th>" +
                "<th onclick='sortTable(4)'>ČAS ZADNJEGA PREMIKA <span id='timestamp-sort-icon'></span></th>" +
                "</tr></thead><tbody>";

            for (var i = 0; i < data.length; i++) {
                var row = "<tr";
                // Preverimo čas zadnjega posodobitve
                var lastUpdate = new Date(data[i].timestamp);
                var currentTime = new Date();
                var diffInMinutes = (currentTime - lastUpdate) / (1000 * 60);

                // Poudarimo vrstico v rdeči barvi, če je čas zadnje posodobitve več kot 30 minut nazaj
                if (diffInMinutes > 525600) {
                    row += ' class="table-danger"';
                }
                row += ">" +
                    "<td class='d-none d-md-table-cell rfid-column'>" + data[i].idHex + "</td>" +
                    "<td>" + data[i].bookName + "</td>" +
                    "<td class='author-column'>" + data[i].ime_in_priimek + "</td>" +
                    "<td>" + data[i].location + "</td>" +
                    "<td>" + formatDate(lastUpdate) + "</td>" + // Prikaz oblikovanega datuma in časa
                    "</tr>";
                table.innerHTML += row;
            }
            table.innerHTML += "</tbody>";
        }

        // Funkcija za oblikovanje datuma in časa
        function formatDate(date) {
            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();
            var hours = date.getHours();
            var minutes = date.getMinutes();

            // Dodamo vodilne ničle za enomestne številke
            if (day < 10) {
                day = '0' + day;
            }
            if (month < 10) {
                month = '0' + month;
            }
            if (hours < 10) {
                hours = '0' + hours;
            }
            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            var formattedDate = day + '.' + month + '.' + year + ' ' + hours + ':' + minutes;
            return formattedDate;
        }

        // Funkcija za uporabo filtrov in razvrščanja
        function applyFiltersAndSort() {
            var filteredData = allData;

            // Filtriranje po lokaciji
            if (currentFilter !== 'all' && currentFilter !== 'redRows') {
                filteredData = filteredData.filter(function (item) {
                    return item.location === currentFilter;
                });
            }

            // Filtriranje po redRows (poudarjene vrstice)
            if (currentFilter === 'redRows') {
                filteredData = filteredData.filter(function (item) {
                    var lastUpdate = new Date(item.timestamp);
                    var currentTime = new Date();
                    var diffInMinutes = (currentTime - lastUpdate) / (1000 * 60);
                    return diffInMinutes > 525600;
                });
            }

            // Razvrščanje filtriranih podatkov
            filteredData = sortData(filteredData);

            renderTable(filteredData); // Prikaz filtriranih in razvrščenih podatkov

            // Posodobimo prikaz trikotnikov za smer razvrščanja
            updateSortIcons();
        }

        // Funkcija za razvrščanje podatkov glede na trenutno smer razvrščanja
        function sortData(data) {
            var columnIndex = Object.keys(sortDirection).find(function (key) {
                return sortDirection[key] !== 'none';
            });

            if (columnIndex !== undefined) {
                var direction = sortDirection[columnIndex];
                data.sort(function (a, b) {
                    var x = a[getColumnProperty(parseInt(columnIndex))];
                    var y = b[getColumnProperty(parseInt(columnIndex))];

                    // Special handling for date column
                    if (columnIndex == '3') {
                        x = new Date(x);
                        y = new Date(y);
                    }

                    if (direction === 'asc') {
                        return x < y ? -1 : x > y ? 1 : 0;
                    } else {
                        return x > y ? -1 : x < y ? 1 : 0;
                    }
                });
            }

            return data;
        }

        // Funkcija za pridobitev lastnosti stolpca glede na indeks
        function getColumnProperty(index) {
            switch (index) {
                case 0:
                    return 'idHex'; // Lastnost stolpca RFID
                case 1:
                    return 'bookName'; // Lastnost stolpca NASLOV KNJIGE
                case 2:
                    return 'ime_in_priimek'; // Lastnost stolpca AVTOR (Priimek)
                case 3:
                    return 'location'; // Lastnost stolpca ZADNJIČ VIDENO
                case 4:
                    return 'timestamp'; // Lastnost stolpca ČAS ZADNJEGA PREMIKA
                default:
                    return '';
            }
        }

        // Funkcija za razvrščanje tabele glede na izbrani stolpec
        function sortTable(columnIndex) {
            switch (sortDirection[columnIndex.toString()]) {
                case 'asc':
                    sortDirection[columnIndex.toString()] = 'desc';
                    break;
                case 'desc':
                    sortDirection[columnIndex.toString()] = 'asc';
                    break;
                default:
                    sortDirection[columnIndex.toString()] = 'asc'; // Privzeto na naraščajoče
                    break;
            }

            // Izklopimo razvrščanje na prejšnjem stolpcu
            Object.keys(sortDirection).forEach(function (key) {
                if (key !== columnIndex.toString()) {
                    sortDirection[key] = 'none';
                }
            });

            // Prikaz filtriranih in razvrščenih podatkov
            applyFiltersAndSort();
        }

        // Funkcija za posodobitev prikaza trikotnikov za smer razvrščanja
        function updateSortIcons() {
            // Gremo čez vse stolpce in posodobimo prikaz trikotnikov
            Object.keys(sortDirection).forEach(function (key) {
                var iconElement = document.getElementById(getSortIconId(parseInt(key)));
                if (iconElement) {
                    // Nastavimo ustrezno ikono glede na smer razvrščanja
                    if (sortDirection[key] === 'asc') {
                        iconElement.innerHTML = '&#9650;'; // Puščica gor
                    } else if (sortDirection[key] === 'desc') {
                        iconElement.innerHTML = '&#9660;'; // Puščica dol
                    } else {
                        iconElement.innerHTML = ''; // Brez ikone
                    }
                }
            });
        }

        // Funkcija za pridobitev ID elementa za ikono smeri razvrščanja
        function getSortIconId(columnIndex) {
            switch (columnIndex) {
                case 0:
                    return 'rfid-sort-icon'; // ID ikone za RFID stolpec
                case 1:
                    return 'book-sort-icon'; // ID ikone za NASLOV KNJIGE stolpec
                case 2:
                    return 'name-sort-icon'; // ID ikone za AVTOR (Priimek) stolpec
                case 3:
                    return 'location-sort-icon'; // ID ikone za ZADNJIČ VIDENO stolpec
                case 4:
                    return 'timestamp-sort-icon'; // ID ikone za ČAS ZADNJEGA PREMIKA stolpec
                default:
                    return '';
            }
        }

        // Filtriranje po lokaciji
        function filterLocation(location) {
            currentFilter = location;
            applyFiltersAndSort(); // Uporabimo filter
        }

        // Omogočimo filter redRows
        function filterRedRows() {
            currentFilter = 'redRows';
            applyFiltersAndSort(); // Uporabimo filter
        }

        // Ponastavitev vseh filtrov
        function resetFilters() {
            currentFilter = 'all'; // Ponastavimo filter
            Object.keys(sortDirection).forEach(function (key) {
                sortDirection[key] = 'none'; // Ponastavimo smer razvrščanja na 'none'
            });
            sortDirection['4'] = 'desc'; // Default sorting by timestamp in descending order
            applyFiltersAndSort(); // Uporabimo filter
        }
        // JavaScript funkcija za iskanje po vseh stolpcih tabele
        function searchTable() {
            var input = document.getElementById('search-bar').value.toLowerCase();
            var table = document.getElementById('data-table');
            var rows = table.getElementsByTagName('tr');

            // Iteriramo skozi vse vrstice tabele
            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var found = false;
                // Iteriramo skozi vse celice (stolpce) v trenutni vrstici
                for (var j = 0; j < cells.length; j++) {
                    var cellText = cells[j].innerText.toLowerCase() || cells[j].textContent.toLowerCase();
                    if (cellText.indexOf(input) > -1) {
                        found = true;
                        break;
                    }
                }
                // Prikažemo ali skrijemo vrstico glede na to, ali je bila najdena ujemanja
                if (found) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
        function searchTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("data-table");
        tr = table.getElementsByTagName("tr");

        // Start looping from index 1 to skip the header row (index 0)
        for (i = 1; i < tr.length; i++) {
            var found = false;
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break; // Exit the inner loop early if found
                }
            }
            if (found) {
                tr[i].style.display = ""; // Show the row
            } else {
                tr[i].style.display = "none"; // Hide the row
            }
        }
    }


        // Osvežimo podatke vsako sekundo
        setInterval(fetchData, 10000);
    </script>
</head>

<body>
<?php include 'sidebar.php'; ?>
    <!-- Top navbar -->
    <div class="content">
    <nav class="navbar sticky-top navbar-light bg-light">
        <div class="container-fluid d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mx-auto mb-2 mb-md-0">   
                <div class="btn-group mb-2 mb-md-0" role="group">
                    <button type="button" id="all-bottom" class="btn btn-secondary mb-2 mb-md-0" onclick="resetFilters()">Vse</button>
                    <button type="button" id="inLibrary-bottom" class="btn btn-secondary mb-2 mb-md-0" onclick="filterLocation('V knjižnjici')">V knjižnici</button>
                    <button type="button" id="outside-bottom" class="btn btn-secondary mb-2 mb-md-0" onclick="filterLocation('Pri vhodu/izhodu')">Pri vhodu/izhodu</button>
                </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="search-box">
            <input type="text" id="searchInput" class="form-control" onkeyup="searchTable()" placeholder="Iskanje...">
        </div>
        <div class="table-responsive">
            <table id="data-table" class="table table-bordered table-striped">
            <thead class="thead-light">
                <!-- Table content will be generated dynamically -->
            </table>
        </div>
    </div>
    </br>
    </br>


    <footer>
        <div class="container">
                    </div>
    </footer>
    </div>
    <!-- JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>