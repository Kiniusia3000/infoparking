<?php
session_start();
if (!isset($_SESSION["id"])) {
 header("Location: login.php");
 exit();
}

$conn = mysqli_connect("localhost", "root", "", "infoparking");
$userId = $_SESSION["id"];
$message = "";

// usuwanie
if (isset($_GET["delete"])) {
 $cardId = (int)$_GET["delete"];
 mysqli_query($conn, "DELETE FROM wizytowki WHERE id=$cardId AND uzytkownik_id=$userId");
 header("Location: main.php");
 exit();
}

// wlaczanie i wylaczanie karty
if (isset($_GET["toggle"])) {
 $cardId = (int)$_GET["toggle"];
 mysqli_query($conn, "UPDATE wizytowki SET active = 1 - active WHERE id=$cardId AND uzytkownik_id=$userId");
 header("Location: main.php");
 exit();
}

// dodawanie i edytowanie
if (isset($_POST["name"])) {
 $cardId = (int)$_POST["id"];
 $name = mysqli_real_escape_string($conn, trim($_POST["name"]));
 $phone = mysqli_real_escape_string($conn, trim($_POST["phone"]));

 if ($name == "" || $phone == "") {
 $message = "Wypełnij wszystkie pola.";
 } elseif ($cardId == 0) {
 $token = bin2hex(random_bytes(16));
 mysqli_query($conn, "INSERT INTO wizytowki (uzytkownik_id, imie, telefon, token) VALUES ($userId, '$name', '$phone', '$token')");
 $message = "Dodano wizytówkę.";
 } else {
 mysqli_query($conn, "UPDATE wizytowki SET imie='$name', telefon='$phone' WHERE id=$cardId AND uzytkownik_id=$userId");
 $message = "Zapisano zmiany.";
 }
}

// dane karty
$card = ["id" => 0, "imie" => "", "telefon" => ""];
if (isset($_GET["edit"])) {
 $cardId = (int)$_GET["edit"];
 $result = mysqli_query($conn, "SELECT * FROM wizytowki WHERE id=$cardId AND uzytkownik_id=$userId");
 if (mysqli_num_rows($result) > 0) {
 $card = mysqli_fetch_assoc($result);
 }
}

$cards = mysqli_query($conn, "SELECT * FROM wizytowki WHERE uzytkownik_id=$userId");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
 <meta charset="UTF-8">
 <title>INFOPARKING - Panel</title>
	<link rel="icon" type="image/svg+xml" href="car.svg?v=2">
 <link rel="stylesheet" href="style_index.css">
 <script>
 function printCard(id) {
 var printWindow = window.open("picture.php?id=" + id);
 printWindow.onload = function() {
 printWindow.print();
 };
 }

 document.addEventListener('DOMContentLoaded', function () {
 var toggleBtn = document.getElementById('toggleFormBtn');
 var formPanel = document.getElementById('cardFormPanel');

 if (toggleBtn && formPanel) {
 toggleBtn.addEventListener('click', function () {
 var isOpen = formPanel.classList.toggle('open');
 toggleBtn.textContent = isOpen ? 'Schowaj' : 'Dodaj nową';
 toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
 });
 }
 });
 </script>
</head>
<body>
 <header>
 <h1>INFOPARKING - Panel</h1>
 <a href="logout.php" class="logout-link">Wyloguj</a>
 </header>

 <main>
 <button type="button" id="toggleFormBtn" class="toggle-form-btn">Dodaj nową</button>
 <p><?php echo $message; ?></p>

 <div id="cardFormPanel" class="form-panel">
 <form method="post">
 <input type="hidden" name="id" value="<?php echo $card["id"]; ?>">

 <label>Imię:
 <span class="hint" tabindex="0">ⓘ
 <span class="tip">Ze względów bezpieczeństwa radzimy nie podawać swojego nazwiska.</span>
 </span>

 <input type="text" name="name" value="<?php echo $card["imie"]; ?>">
 </label>

 <label>Telefon:
 <input type="text" name="phone" value="<?php echo $card["telefon"]; ?>">
 </label>
 <button type="submit">Zapisz</button>
 </form>
 </div>

 <h2>Twoje wizytówki</h2>
 <?php
 while ($row = mysqli_fetch_assoc($cards)) {
 $cardId = $row["id"];
 echo "<div class='card'>";
 echo "<img src='picture.php?id=$cardId' width='250'>";
 echo "<div class='card-content'>";
 echo "<p>" . $row["imie"] . " - " . $row["telefon"] . "</p>";

 if ($row["active"] == 1) {
 echo "<p>Status: włączona</p>";
 } else {
 echo "<p>Status: wyłączona</p>";
 }

 echo "<div class='card-actions'>";
 echo "<a href='picture.php?id=$cardId&download=1'>Pobierz JPG</a> ";
 echo "<button type='button' onclick='printCard($cardId)'>Drukuj</button> ";
 echo "<a href='contact.php?token=" . $row["token"] . "' target='_blank'>Strona kontaktowa</a> ";
 echo "<a href='main.php?toggle=$cardId'>Włącz / wyłącz</a> ";
 echo "<a href='main.php?edit=$cardId'>Edytuj</a> ";
 echo "<a href='main.php?delete=$cardId'>Usuń</a>";
 echo "</div>";

 echo "</div>";
 echo "</div>";
 }
 ?>
 <!--faq-->
 <h3>FAQ</h3>
