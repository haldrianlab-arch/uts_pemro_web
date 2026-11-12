<?php
// =====================================================================
// poli.php  ->  CRUD LENGKAP DALAM 1 FILE (tabel master "poli")
//
// Kalau soal ujian minta CRUD sederhana 1 tabel, pola file ini paling cepat
// ditulis. Cara membedakan aksi: input hidden name="aksi".
//   - POST aksi=tambah  -> INSERT
//   - POST aksi=ubah    -> UPDATE
//   - POST aksi=hapus   -> DELETE
//   - GET  ?edit=ID     -> form diisi data lama (mode ubah)
//   - selain itu        -> tampilkan tabel (READ)
// =====================================================================
include "koneksi.php";
include "fungsi.php";

$errors    = [];
$pesan     = "";
$nama_poli = "";
$tarif     = "";
$edit_id   = 0; // 0 = mode tambah, > 0 = mode ubah

// ---------------------------------------------------------------------
// PROSES POST (tambah / ubah / hapus)
// ---------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aksi = $_POST["aksi"];

    if ($aksi == "hapus") {
        $id = (int) $_POST["id"];

        if ($conn->query("DELETE FROM poli WHERE id = $id") === TRUE) {
            $pesan = "Poli berhasil dihapus.";
        } else if ($conn->errno == 1451) {
            // 1451 = error FOREIGN KEY: poli masih dipakai oleh data pasien
            $errors[] = "Poli tidak bisa dihapus karena masih dipakai data pasien.";
        } else {
            $errors[] = "Gagal menghapus: " . $conn->error;
        }
    } else {
        // aksi tambah / ubah -> validasi dulu
        $nama_poli = trim($_POST["nama_poli"]);
        $tarif     = trim($_POST["tarif"]);
        $edit_id   = (int) $_POST["id"];

        if (empty($nama_poli)) {
            $errors[] = "Nama poli wajib diisi.";
        } else if (strlen($nama_poli) > 50) {
            $errors[] = "Nama poli maksimal 50 karakter.";
        }

        if ($tarif === "") {
            $errors[] = "Tarif wajib diisi.";
        } else if (!is_numeric($tarif) || $tarif < 0) {
            $errors[] = "Tarif harus angka dan tidak boleh negatif.";
        }

        if (empty($errors)) {
            $nama_aman = $conn->real_escape_string(ucwords($nama_poli));
            $tarif_int = (int) $tarif;

            if ($aksi == "tambah") {
                $sql = "INSERT INTO poli (nama_poli, tarif) VALUES ('$nama_aman', $tarif_int)";
            } else {
                $sql = "UPDATE poli SET nama_poli = '$nama_aman', tarif = $tarif_int WHERE id = $edit_id";
            }

            if ($conn->query($sql) === TRUE) {
                $pesan = ($aksi == "tambah") ? "Poli berhasil ditambahkan." : "Poli berhasil diubah.";
                // kosongkan form lagi
                $nama_poli = "";
                $tarif     = "";
                $edit_id   = 0;
            } else if ($conn->errno == 1062) {
                // 1062 = Duplicate entry (kolom nama_poli bersifat UNIQUE)
                $errors[] = "Nama poli '" . htmlspecialchars($nama_poli) . "' sudah ada.";
            } else {
                $errors[] = "Gagal menyimpan: " . $conn->error;
            }
        }
    }
}

// ---------------------------------------------------------------------
// MODE UBAH: poli.php?edit=2 -> isi form dengan data lama
// ---------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["edit"])) {
    $edit_id = (int) $_GET["edit"];
    $res     = $conn->query("SELECT * FROM poli WHERE id = $edit_id");
    if ($res->num_rows > 0) {
        $row       = $res->fetch_assoc();
        $nama_poli = $row["nama_poli"];
        $tarif     = $row["tarif"];
    } else {
        $edit_id = 0;
    }
}

// ---------------------------------------------------------------------
// READ: ambil semua poli + jumlah pasien di tiap poli
// LEFT JOIN supaya poli yang belum punya pasien tetap muncul (jumlah = 0)
// ---------------------------------------------------------------------
$sql = "SELECT po.*, COUNT(p.id) AS jumlah_pasien
        FROM poli po
        LEFT JOIN pasien p ON p.poli_id = po.id
        GROUP BY po.id
        ORDER BY po.nama_poli";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Poli - Klinik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <span class="logo">Klinik Sehat</span>
        <a href="index.php">Data Pasien</a>
        <a href="tambah.php">+ Registrasi</a>
        <a href="poli.php" class="aktif">Data Poli</a>
        <a href="latihan.php">Latihan</a>
    </nav>

    <div class="container">
        <h1>Data Poli</h1>

        <?php
        if ($pesan != "") {
            echo "<div class='alert alert-sukses'>$pesan</div>";
        }
        if (!empty($errors)) {
            echo "<div class='alert alert-error'><ul>";
            foreach ($errors as $err) {
                echo "<li>$err</li>";
            }
            echo "</ul></div>";
        }
        ?>

        <!-- Form tambah / ubah (form yang sama, dibedakan oleh hidden input "aksi") -->
        <form action="poli.php" method="post" class="form-cari">
            <input type="hidden" name="aksi" value="<?php echo ($edit_id > 0) ? "ubah" : "tambah"; ?>">
            <input type="hidden" name="id" value="<?php echo $edit_id; ?>">

            <input type="text" name="nama_poli" placeholder="Nama poli" required maxlength="50"
                   value="<?php echo htmlspecialchars($nama_poli); ?>">
            <input type="number" name="tarif" placeholder="Tarif (Rp)" required min="0"
                   value="<?php echo htmlspecialchars($tarif); ?>">

            <?php if ($edit_id > 0) { ?>
                <button type="submit" class="btn-kuning">Simpan Perubahan</button>
                <a href="poli.php" class="btn btn-abu">Batal</a>
            <?php } else { ?>
                <button type="submit">+ Tambah Poli</button>
            <?php } ?>
        </form>

        <table>
            <tr>
                <th>No</th>
                <th>Nama Poli</th>
                <th>Tarif</th>
                <th>Jumlah Pasien</th>
                <th>Aksi</th>
            </tr>
            <?php
            $no = 1;
            while ($row = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row["nama_poli"]); ?></td>
                    <td><?php echo formatRupiah($row["tarif"]); ?></td>
                    <td><?php echo $row["jumlah_pasien"]; ?></td>
                    <td class="aksi">
                        <a href="poli.php?edit=<?php echo $row["id"]; ?>" class="btn btn-kecil btn-kuning">Ubah</a>
                        <!-- Hapus pakai form POST kecil + konfirmasi JavaScript confirm() -->
                        <form action="poli.php" method="post" class="inline"
                              onsubmit="return confirm('Hapus poli <?php echo htmlspecialchars($row["nama_poli"], ENT_QUOTES); ?>?');">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
                            <button type="submit" class="btn-kecil btn-merah">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>
