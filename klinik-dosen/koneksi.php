<?php
// =====================================================================
// koneksi.php
// File ini berisi koneksi ke database MySQL.
// Di-include di setiap halaman dengan:  include "koneksi.php";
//
// Catatan: di contoh dosen (SBD2), koneksi ditulis ulang di SETIAP file.
// Di sini dipisah ke 1 file supaya kalau nama database berubah,
// cukup ganti di sini saja (prinsip DRY = Don't Repeat Yourself).
// =====================================================================

$servername = "localhost";
$username   = "root";
$password   = "";              // default XAMPP: password kosong
$dbname     = "klinik_dosen";  // GANTI sesuai nama database kamu

// Sejak PHP 8.1, mysqli otomatis "melempar exception" kalau query error,
// sehingga pola  if ($conn->query($sql) === TRUE) { ... } else { ... }
// tidak pernah masuk ke else (program langsung Fatal error).
// Baris ini mematikan mode exception supaya pola if/else ala dosen tetap jalan.
mysqli_report(MYSQLI_REPORT_OFF);

// Membuat koneksi (gaya OOP mysqli, sama seperti contoh dosen)
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi, kalau gagal hentikan program dengan die()
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Supaya huruf/karakter khusus (é, emoji, dll) tersimpan dengan benar
$conn->set_charset("utf8mb4");

// Zona waktu Indonesia (WIB). Tanpa ini, date() bisa memakai UTC
// sehingga "hari ini" bisa salah 7 jam.
date_default_timezone_set("Asia/Jakarta");
?>
