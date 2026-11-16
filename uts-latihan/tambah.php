<?php
// =====================================================================
// tambah.php  ->  KISI-KISI (b2): TAMBAH DATA (Create)
// Pola: form POST ke diri sendiri -> ambil $_POST -> validasi -> INSERT
//       -> alert + kembali ke index.php
// =====================================================================
include "cek_login.php"; // [SKIP-LOGIN] hapus baris ini kalau tanpa login
include "koneksi.php";

$daftar_jurusan = ["Teknik Informatika", "Sistem Informasi", "Desain Komunikasi Visual"];

// Nilai awal form kosong (supaya isian tidak hilang kalau validasi gagal)
$nim = $nama = $jk = $jurusan = $tgl_lahir = $alamat = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form
    $nim       = trim($_POST["nim"]);
    $nama      = trim($_POST["nama"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : ""; // radio tidak dipilih = tidak terkirim
    $jurusan   = $_POST["jurusan"];
    $tgl_lahir = $_POST["tgl_lahir"];
    $alamat    = trim($_POST["alamat"]);

    // 2. Validasi (Pertemuan 6)
    if (empty($nim)) {
        $errors[] = "NIM wajib diisi.";
    } else if (!is_numeric($nim) || strlen($nim) != 8) {
        $errors[] = "NIM harus 8 digit angka.";
    }
    if (empty($nama)) {
        $errors[] = "Nama wajib diisi.";
    } else if (!preg_match("/^[a-zA-Z\s]+$/", $nama)) {
        $errors[] = "Nama hanya boleh huruf dan spasi.";
    }
    if (empty($jk)) {
        $errors[] = "Jenis kelamin wajib dipilih.";
    }
    if (empty($jurusan)) {
        $errors[] = "Jurusan wajib dipilih.";
    }
    if (empty($tgl_lahir)) {
        $errors[] = "Tanggal lahir wajib diisi.";
    }

    // 3. Simpan kalau tidak ada error
    if (empty($errors)) {
        // real_escape_string -> amankan tanda kutip ' supaya query tidak rusak (SQL Injection)
        $nim_aman     = $conn->real_escape_string($nim);
        $nama_aman    = $conn->real_escape_string($nama);
        $jk_aman      = $conn->real_escape_string($jk);
        $jurusan_aman = $conn->real_escape_string($jurusan);
        $tgl_aman     = $conn->real_escape_string($tgl_lahir);
        $alamat_aman  = $conn->real_escape_string($alamat);

        $sql = "INSERT INTO mahasiswa (nim, nama, jenis_kelamin, jurusan, tgl_lahir, alamat)
                VALUES ('$nim_aman', '$nama_aman', '$jk_aman', '$jurusan_aman', '$tgl_aman', '$alamat_aman')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Data berhasil ditambah'); window.location='index.php';</script>";
            exit;
        } else if ($conn->errno == 1062) {
            $errors[] = "NIM $nim sudah terdaftar."; // 1062 = data UNIQUE kembar
        } else {
            $errors[] = "Gagal menyimpan: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tambah Data</title>
    <link rel="stylesheet" href="style.css"> <!-- [SKIP-CSS] boleh tidak ditulis kalau tanpa CSS -->
</head>
<body>
    <h1>Tambah Data Mahasiswa</h1>

    <?php
    // Tampilkan semua error
    foreach ($errors as $err) {
        echo "<p class='error'>$err</p>";
    }
    ?>

    <form method="post" action="">
        <table>
            <tr>
                <td>NIM</td><td>:</td>
                <td><input type="text" name="nim" value="<?php echo htmlspecialchars($nim); ?>" maxlength="8" required></td>
            </tr>
            <tr>
                <td>Nama</td><td>:</td>
                <td><input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td><td>:</td>
                <td>
                    <input type="radio" name="jk" value="L" <?php echo ($jk == "L") ? "checked" : ""; ?>> Laki-laki
                    <input type="radio" name="jk" value="P" <?php echo ($jk == "P") ? "checked" : ""; ?>> Perempuan
                </td>
            </tr>
            <tr>
                <td>Jurusan</td><td>:</td>
                <td>
                    <select name="jurusan" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($daftar_jurusan as $j) { ?>
                            <option value="<?php echo $j; ?>" <?php echo ($jurusan == $j) ? "selected" : ""; ?>><?php echo $j; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td><td>:</td>
                <td><input type="date" name="tgl_lahir" value="<?php echo htmlspecialchars($tgl_lahir); ?>" required></td>
            </tr>
            <tr>
                <td>Alamat</td><td>:</td>
                <td><textarea name="alamat" rows="3"><?php echo htmlspecialchars($alamat); ?></textarea></td>
            </tr>
        </table>
        <button type="submit">Simpan</button>
        <a href="index.php" class="btn btn-abu">Kembali</a>
    </form>
</body>
</html>
<?php $conn->close(); ?>
