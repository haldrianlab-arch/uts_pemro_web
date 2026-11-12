<?php
// =====================================================================
// index.php  ->  READ (menampilkan data)
// Fitur:
//   - Statistik ringkas (COUNT, SUM, GROUP BY)
//   - Pencarian & filter pakai method GET (Pertemuan 5: GET bisa di-bookmark)
//   - Tabel data pasien + JOIN ke tabel poli
//   - Pagination (LIMIT & OFFSET + perulangan FOR)
// =====================================================================
include "koneksi.php";
include "fungsi.php";

// ---------------------------------------------------------------------
// 1. Ambil parameter dari URL ($_GET)
//    Contoh URL: index.php?cari=agus&poli=1&status=Selesai&halaman=2
//    isset() dipakai karena saat pertama dibuka, parameter belum ada.
// ---------------------------------------------------------------------
$cari    = isset($_GET["cari"]) ? trim($_GET["cari"]) : "";
$poli    = isset($_GET["poli"]) ? $_GET["poli"] : "";
$status  = isset($_GET["status"]) ? $_GET["status"] : "";
$halaman = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
if ($halaman < 1) {
    $halaman = 1;
}

// ---------------------------------------------------------------------
// 2. Susun kondisi WHERE secara dinamis
//    Setiap filter yang diisi ditambahkan ke array $kondisi,
//    lalu digabung dengan implode(" AND ", ...)
// ---------------------------------------------------------------------
$kondisi = [];

if ($cari != "") {
    // real_escape_string() = mengamankan input dari SQL Injection
    // (karakter seperti ' diubah menjadi \' sehingga tidak merusak query)
    $cari_aman = $conn->real_escape_string($cari);
    $kondisi[] = "(p.nama LIKE '%$cari_aman%' OR p.no_registrasi LIKE '%$cari_aman%' OR p.email LIKE '%$cari_aman%')";
}
if ($poli != "") {
    $kondisi[] = "p.poli_id = " . (int) $poli; // (int) = paksa jadi angka, aman dari injection
}
if ($status != "" && in_array($status, $daftar_status)) {
    $kondisi[] = "p.status = '$status'";
}

$where = "";
if (count($kondisi) > 0) {
    $where = "WHERE " . implode(" AND ", $kondisi);
}

// ---------------------------------------------------------------------
// 3. Pagination
// ---------------------------------------------------------------------
$per_halaman = 5;

$sql_total  = "SELECT COUNT(*) AS total FROM pasien p $where";
$total_data = $conn->query($sql_total)->fetch_assoc()["total"];

$total_halaman = ceil($total_data / $per_halaman); // ceil = bulatkan ke atas
$offset        = ($halaman - 1) * $per_halaman;

// ---------------------------------------------------------------------
// 4. Query utama: JOIN pasien dengan poli untuk dapat nama_poli
// ---------------------------------------------------------------------
$sql = "SELECT p.*, po.nama_poli, po.tarif
        FROM pasien p
        JOIN poli po ON p.poli_id = po.id
        $where
        ORDER BY p.tanggal_kunjungan DESC, p.id DESC
        LIMIT $per_halaman OFFSET $offset";
$result = $conn->query($sql);

// ---------------------------------------------------------------------
// 5. Statistik (tidak terpengaruh filter)
// ---------------------------------------------------------------------
// Jumlah pasien per status -> GROUP BY, disimpan ke associative array
$statistik = ["Menunggu" => 0, "Diperiksa" => 0, "Selesai" => 0];
$res_stat  = $conn->query("SELECT status, COUNT(*) AS jumlah FROM pasien GROUP BY status");
while ($row = $res_stat->fetch_assoc()) {
    $statistik[$row["status"]] = $row["jumlah"];
}
$total_pasien = array_sum($statistik);

// Total pendapatan dari pasien yang sudah selesai.
// Biaya dihitung oleh fungsi hitungBiaya() (tidak tersimpan di database),
// jadi tidak bisa pakai SUM() di SQL -> dijumlahkan dengan perulangan di PHP.
$sql_pendapatan = "SELECT p.umur, p.gejala, po.tarif FROM pasien p
                   JOIN poli po ON p.poli_id = po.id
                   WHERE p.status = 'Selesai'";
$res_pendapatan = $conn->query($sql_pendapatan);
$pendapatan     = 0;
while ($row = $res_pendapatan->fetch_assoc()) {
    $biaya       = hitungBiaya($row["tarif"], $row["gejala"], $row["umur"]);
    $pendapatan += $biaya["total"];
}

// Daftar poli untuk dropdown filter
$res_poli = $conn->query("SELECT * FROM poli ORDER BY nama_poli");

