<?php
// cek_login.php -> "satpam" halaman.
// Taruh  include "cek_login.php";  di BARIS PALING ATAS index/tambah/edit/hapus.
// Kalau belum login, user dilempar balik ke login.php.
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
?>
