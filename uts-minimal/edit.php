<?php
// edit.php -> edit data (dibuka dari index: edit.php?id=3)
include "cek_login.php";
include "koneksi.php";

$id = (int) $_GET["id"];

// 1. Ambil data lama
$data = $conn->query("SELECT * FROM mahasiswa WHERE id = $id")->fetch_assoc();

// 2. Simpan perubahan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim       = $conn->real_escape_string($_POST["nim"]);
    $nama      = $conn->real_escape_string($_POST["nama"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : "";
    $jurusan   = $conn->real_escape_string($_POST["jurusan"]);
    $tgl_lahir = $conn->real_escape_string($_POST["tgl_lahir"]);
    $alamat    = $conn->real_escape_string($_POST["alamat"]);

    // WAJIB pakai WHERE, kalau tidak SEMUA data ikut berubah
    $sql = "UPDATE mahasiswa SET
                nim = '$nim',
                nama = '$nama',
                jenis_kelamin = '$jk',
                jurusan = '$jurusan',
                tgl_lahir = '$tgl_lahir',
                alamat = '$alamat'
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Data berhasil diubah'); window.location='index.php';</script>";
        exit;
    } else {
        echo "Gagal: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Edit Data Mahasiswa</h2>

    <form method="post" action="">
        <table>
            <tr>
                <td>NIM</td>
                <td><input type="text" name="nim" value="<?php echo $data["nim"]; ?>" required></td>
            </tr>
            <tr>
                <td>Nama</td>
                <td><input type="text" name="nama" value="<?php echo $data["nama"]; ?>" required></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>
                    <input type="radio" name="jk" value="L" <?php if ($data["jenis_kelamin"] == "L") echo "checked"; ?>> Laki-laki
                    <input type="radio" name="jk" value="P" <?php if ($data["jenis_kelamin"] == "P") echo "checked"; ?>> Perempuan
                </td>
            </tr>
            <tr>
                <td>Jurusan</td>
                <td>
                    <select name="jurusan" required>
                        <option value="Teknik Informatika" <?php if ($data["jurusan"] == "Teknik Informatika") echo "selected"; ?>>Teknik Informatika</option>
                        <option value="Sistem Informasi" <?php if ($data["jurusan"] == "Sistem Informasi") echo "selected"; ?>>Sistem Informasi</option>
                        <option value="Desain Komunikasi Visual" <?php if ($data["jurusan"] == "Desain Komunikasi Visual") echo "selected"; ?>>Desain Komunikasi Visual</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td><input type="date" name="tgl_lahir" value="<?php echo $data["tgl_lahir"]; ?>" required></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <!-- textarea: isi di ANTARA tag, bukan value="" -->
                <td><textarea name="alamat" rows="3"><?php echo $data["alamat"]; ?></textarea></td>
            </tr>
        </table>
        <br>
        <button type="submit">Update</button>
        <a href="index.php">Batal</a>
    </form>
</body>
</html>