// Query string filter, dipakai di link pagination supaya filter tidak hilang
$query_filter = "cari=" . urlencode($cari) . "&poli=" . urlencode($poli) . "&status=" . urlencode($status);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pasien - Klinik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <span class="logo">Klinik Sehat</span>
        <a href="index.php" class="aktif">Data Pasien</a>
        <a href="tambah.php">+ Registrasi</a>
        <a href="poli.php">Data Poli</a>
        <a href="latihan.php">Latihan</a>
    </nav>

    <div class="container">
        <h1>Data Registrasi Pasien</h1>

        <!-- Kartu statistik (Flexbox - Pertemuan 3) -->
        <div class="kartu-wrap">
            <div class="kartu">
                <span class="angka"><?php echo $total_pasien; ?></span>
                <span>Total Pasien</span>
            </div>
            <?php
            // foreach associative array: $kunci => $nilai
            foreach ($statistik as $nama_status => $jumlah) {
                echo "<div class='kartu'>";
                echo "<span class='angka'>$jumlah</span>";
                echo "<span>$nama_status</span>";
                echo "</div>";
            }
            ?>
            <div class="kartu">
                <span class="angka kecil"><?php echo formatRupiah($pendapatan); ?></span>
                <span>Pendapatan (Selesai)</span>
            </div>
        </div>

        <!-- Form pencarian pakai GET: hasilnya muncul di URL -->
        <form action="index.php" method="get" class="form-cari">
            <input type="text" name="cari" placeholder="Cari nama / no. registrasi / email"
                   value="<?php echo htmlspecialchars($cari); ?>">

            <select name="poli">
                <option value="">-- Semua Poli --</option>
                <?php while ($row_poli = $res_poli->fetch_assoc()) { ?>
                    <option value="<?php echo $row_poli["id"]; ?>"
                        <?php echo ($poli == $row_poli["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($row_poli["nama_poli"]); ?>
                    </option>
                <?php } ?>
            </select>

            <select name="status">
                <option value="">-- Semua Status --</option>
                <?php foreach ($daftar_status as $s) { ?>
                    <option value="<?php echo $s; ?>" <?php echo ($status == $s) ? "selected" : ""; ?>>
                        <?php echo $s; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit">Cari</button>
            <a href="index.php" class="btn btn-abu">Reset</a>
        </form>

        <p>Ditemukan <b><?php echo $total_data; ?></b> data.</p>

        <?php if ($result->num_rows > 0) { ?>
            <table>
                <tr>
                    <th>No</th>
                    <th>No. Registrasi</th>
                    <th>Nama</th>
                    <th>Umur</th>
                    <th>JK</th>
                    <th>Gejala</th>
                    <th>Poli</th>
                    <th>Total Biaya</th>
                    <th>Kunjungan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                <?php
                $no = $offset + 1; // nomor urut lanjut sesuai halaman
                while ($row = $result->fetch_assoc()) {
                    // explode: "Pusing,Flu" -> ["Pusing", "Flu"]
                    // implode: ["Pusing", "Flu"] -> "Pusing, Flu"
                    $gejala = implode(", ", explode(",", $row["gejala"]));

                    // Kolom hasil perhitungan (tidak ada di database)
                    $biaya = hitungBiaya($row["tarif"], $row["gejala"], $row["umur"]);
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row["no_registrasi"]; ?></td>
                        <td><?php echo htmlspecialchars(ucwords($row["nama"])); ?></td>
                        <td><?php echo $row["umur"]; ?> th<br><small><?php echo kategoriUsia($row["umur"]); ?></small></td>
                        <td><?php echo $row["jenis_kelamin"]; ?></td>
                        <td><?php echo htmlspecialchars($gejala); ?></td>
                        <td><?php echo htmlspecialchars($row["nama_poli"]); ?></td>
                        <td><?php echo formatRupiah($biaya["total"]); ?></td>
                        <td>
                            <?php echo namaHari($row["tanggal_kunjungan"]); ?>,<br>
                            <?php echo formatTanggal($row["tanggal_kunjungan"]); ?>
                        </td>
                        <td><?php echo labelStatus($row["status"]); ?></td>
                        <td class="aksi">
                            <a href="detail.php?id=<?php echo $row["id"]; ?>" class="btn btn-kecil">Detail</a>
                            <a href="ubah.php?id=<?php echo $row["id"]; ?>" class="btn btn-kecil btn-kuning">Ubah</a>
                            <a href="hapus.php?id=<?php echo $row["id"]; ?>" class="btn btn-kecil btn-merah">Hapus</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>

            <!-- Pagination: perulangan FOR (Pertemuan 5) -->
            <div class="pagination">
                <?php if ($halaman > 1) { ?>
                    <a href="index.php?<?php echo $query_filter; ?>&halaman=<?php echo $halaman - 1; ?>">&laquo; Prev</a>
                <?php } ?>

                <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>
                    <a href="index.php?<?php echo $query_filter; ?>&halaman=<?php echo $i; ?>"
                       class="<?php echo ($i == $halaman) ? "aktif" : ""; ?>"><?php echo $i; ?></a>
                <?php } ?>

                <?php if ($halaman < $total_halaman) { ?>
                    <a href="index.php?<?php echo $query_filter; ?>&halaman=<?php echo $halaman + 1; ?>">Next &raquo;</a>
                <?php } ?>
            </div>
        <?php
        } else {
            echo "<p class='kosong'>0 result - data tidak ditemukan.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
