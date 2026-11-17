<?php
// =====================================================================
// edit.php  ->  KISI-KISI (b3): EDIT DATA (Update)
// Dibuka dari index.php:  edit.php?id=3
// Pola: ambil id dari $_GET -> SELECT data lama -> isi form
//       -> POST -> validasi -> (upload foto baru) -> UPDATE ... WHERE id = $id
// Bedanya dengan tambah.php:
//   (1) ada SELECT data lama, (2) value form diisi data lama,
//   (3) INSERT jadi UPDATE, (4) foto BOLEH kosong = pakai foto lama
// =====================================================================
include "cek_login.php"; // [SKIP-LOGIN] hapus baris ini kalau tanpa login
include "koneksi.php";
include "validasi.php";

// (int) -> id dipaksa jadi angka, aman dari SQL Injection
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// 1. Ambil data lama
$result = $conn->query("SELECT * FROM mahasiswa WHERE id = $id");
if ($result->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index.php';</script>";
    exit;
}
$data_lama = $result->fetch_assoc();

// 2. Isi variabel form dengan data lama
$nim       = $data_lama["nim"];
$nama      = $data_lama["nama"];
$email     = $data_lama["email"];
$jk        = $data_lama["jenis_kelamin"];
$jurusan   = $data_lama["jurusan"];
$tgl_lahir = $data_lama["tgl_lahir"];
$alamat    = $data_lama["alamat"];
$errors    = [];

// 3. Proses saat tombol Update diklik
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim       = trim($_POST["nim"]);
    $nama      = trim($_POST["nama"]);
    $email     = trim($_POST["email"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : "";
    $jurusan   = $_POST["jurusan"];
    $tgl_lahir = $_POST["tgl_lahir"];
    $alamat    = trim($_POST["alamat"]);
    $foto      = $_FILES["foto"];

    $data = [
        "nim" => $nim, "nama" => $nama, "email" => $email, "jk" => $jk,
        "jurusan" => $jurusan, "tgl_lahir" => $tgl_lahir, "alamat" => $alamat,
    ];
    // false = foto TIDAK wajib saat edit
    $errors = array_merge(validasiMahasiswa($data), validasiFoto($foto, false));

    // Foto: kalau user pilih file baru -> upload. Kalau tidak -> pakai nama file lama
    $nama_foto = (string) $data_lama["foto"]; // (string): NULL dari database jadi ""
    $foto_baru = false;
    if (empty($errors) && $foto["error"] == UPLOAD_ERR_OK) {
        $nama_foto = uploadFoto($foto);
        $foto_baru = true;
        if ($nama_foto === false) {
            $errors[] = "Gagal menyimpan foto.";
        }
    }

    if (empty($errors)) {
        $nim_aman     = $conn->real_escape_string($nim);
        $nama_aman    = $conn->real_escape_string($nama);
        $email_aman   = $conn->real_escape_string($email);
        $jk_aman      = $conn->real_escape_string($jk);
        $jurusan_aman = $conn->real_escape_string($jurusan);
        $tgl_aman     = $conn->real_escape_string($tgl_lahir);
        $alamat_aman  = $conn->real_escape_string($alamat);
        $foto_aman    = $conn->real_escape_string($nama_foto);

        // JANGAN LUPA "WHERE id = $id" -> tanpa WHERE, SEMUA baris ikut berubah!
        $sql = "UPDATE mahasiswa SET
                    nim = '$nim_aman',
                    nama = '$nama_aman',
                    email = '$email_aman',
                    jenis_kelamin = '$jk_aman',
                    jurusan = '$jurusan_aman',
                    tgl_lahir = '$tgl_aman',
                    alamat = '$alamat_aman',
                    foto = '$foto_aman'
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            if ($foto_baru) {
                hapusFoto($data_lama["foto"]); // foto lama dibuang supaya folder uploads tidak menumpuk
            }
            echo "<script>alert('Data berhasil diubah'); window.location='index.php';</script>";
            exit;
        }

        if ($foto_baru) {
            hapusFoto($nama_foto); // UPDATE gagal -> buang foto baru
        }
        if ($conn->errno == 1062) {
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

    <!-- action kosong = kirim ke URL yang sama, termasuk ?id=... ; enctype wajib karena ada file -->
    <form method="post" action="" enctype="multipart/form-data">
        <table>
            <tr>
                <td>NIM</td><td>:</td>
                <td><input type="text" name="nim" value="<?php echo htmlspecialchars($nim); ?>"
                           required maxlength="8" pattern="[0-9]{8}" title="8 digit angka"></td>
            </tr>
            <tr>
                <td>Nama</td><td>:</td>
                <td><input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>"
                           required minlength="3" maxlength="50" pattern="[a-zA-Z\s]+" title="Hanya huruf dan spasi"></td>
            </tr>
            <tr>
                <td>Email</td><td>:</td>
                <td><input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                           required maxlength="50"></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td><td>:</td>
                <td>
                    <!-- checked sesuai data lama -->
                    <input type="radio" name="jk" value="L" required <?php echo ($jk == "L") ? "checked" : ""; ?>> Laki-laki
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
                <td><textarea name="alamat" rows="3" maxlength="200"><?php echo htmlspecialchars($alamat); ?></textarea></td>
            </tr>
            <tr>
                <td>Foto</td><td>:</td>
                <td>
                    <?php if (!empty($data_lama["foto"])) { ?>
                        <img src="uploads/<?php echo htmlspecialchars($data_lama["foto"]); ?>" width="80"><br>
                    <?php } ?>
                    <!-- TIDAK pakai required: kosong = foto lama tetap dipakai -->
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png">
                    <br><small>Kosongkan jika tidak diganti. JPG/PNG, maks 2MB</small>
                </td>
            </tr>
        </table>
        <button type="submit">Update</button>
        <a href="index.php" class="btn btn-abu">Batal</a>
    </form>
</body>
</html>
<?php $conn->close(); ?>
