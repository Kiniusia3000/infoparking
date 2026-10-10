<?php
define("MAX_PER_IP", 3); // ilosc prob na godzine z jednego adresu ip
define("MAX_PER_CARD", 10); // proby na jedną karte

$conn = mysqli_connect("localhost", "root", "", "infoparking");
$message = "";
$ip = mysqli_real_escape_string($conn, $_SERVER["REMOTE_ADDR"]);

$token = "";
if (isset($_GET["token"])) {
 $token = mysqli_real_escape_string($conn, $_GET["token"]);
}

$result = mysqli_query($conn, "SELECT id, imie, telefon, active FROM wizytowki WHERE token='$token'");
$card = mysqli_fetch_assoc($result);

if ($card && $card["active"] == 1 && isset($_POST["action"])) {
 $cardId = $card["id"];

 // liczenie prob
 $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_log WHERE card_id=$cardId AND ip='$ip' AND created_at > NOW() - INTERVAL 1 HOUR");
 $row = mysqli_fetch_assoc($result);
 $perIp = $row["total"];

 $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_log WHERE card_id=$cardId AND created_at > NOW() - INTERVAL 1 HOUR");
 $row = mysqli_fetch_assoc($result);
 $perCard = $row["total"];

 if ($perIp >= MAX_PER_IP || $perCard >= MAX_PER_CARD) {
 $message = "Przekroczono limit prób. Spróbuj ponownie za godzinę.";
 } elseif ($_POST["action"] == "call") {
 mysqli_query($conn, "INSERT INTO contact_log (card_id, ip) VALUES ($cardId, '$ip')");

 // phone dialer
 $phone = preg_replace("/[^0-9+]/", "", $card["telefon"]);
 mysqli_close($conn);
 header("Location: tel:" . $phone);
 exit();
 }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>INFOPARKING - Kontakt z kierowcą pojazdu</title>
 <link rel="icon" type="image/svg+xml" href="car.svg?v=2">
 <link rel="stylesheet" href="style_index.css">
</head>
<body>
 <h1>INFOPARKING</h1>
 <main class="contact-page">
 <div class="contact-card">
 <?php if (!$card) { ?>
 <h1>Nie znaleziono wizytówki.</h1>
 <?php } elseif ($card["active"] == 0) { ?>
 <h1>Ta wizytówka jest wyłączona.</h1>
 <?php } else { ?>
 <h1>Kontakt z kierowcą pojazdu</h1>
 <p>Kierowca: <?php echo htmlspecialchars($card["imie"]); ?></p>
 <p><?php echo htmlspecialchars($message); ?></p>

 <form method="post" class="contact-form">
 <input type="hidden" name="action" value="call">
 <button type="submit">Zadzwoń</button>
 </form>
 <?php } ?>
 <a href="https://github.com/Kiniusia3000" class="github-link">GitHub</a>
 </div>
 </main>
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
</body>
</html>