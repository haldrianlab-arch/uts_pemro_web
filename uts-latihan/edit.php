<?php
// =====================================================================
// edit.php  ->  KISI-KISI (b3): EDIT DATA (Update)
// Dibuka dari index.php:  edit.php?id=3
// Pola: ambil id dari $_GET -> SELECT data lama -> isi form
//       -> POST -> validasi -> UPDATE ... WHERE id = $id -> alert + index
// Bedanya dengan tambah.php hanya 3 hal:
//   (1) ada SELECT data lama, (2) value form diisi data lama, (3) INSERT jadi UPDATE
// =====================================================================
include "cek_login.php"; // [SKIP-LOGIN] hapus baris ini kalau tanpa login
include "koneksi.php";

$daftar_jurusan = ["Teknik Informatika", "Sistem Informasi", "Desain Komunikasi Visual"];

// (int) -> id dipaksa jadi angka, aman dari SQL Injection
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// 1. Ambil data lama
$result = $conn->query("SELECT * FROM mahasiswa WHERE id = $id");
if ($result->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index.php';</script>";
    exit;
}
$data = $result->fetch_assoc();

// 2. Isi variabel form dengan data lama
$nim       = $data["nim"];
$nama      = $data["nama"];
$jk        = $data["jenis_kelamin"];
$jurusan   = $data["jurusan"];
$tgl_lahir = $data["tgl_lahir"];
$alamat    = $data["alamat"];
$errors    = [];

// 3. Proses saat tombol Update diklik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim       = trim($_POST["nim"]);
    $nama      = trim($_POST["nama"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : "";
    $jurusan   = $_POST["jurusan"];
    $tgl_lahir = $_POST["tgl_lahir"];
    $alamat    = trim($_POST["alamat"]);

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

    if (empty($errors)) {
        $nim_aman     = $conn->real_escape_string($nim);
        $nama_aman    = $conn->real_escape_string($nama);
        $jk_aman      = $conn->real_escape_string($jk);
        $jurusan_aman = $conn->real_escape_string($jurusan);
        $tgl_aman     = $conn->real_escape_string($tgl_lahir);
        $alamat_aman  = $conn->real_escape_string($alamat);

        // JANGAN LUPA "WHERE id = $id" -> tanpa WHERE, SEMUA baris ikut berubah!
        $sql = "UPDATE mahasiswa SET
                    nim = '$nim_aman',
                    nama = '$nama_aman',
                    jenis_kelamin = '$jk_aman',
                    jurusan = '$jurusan_aman',
                    tgl_lahir = '$tgl_aman',
                    alamat = '$alamat_aman'
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Data berhasil diubah'); window.location='index.php';</script>";
            exit;
        } else if ($conn->errno == 1062) {
            $errors[] = "NIM $nim sudah dipakai mahasiswa lain.";
        } else {
            $errors[] = "Gagal mengubah: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Data</title>
    <link rel="stylesheet" href="style.css"> <!-- [SKIP-CSS] boleh tidak ditulis kalau tanpa CSS -->
</head>
<body>
    <h1>Edit Data Mahasiswa</h1>

    <?php
    foreach ($errors as $err) {
        echo "<p class='error'>$err</p>";
    }
    ?>

    <!-- action kosong = kirim ke URL yang sama, termasuk ?id=... -->
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
                    <!-- checked sesuai data lama -->
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
                            <!-- selected sesuai data lama -->
                            <option value="<?php echo $j; ?>" <?php echo ($jurusan == $j) ? "selected" : ""; ?>><?php echo $j; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td><td>:</td>
                <!-- input date butuh format YYYY-MM-DD, sama dengan format DATE di MySQL -->
                <td><input type="date" name="tgl_lahir" value="<?php echo htmlspecialchars($tgl_lahir); ?>" required></td>
            </tr>
            <tr>
                <td>Alamat</td><td>:</td>
                <!-- textarea: isi ditaruh DI ANTARA tag, bukan di value="" -->
                <td><textarea name="alamat" rows="3"><?php echo htmlspecialchars($alamat); ?></textarea></td>
            </tr>
        </table>
        <button type="submit">Update</button>
        <a href="index.php" class="btn btn-abu">Batal</a>
    </form>
</body>
</html>
<?php $conn->close(); ?>
