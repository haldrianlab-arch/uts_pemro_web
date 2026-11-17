<?php
// tambah.php -> tambah data + validasi (Pertemuan 6) + upload foto
include "koneksi.php";

$nim = $nama = $email = $jk = $jurusan = $tgl_lahir = $alamat = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim       = trim($_POST["nim"]);
    $nama      = trim($_POST["nama"]);
    $email     = trim($_POST["email"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : ""; // radio tidak dipilih = tidak terkirim
    $jurusan   = $_POST["jurusan"];
    $tgl_lahir = $_POST["tgl_lahir"];
    $alamat    = trim($_POST["alamat"]);
    $foto      = $_FILES["foto"];

    // ===== VALIDASI =====
    if (empty($nim)) {
        $errors[] = "NIM wajib diisi.";
    } else if (!is_numeric($nim) || strlen($nim) != 8) {
        $errors[] = "NIM harus 8 digit angka.";
    }

    if (empty($nama)) {
        $errors[] = "Nama wajib diisi.";
    } else if (strlen($nama) < 3 || strlen($nama) > 50) {
        $errors[] = "Nama harus 3-50 karakter.";
    } else if (!preg_match("/^[a-zA-Z\s]+$/", $nama)) {
        $errors[] = "Nama hanya boleh huruf dan spasi.";
    }

    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    if (empty($jk))        $errors[] = "Jenis kelamin wajib dipilih.";
    if (empty($jurusan))   $errors[] = "Jurusan wajib dipilih.";
    if (empty($tgl_lahir)) $errors[] = "Tanggal lahir wajib diisi.";

    // Validasi foto (wajib saat tambah)
    $allowed_types = ["image/jpeg", "image/png"];
    $max_size      = 2 * 1024 * 1024; // 2MB

    if ($foto["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Foto wajib diunggah.";
    } else if (!in_array($foto["type"], $allowed_types)) {
        $errors[] = "Hanya file JPG dan PNG yang diizinkan.";
    } else if ($foto["size"] > $max_size) {
        $errors[] = "Ukuran file maksimum 2MB.";
    }

    // ===== SIMPAN =====
    if (empty($errors)) {
        $nama_foto = uniqid() . "-" . basename($foto["name"]);
        move_uploaded_file($foto["tmp_name"], "uploads/" . $nama_foto);

        // real_escape_string -> supaya tanda kutip ' tidak merusak query
        $nim       = $conn->real_escape_string($nim);
        $nama      = $conn->real_escape_string($nama);
        $email     = $conn->real_escape_string($email);
        $jk        = $conn->real_escape_string($jk);
        $jurusan   = $conn->real_escape_string($jurusan);
        $tgl_lahir = $conn->real_escape_string($tgl_lahir);
        $alamat    = $conn->real_escape_string($alamat);
        $nama_foto = $conn->real_escape_string($nama_foto);

        $sql = "INSERT INTO mahasiswa (nim, nama, email, jenis_kelamin, jurusan, tgl_lahir, alamat, foto)
                VALUES ('$nim', '$nama', '$email', '$jk', '$jurusan', '$tgl_lahir', '$alamat', '$nama_foto')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Data berhasil ditambah'); window.location='index.php';</script>";
            exit;
        } else {
            $errors[] = "Gagal menyimpan: " . $conn->error;
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

    <?php foreach ($errors as $err) { echo "<p style='color:red'>$err</p>"; } ?>

    <!-- enctype WAJIB kalau ada input file -->
    <form method="post" action="" enctype="multipart/form-data">
        <table>
            <tr>
                <td>NIM</td>
                <td><input type="text" name="nim" value="<?php echo htmlspecialchars($nim); ?>" required pattern="[0-9]{8}" title="8 digit angka"></td>
            </tr>
            <tr>
                <td>Nama</td>
                <td><input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required pattern="[a-zA-Z\s]{3,50}" title="Huruf & spasi, 3-50 karakter"></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>
                    <input type="radio" name="jk" value="L" required <?php if ($jk == "L") echo "checked"; ?>> Laki-laki
                    <input type="radio" name="jk" value="P" <?php if ($jk == "P") echo "checked"; ?>> Perempuan
                </td>
            </tr>
            <tr>
                <td>Jurusan</td>
                <td>
                    <select name="jurusan" required>
                        <option value="">-- Pilih --</option>
                        <option value="Teknik Informatika" <?php if ($jurusan == "Teknik Informatika") echo "selected"; ?>>Teknik Informatika</option>
                        <option value="Sistem Informasi" <?php if ($jurusan == "Sistem Informasi") echo "selected"; ?>>Sistem Informasi</option>
                        <option value="Desain Komunikasi Visual" <?php if ($jurusan == "Desain Komunikasi Visual") echo "selected"; ?>>Desain Komunikasi Visual</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Tanggal Lahir</td>
                <td><input type="date" name="tgl_lahir" value="<?php echo $tgl_lahir; ?>" required></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td><textarea name="alamat" rows="3"><?php echo htmlspecialchars($alamat); ?></textarea></td>
            </tr>
            <tr>
                <td>Foto</td>
                <td><input type="file" name="foto" accept=".jpg,.jpeg,.png" required> <small>JPG/PNG, maks 2MB</small></td>
            </tr>
        </table>
        <br>
        <button type="submit">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
