<?php
// tambah.php -> tambah data
include "cek_login.php";
include "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // real_escape_string -> supaya tanda kutip ' di input tidak merusak query
    $nim       = $conn->real_escape_string($_POST["nim"]);
    $nama      = $conn->real_escape_string($_POST["nama"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : "";
    $jurusan   = $conn->real_escape_string($_POST["jurusan"]);
    $tgl_lahir = $conn->real_escape_string($_POST["tgl_lahir"]);
    $alamat    = $conn->real_escape_string($_POST["alamat"]);

    if (empty($nim) || empty($nama) || empty($jk) || empty($jurusan) || empty($tgl_lahir)) {
        echo "<script>alert('Semua data wajib diisi!');</script>";
    } else {
        $sql = "INSERT INTO mahasiswa (nim, nama, jenis_kelamin, jurusan, tgl_lahir, alamat)
                VALUES ('$nim', '$nama', '$jk', '$jurusan', '$tgl_lahir', '$alamat')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Data berhasil ditambah'); window.location='index.php';</script>";
            exit;
        } else {
            echo "Gagal: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Tambah Data Mahasiswa</h2>

    <form method="post" action="">
        <table>
            <tr>
                <td>NIM</td>
                <td><input type="text" name="nim" required></td>
            </tr>
            <tr>
                <td>Nama</td>
                <td><input type="text" name="nama" required></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>
                    <input type="radio" name="jk" value="L" required> Laki-laki
                    <input type="radio" name="jk" value="P"> Perempuan
                </td>
            </tr>
            <tr>
                <td>Jurusan</td>
                <td>
                    <select name="jurusan" required>
                        <option value="">-- Pilih --</option>
                        <option value="Teknik Informatika">Teknik Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                        <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td><input type="date" name="tgl_lahir" required></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td><textarea name="alamat" rows="3"></textarea></td>
            </tr>
        </table>
        <br>
        <button type="submit">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
