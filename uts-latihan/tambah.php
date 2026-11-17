<?php
// =====================================================================
// tambah.php  ->  KISI-KISI (b2): TAMBAH DATA (Create)
// Pola: form POST ke diri sendiri -> ambil $_POST & $_FILES -> validasi
//       -> upload foto -> INSERT -> alert + kembali ke index.php
// =====================================================================
include "cek_login.php"; // [SKIP-LOGIN] hapus baris ini kalau tanpa login
include "koneksi.php";
include "validasi.php";  // fungsi validasiMahasiswa(), validasiFoto(), uploadFoto(), $daftar_jurusan

// Nilai awal form kosong (supaya isian tidak hilang kalau validasi gagal = "sticky form")
$nim = $nama = $email = $jk = $jurusan = $tgl_lahir = $alamat = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form. trim() = buang spasi di awal/akhir
    $nim       = trim($_POST["nim"]);
    $nama      = trim($_POST["nama"]);
    $email     = trim($_POST["email"]);
    $jk        = isset($_POST["jk"]) ? $_POST["jk"] : ""; // radio tidak dipilih = tidak terkirim
    $jurusan   = $_POST["jurusan"];
    $tgl_lahir = $_POST["tgl_lahir"];
    $alamat    = trim($_POST["alamat"]);
    $foto      = $_FILES["foto"];

    // 2. Validasi data + foto (foto WAJIB saat tambah), gabungkan semua error
    $data = [
        "nim" => $nim, "nama" => $nama, "email" => $email, "jk" => $jk,
        "jurusan" => $jurusan, "tgl_lahir" => $tgl_lahir, "alamat" => $alamat,
    ];
    $errors = array_merge(validasiMahasiswa($data), validasiFoto($foto, true));

    // 3. Simpan kalau tidak ada error
    if (empty($errors)) {
        $nama_foto = uploadFoto($foto);

        if ($nama_foto === false) {
            $errors[] = "Gagal menyimpan foto.";
        } else {
            // real_escape_string -> amankan tanda kutip ' supaya query tidak rusak (SQL Injection)
            $nim_aman     = $conn->real_escape_string($nim);
            $nama_aman    = $conn->real_escape_string($nama);
            $email_aman   = $conn->real_escape_string($email);
            $jk_aman      = $conn->real_escape_string($jk);
            $jurusan_aman = $conn->real_escape_string($jurusan);
            $tgl_aman     = $conn->real_escape_string($tgl_lahir);
            $alamat_aman  = $conn->real_escape_string($alamat);
            $foto_aman    = $conn->real_escape_string($nama_foto);

            $sql = "INSERT INTO mahasiswa (nim, nama, email, jenis_kelamin, jurusan, tgl_lahir, alamat, foto)
                    VALUES ('$nim_aman', '$nama_aman', '$email_aman', '$jk_aman', '$jurusan_aman',
                            '$tgl_aman', '$alamat_aman', '$foto_aman')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Data berhasil ditambah'); window.location='index.php';</script>";
                exit;
            }

            // INSERT gagal -> foto yang sudah terupload dihapus lagi supaya tidak jadi sampah
            hapusFoto($nama_foto);
            if ($conn->errno == 1062) {
                $errors[] = "NIM $nim sudah terdaftar."; // 1062 = data UNIQUE kembar
            } else {
                $errors[] = "Gagal menyimpan: " . $conn->error;
            }
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
    // Tampilkan SEMUA error sekaligus (Pertemuan 6)
    foreach ($errors as $err) {
        echo "<p class='error'>$err</p>";
    }
    ?>

    <!--
        enctype="multipart/form-data" WAJIB kalau ada input file. Tanpa ini $_FILES kosong!
        required, pattern, maxlength, type="email" = validasi sisi KLIEN (HTML5).
        Validasi sisi SERVER (validasi.php) tetap wajib karena validasi klien bisa diakali.
    -->
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
                <td><textarea name="alamat" rows="3" maxlength="200"><?php echo htmlspecialchars($alamat); ?></textarea></td>
            </tr>
            <tr>
                <td>Foto</td><td>:</td>
                <!-- file tidak bisa "diingat" (sticky), user harus pilih ulang kalau validasi gagal -->
                <td><input type="file" name="foto" accept=".jpg,.jpeg,.png" required>
                    <br><small>JPG/PNG, maks 2MB</small></td>
            </tr>
        </table>
        <button type="submit">Simpan</button>
        <a href="index.php" class="btn btn-abu">Kembali</a>
    </form>
</body>
</html>
<?php $conn->close(); ?>
