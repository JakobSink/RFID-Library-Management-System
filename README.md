 RFID Library Management System

 Opis projekta

RFID Library Management System je spletna aplikacija, namenjena knjižnicam za učinkovito upravljanje knjig, rezervacij in prisotnosti uporabnikov s pomočjo RFID tehnologije. Sistem omogoča uporabnikom knjižnice hitro in enostavno rezervacijo knjig, hkrati pa administratorjem omogoča nadzor in urejanje uporabnikov ter podatkov o knjigah.

 Tehnologije

Projekt uporablja različne tehnologije za zajem, obdelavo, preverjanje in prikaz podatkov:

- Python: Komunicira z RFID bralnikom, bere podatke in jih ustrezno preverja za podvojene vnose. Če so zapisi pravilni, se shranijo v bazo podatkov.
- JavaScript: Dinamično ureja podatke na odjemalski strani in omogoča asinkrono osveževanje vsebin brez ponovnega nalaganja strani.
- PHP: Povezuje aplikacijo z bazo podatkov in omogoča izvajanje CRUD operacij (vstavljanje, brisanje, urejanje in branje podatkov).
- HTML, CSS, Bootstrap: Zgrajen uporabniški vmesnik je odziven in omogoča prijetno uporabniško izkušnjo.

 Struktura datotek

 Backend (PHP skripti)

- `adminus.php`: Upravlja administracijske funkcije za knjižnico.
- `composer.json`, `composer.lock`: Upravljanje odvisnosti za PHP skripte.
- `delete_data.php`: Skript za brisanje podatkov iz baze.
- `export.php`: Omogoča izvoz podatkov iz sistema.
- `fetch_data.php`, `fetch2.php`: Pridobivanje podatkov iz baze.
- `get_attendance.php`: Pridobi podatke o prisotnosti uporabnikov.
- `get_book_name.php`: Pridobi podrobnosti o knjigah.
- `get_reservation_details.php`, `get_reservations.php`: Pridobi podatke o rezervacijah.
- `import.php`: Omogoča uvoz podatkov v sistem.
- `insert_data.php`, `insert_user.php`: Vstavljanje novih podatkov in ustvarjanje uporabnikov.
- `login.php`, `logout.php`: Prijava in odjava uporabnikov.
- `portal.php`: Glavni portal za uporabnike knjižnice, kjer lahko upravljajo svoje rezervacije in pregledujejo knjige.
- `rezervirajme.php`: Omogoča rezervacijo knjig.
- `update_event.php`: Posodablja dogodke in vnose v sistemu.
- `ustvariracun.php`: Skript za ustvarjanje novih uporabniških računov.
- `verify_password.php`: Preverja uporabniška gesla.
- `vstavljanje.php`: Vstavljanje podatkov v sistem.
- `info.php`, `index.php`, `test.php`: Različni testni in informativni skripti za delovanje spletne aplikacije.

 Frontend

- `style.css`: Glavna CSS datoteka za oblikovanje spletne strani.
- Slike: Različne slike, ki so vključene v oblikovanje:
  - `spica.png`, `spica-250x160.png`, `spica-250x160-remo.png`: Logotipi in slike za prikaz na spletnem mestu.
  - `download.png`, `download-removebg-preview (1).png`: Pomožne slike za spletno stran.

 Dodatni viri

- `RFID_podatki.xlsx`: Excel datoteka, ki vsebuje podatke za analizo prisotnosti ali knjižničnih transakcij.

 Namestitev in uporaba

1. Kloniranje repozitorija:
   ```bash
   git clone https://github.com/yourusername/RFID-Library-Management-System.git
   ```

2. Namestitev PHP odvisnosti (z uporabo Composerja):
   ```bash
   composer install
   ```

3. Nastavitve baze podatkov: Poskrbite, da nastavite pravilne podatke za povezavo z bazo v ustreznih PHP datotekah (kot so `insert_data.php`, `get_reservations.php`, ipd.).

4. Zagon aplikacije: PHP datoteke naložite na svoj strežnik ali uporabite lokalno okolje, kot je XAMPP ali MAMP, da preizkusite aplikacijo.

 Funkcionalnosti sistema

- Upravljanje uporabnikov: Ustvarjanje, prijava, odjava in preverjanje uporabnikov.
- Rezervacije knjig: Uporabniki lahko brskajo po knjigah in jih rezervirajo preko portala.
- Upravljanje dogodkov: Administrator lahko upravlja dogodke in prisotnost s pomočjo RFID tehnologije.
- Povezava z bazo podatkov: Sistem uporablja MySQL (ali drug SQL strežnik), kamor se shranjujejo vsi podatki o knjigah, uporabnikih in rezervacijah.
