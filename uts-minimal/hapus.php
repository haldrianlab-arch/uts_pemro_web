<?php
// hapus.php -> hapus data + fotonya (konfirmasi sudah di index.php lewat confirm())
include "koneksi.php";

$id = (int) $_GET["id"];

// Ambil nama foto dulu sebelum datanya dihapus
$data = $conn->query("SELECT foto FROM mahasiswa WHERE id = $id")->fetch_assoc();

// WAJIB pakai WHERE, kalau tidak SEMUA data terhapus
if ($conn->query("DELETE FROM mahasiswa WHERE id = $id") === TRUE) {
    if ($data["foto"] != "" && file_exists("uploads/" . $data["foto"])) {
        unlink("uploads/" . $data["foto"]);
    }
    echo "<script>alert('Data berhasil dihapus'); window.location='index.php';</script>";
} else {
    echo "Gagal: " . $conn->error;
}
?>