<div class="faq">
 <span>Jak działa ta strona?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Zakładasz konto, dodajesz wizytówkę z imieniem i numerem telefonu, a strona generuje obrazek JPG z napisem "Kontakt z kierowcą pojazdu" i kodem QR. Drukujesz go i kładziesz za szybą samochodu. Gdy ktoś zeskanuje kod, otwiera się strona z przyciskiem "Zadzwoń".</p>
 </div>
</div>

<div class="faq">
 <span>Co jest w kodzie QR?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Tylko adres strony kontaktowej z długim, losowym kodem. Nie ma w nim twojego imienia ani numeru telefonu, więc samo zdjęcie karteczki niczego nie ujawnia.</p>
 </div>
</div>

<div class="faq">
 <span>Czy mój numer telefonu jest widoczny dla wszystkich?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Nie jest zapisany w kodzie strony kontaktowej ani w kodzie QR. Numer pojawia się dopiero po kliknięciu "Zadzwoń", gdy telefon przechodzi do wybierania numeru. Wtedy osoba dzwoniąca go widzi, a on zostaje w jej historii połączeń. Jeśli ci to przeszkadza, podaj numer, którego możesz używać publicznie.</p>
 </div>
</div>

<div class="faq">
 <span>Jak zabezpieczone są moje dane?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Hasło jest przechowywane w bazie w postaci zaszyfrowanej (hash), więc nikt, nawet administrator, nie może go odczytać. Dane z formularzy są filtrowane przed zapisem do bazy, żeby nikt nie wstrzyknął złośliwego kodu. Panel jest dostępny tylko po zalogowaniu i każdy widzi oraz edytuje wyłącznie swoje wizytówki.</p>
 </div>
</div>

<div class="faq">
 <span>Czy ktoś może zgadnąć adres mojej strony kontaktowej?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Praktycznie nie. Adres zawiera losowy 32-znakowy kod, którego nie da się odgadnąć ani przeglądać po kolei, jak zwykłe numery 1, 2, 3.</p>
 </div>
</div>

<div class="faq">
 <span>Co chroni przed nadużyciami?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Każde kliknięcie "Zadzwoń" jest zapisywane, a liczba prób jest ograniczona: 3 na godzinę z jednego adresu IP i 10 na godzinę dla jednej wizytówki. To utrudnia masowe zbieranie numerów i spamowanie.</p>
 </div>
</div>

<div class="faq">
 <span>Co zrobić, gdy zgubię karteczkę albo ktoś nadużywa wizytówki?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>W panelu kliknij "Włącz / wyłącz", a strona kontaktowa przestanie działać od razu. Możesz też usunąć wizytówkę całkiem. Wydrukowany kod stanie się wtedy bezużyteczny.</p>
 </div>
</div>

<div class="faq">
 <span>Czy muszę podawać nazwisko?</span>
 <button onclick="this.parentElement.classList.toggle('open')">▼</button>
 <div class="answer">
 <p>Nie, i radzimy tego nie robić. Wystarczy imię albo pseudonim. Na stronie kontaktowej widać tylko to, co wpiszesz w polu "Imię".</p>
 </div>
</div>
 <a href="https://github.com/Kiniusia3000" class="github-link">GitHub</a>
</main>
</body>
</html>
<?php mysqli_close($conn); ?>