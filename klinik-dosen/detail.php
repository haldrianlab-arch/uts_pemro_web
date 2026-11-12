<?php
// =====================================================================
// detail.php  ->  READ (1 data)
// Menampilkan semua kolom 1 pasien + gambar bukti bayar.
// =====================================================================
include "koneksi.php";
include "fungsi.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$sql = "SELECT p.*, po.nama_poli, po.tarif
        FROM pasien p
        JOIN poli po ON p.poli_id = po.id
        WHERE p.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location = 'index.php';</script>";
    exit();
}
$data = $result->fetch_assoc();
$conn->close();

// Contoh fungsi string (Pertemuan 5)
$nama_besar   = strtoupper($data["nama"]);              // huruf besar semua
$inisial      = $data["nama"][0];                       // akses karakter pertama seperti array
$jumlah_huruf = strlen(str_replace(" ", "", $data["nama"])); // panjang nama tanpa spasi
$gejala_array = explode(",", $data["gejala"]);          // string -> array

// Rincian biaya (pola Latihan 4 P4: hitung gaji karyawan)
$biaya = hitungBiaya($data["tarif"], $data["gejala"], $data["umur"]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pasien - Klinik</title>
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
        <div class="detail-header">
            <div class="avatar"><?php echo htmlspecialchars(strtoupper($inisial)); ?></div>
            <div>
                <h1><?php echo htmlspecialchars($nama_besar); ?></h1>
                <p><?php echo $data["no_registrasi"]; ?> &middot; <?php echo labelStatus($data["status"]); ?></p>
            </div>
        </div>

        <div class="detail-wrap">
            <table class="form-table detail">
                <tr><td>Nama</td><td>:</td><td><?php echo htmlspecialchars($data["nama"]); ?> <small>(<?php echo $jumlah_huruf; ?> huruf)</small></td></tr>
                <tr><td>Umur</td><td>:</td><td><?php echo $data["umur"]; ?> tahun (<?php echo kategoriUsia($data["umur"]); ?>)</td></tr>
                <tr><td>Email</td><td>:</td><td><?php echo htmlspecialchars($data["email"]); ?></td></tr>
                <tr><td>Nomor HP</td><td>:</td><td><?php echo htmlspecialchars($data["no_hp"]); ?></td></tr>
                <tr><td>PIN</td><td>:</td><td>****** <small>(tersimpan sebagai hash)</small></td></tr>
                <tr><td>Jenis Kelamin</td><td>:</td><td><?php echo labelJk($data["jenis_kelamin"]); ?></td></tr>
                <tr>
                    <td>Gejala</td><td>:</td>
                    <td>
                        <ul class="list-gejala">
                            <?php
                            // Perulangan FOR biasa untuk indexed array (Pertemuan 5)
                            for ($i = 0; $i < count($gejala_array); $i++) {
                                echo "<li>" . htmlspecialchars($gejala_array[$i]) . "</li>";
                            }
                            ?>
                        </ul>
                    </td>
                </tr>
                <tr><td>Poli Tujuan</td><td>:</td><td><?php echo htmlspecialchars($data["nama_poli"]); ?></td></tr>
                <tr>
                    <td>Rincian Biaya</td><td>:</td>
                    <td>
                        <table class="rincian">
                            <tr><td>Biaya pokok (poli)</td><td><?php echo formatRupiah($biaya["biaya_pokok"]); ?></td></tr>
                            <tr><td>Biaya pemeriksaan gejala</td><td><?php echo formatRupiah($biaya["biaya_gejala"]); ?></td></tr>
                            <tr><td>Subtotal</td><td><?php echo formatRupiah($biaya["subtotal"]); ?></td></tr>
                            <tr><td>Diskon <?php echo kategoriUsia($data["umur"]); ?> (<?php echo $biaya["persen"]; ?>%)</td><td>- <?php echo formatRupiah($biaya["diskon"]); ?></td></tr>
                            <tr><th>Total Biaya</th><th><?php echo formatRupiah($biaya["total"]); ?></th></tr>
                        </table>
                    </td>
                </tr>
                <tr><td>Kunjungan</td><td>:</td><td><?php echo namaHari($data["tanggal_kunjungan"]) . ", " . formatTanggal($data["tanggal_kunjungan"]); ?></td></tr>
                <tr><td>Deskripsi</td><td>:</td><td><?php echo nl2br(htmlspecialchars($data["deskripsi"])); ?></td></tr>
                <tr><td>Didaftarkan</td><td>:</td><td><?php echo date("d-m-Y H:i", strtotime($data["created_at"])); ?></td></tr>
            </table>

            <div class="bukti">
                <h3>Bukti Bayar</h3>
                <?php if (!empty($data["bukti_bayar"])) { ?>
                    <a href="uploads/<?php echo htmlspecialchars($data["bukti_bayar"]); ?>" target="_blank">
                        <img src="uploads/<?php echo htmlspecialchars($data["bukti_bayar"]); ?>" alt="Bukti bayar">
                    </a>
                <?php } else { ?>
                    <p class="kosong">Belum ada bukti bayar.</p>
                <?php } ?>
            </div>
        </div>

        <a href="ubah.php?id=<?php echo $data["id"]; ?>" class="btn btn-kuning">Ubah</a>
        <a href="hapus.php?id=<?php echo $data["id"]; ?>" class="btn btn-merah">Hapus</a>
        <a href="index.php" class="btn btn-abu">Kembali</a>
    </div>
</body>
</html>
