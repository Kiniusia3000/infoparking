<?php
$conn = mysqli_connect("localhost", "root", "", "infoparking");
$message = "";

if (isset($_POST["login"])) {
 $login = mysqli_real_escape_string($conn, $_POST["login"]);
 $password = $_POST["password"];
 $password2 = $_POST["password2"];

 // sprawdzanie dlugosci loginu i hasla
 if (strlen($login) < 3) {
 $message = "Login jest za krótki.";
 } elseif (strlen($password) < 8) {
 $message = "Hasło jest za krótkie.";
 } elseif ($password != $password2) {
 $message = "Hasła nie są takie same.";
 } else {
 // sprawdzanie czy login juz istnieje
 $result = mysqli_query($conn, "SELECT id FROM uzytkownicy WHERE login = '$login'");

 if (mysqli_num_rows($result) > 0) {
 $message = "Ten login jest już zajęty.";
 } else {
 $hash = password_hash($password, PASSWORD_DEFAULT);
 mysqli_query($conn, "INSERT INTO uzytkownicy (login, haslo) VALUES ('$login', '$hash')");
 $message = "Konto zostało utworzone.";
 }
 }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
 <meta charset="UTF-8">
 <title>INFOPARKING - Rejestracja</title>
 <link rel="icon" type="image/svg+xml" href="car.svg?v=2">
 <link rel="stylesheet" href="style_login.css">
</head>
<body>
 <div class="style_around">
 <form method="post">
 <h1>INFOPARKING - Rejestracja</h1>
 <p><?php echo htmlspecialchars($message); ?></p>

 <label>Login</label>
 <input type="text" name="login">

 <label>Hasło</label>
 <input type="password" name="password">

 <label>Powtórz hasło</label>
 <input type="password" name="password2">

 <button type="submit" class="button_style">Załóż konto</button>
 <p>Masz konto? <a href="login.php" class="register-link">Zaloguj się</a></p>
 </form>
 <a href="https://github.com/Kiniusia3000" class="github-link">GitHub</a>
 </div>
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
 </div>
</body>
</html>