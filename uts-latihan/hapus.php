<?php
// =====================================================================
// hapus.php  ->  KISI-KISI (b4): HAPUS DATA (Delete)
// Pola dosen: GET tampilkan konfirmasi -> POST (klik tombol) -> DELETE
//
// VERSI CEPAT (kalau waktu ujian mepet), cukup 4 baris + link dengan confirm():
//   index.php : <a href="hapus.php?id=..." onclick="return confirm('Yakin?')">Hapus</a>
//   hapus.php : $id = (int) $_GET["id"];
//               $conn->query("DELETE FROM mahasiswa WHERE id = $id");
//               echo "<script>alert('Terhapus'); window.location='index.php';</script>";
// =====================================================================
include "cek_login.php"; // [SKIP-LOGIN] hapus baris ini kalau tanpa login
include "koneksi.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$result = $conn->query("SELECT * FROM mahasiswa WHERE id = $id");
if ($result->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index.php';</script>";
    exit;
}
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // JANGAN LUPA WHERE -> tanpa WHERE, SEMUA data terhapus!
    if ($conn->query("DELETE FROM mahasiswa WHERE id = $id") === TRUE) {
        echo "<script>alert('Data berhasil dihapus'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data'); window.location='index.php';</script>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hapus Data</title>
    <link rel="stylesheet" href="style.css"> <!-- [SKIP-CSS] boleh tidak ditulis kalau tanpa CSS -->
</head>
<body>
    <h1>Hapus Data</h1>
    <p class="error">Anda yakin menghapus data ini?</p>

    <table>
        <tr><td>NIM</td><td>:</td><td><?php echo htmlspecialchars($data["nim"]); ?></td></tr>
        <tr><td>Nama</td><td>:</td><td><?php echo htmlspecialchars($data["nama"]); ?></td></tr>
        <tr><td>Jurusan</td><td>:</td><td><?php echo htmlspecialchars($data["jurusan"]); ?></td></tr>
    </table>
    <br>
    <form method="post" action="">
        <button type="submit" class="btn-merah">Ya, Hapus</button>
        <a href="index.php" class="btn btn-abu">Batal</a>
    </form>
</body>
</html>
<?php $conn->close(); ?>
