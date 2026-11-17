<?php
// koneksi.php -> dipanggil di setiap halaman dengan: include "koneksi.php";
mysqli_report(MYSQLI_REPORT_OFF); // supaya if ($conn->query(...) === TRUE) ... else ... bisa jalan

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_uts";      // GANTI sesuai nama database

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
