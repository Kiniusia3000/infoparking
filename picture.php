<?php
session_start();
if (!isset($_SESSION["id"])) {
 exit();
}
require "phpqrcode/qrlib.php";

define("BASE_URL", "https://infoparking.freedev.app");
define("IMAGE_WIDTH", 600);
define("IMAGE_HEIGHT", 700);
define("FONT_FILE", "DejaVuSans.ttf");

// texts on the card - change them here
$title = "KONTAKT Z KIEROWCĄ POJAZDU";
$footerLine1 = "Github: https://github.com/Kiniusia3000";
$footerLine2 = "Wygenerowano na stronie: infoparking.freedev.app";

// draws text centered horizontally
function drawCenteredText($image, $text, $size, $y, $color) {
 $box = imagettfbbox($size, 0, FONT_FILE, $text);
 $textWidth = $box[2] - $box[0];
 $x = (int)((IMAGE_WIDTH - $textWidth) / 2);
 imagettftext($image, $size, 0, $x, $y, $color, FONT_FILE, $text);
}

$conn = mysqli_connect("localhost", "root", "", "infoparking");
$cardId = (int)$_GET["id"];
$userId = $_SESSION["id"];

$result = mysqli_query($conn, "SELECT * FROM wizytowki WHERE id=$cardId AND uzytkownik_id=$userId");
$card = mysqli_fetch_assoc($result);
if (!$card) {
 exit();
}

// the QR code contains only the address of the contact page
$qrText = BASE_URL . "/contact.php?token=" . $card["token"];
$qrFile = sys_get_temp_dir() . "/qr_$cardId.png";
QRcode::png($qrText, $qrFile, QR_ECLEVEL_M, 10, 2);

// image with white background
$picture = imagecreatetruecolor(IMAGE_WIDTH, IMAGE_HEIGHT);
$white = imagecolorallocate($picture, 255, 255, 255);
$black = imagecolorallocate($picture, 0, 0, 0);
imagefill($picture, 0, 0, $white);

// QR code in the middle
$qr = imagecreatefrompng($qrFile);
imagecopyresampled($picture, $qr, 100, 150, 0, 0, 400, 400, imagesx($qr), imagesy($qr));
unlink($qrFile);

// main title at the top
drawCenteredText($picture, $title, 24, 90, $black);

// two smaller captions at the bottom
drawCenteredText($picture, $footerLine1, 14, 640, $black);
drawCenteredText($picture, $footerLine2, 14, 670, $black);

// download or preview
if (isset($_GET["download"])) {
 header("Content-Disposition: attachment; filename=kontakt.jpg");
}
header("Content-Type: image/jpeg");
imagejpeg($picture);
imagedestroy($picture);
?>