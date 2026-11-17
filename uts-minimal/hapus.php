<?php
// hapus.php -> hapus data (konfirmasi sudah di index.php lewat confirm())
include "cek_login.php";
include "koneksi.php";

$id = (int) $_GET["id"];

// WAJIB pakai WHERE, kalau tidak SEMUA data terhapus
if ($conn->query("DELETE FROM mahasiswa WHERE id = $id") === TRUE) {
    echo "<script>alert('Data berhasil dihapus'); window.location='index.php';</script>";
} else {
    echo "Gagal: " . $conn->error;
}
?>
