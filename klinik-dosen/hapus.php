<?php
// =====================================================================
// hapus.php  ->  DELETE (menghapus data)
// Pola sama seperti contoh dosen (SBD2):
//   1. GET  hapus.php?id=3  -> tampilkan data + pertanyaan "Anda yakin?"
//   2. POST (klik tombol Hapus) -> DELETE dari database + hapus file upload
//
// Kenapa tidak langsung hapus saat link diklik (GET)?
//   Karena link GET bisa terpanggil tanpa sengaja (dibuka ulang, di-prefetch
//   browser, dll). Aksi yang MENGUBAH data sebaiknya pakai POST.
// =====================================================================
include "koneksi.php";
include "fungsi.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$sql    = "SELECT p.*, po.nama_poli FROM pasien p JOIN poli po ON p.poli_id = po.id WHERE p.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location = 'index.php';</script>";
    exit();
}
$data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sqldelete = "DELETE FROM pasien WHERE id = $id";

    if ($conn->query($sqldelete) === TRUE) {
        hapusFile($data["bukti_bayar"]); // file bukti bayar ikut dihapus
        echo "<script>alert('Data pasien berhasil dihapus'); window.location = 'index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data'); window.location = 'index.php';</script>";
    }
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hapus Data Pasien - Klinik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <span class="logo">Klinik Sehat</span>
        <a href="index.php">Data Pasien</a>
        <a href="tambah.php">+ Registrasi</a>
        <a href="poli.php">Data Poli</a>
        <a href="latihan.php">Latihan</a>
    </nav>

    <div class="container">
        <h1>Hapus Data</h1>
        <div class="alert alert-error">Anda yakin menghapus data ini? Data yang dihapus tidak bisa dikembalikan.</div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>">
            <table class="form-table">
                <tr><td>No. Registrasi</td><td>:</td><td><?php echo $data["no_registrasi"]; ?></td></tr>
                <tr><td>Nama</td><td>:</td><td><?php echo htmlspecialchars($data["nama"]); ?></td></tr>
                <tr><td>Umur</td><td>:</td><td><?php echo $data["umur"]; ?> tahun</td></tr>
                <tr><td>Jenis Kelamin</td><td>:</td><td><?php echo labelJk($data["jenis_kelamin"]); ?></td></tr>
                <tr><td>Poli</td><td>:</td><td><?php echo htmlspecialchars($data["nama_poli"]); ?></td></tr>
                <tr><td>Status</td><td>:</td><td><?php echo labelStatus($data["status"]); ?></td></tr>
            </table>
            <button type="submit" class="btn-merah">Ya, Hapus</button>
            <a href="index.php" class="btn btn-abu">Batal</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
