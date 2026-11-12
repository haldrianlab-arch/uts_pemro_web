<?php
// =====================================================================
// tambah.php  ->  CREATE (menambah data)
// Alur:
//   1. User buka halaman (GET)  -> form kosong ditampilkan
//   2. User klik "Kirim!" (POST) -> data divalidasi
//   3a. Ada error  -> form ditampilkan lagi + pesan error + isian lama tetap ada
//   3b. Tidak error -> upload file, INSERT ke database, alert, kembali ke index
// =====================================================================
include "koneksi.php";
include "fungsi.php";

// Ambil semua poli dari database untuk isi <select>
// Disimpan ke array supaya bisa dipakai 2x (untuk validasi & untuk form)
$daftar_poli    = [];
$daftar_poli_id = [];
$res_poli = $conn->query("SELECT * FROM poli ORDER BY nama_poli");
while ($row = $res_poli->fetch_assoc()) {
    $daftar_poli[]    = $row;       // multidimensional array
    $daftar_poli_id[] = $row["id"]; // indexed array, isinya id saja
}

// Nilai awal form (kosong). Dibuat variabel supaya form bisa "mengingat"
// isian user ketika validasi gagal (sticky form).
$nama              = "";
$umur              = "";
$email             = "";
$no_hp             = "";
$jk                = "";
$gejala            = [];
$poli_id           = "";
$deskripsi         = "";
$tanggal_kunjungan = date("Y-m-d"); // default: hari ini
$errors            = [];

// Proses hanya kalau form dikirim dengan method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // trim() menghapus spasi di awal & akhir input
    $nama              = trim($_POST["nama"]);
    $umur              = trim($_POST["umur"]);
    $email             = trim($_POST["email"]);
    $no_hp             = trim($_POST["no_hp"]);
    $pin              = trim($_POST["pin"]);
    // Radio & checkbox yang TIDAK dipilih tidak ikut terkirim sama sekali,
    // jadi harus dicek dengan isset() supaya tidak muncul "Undefined array key"
    $jk                = isset($_POST["jk"]) ? $_POST["jk"] : "";
    $gejala            = isset($_POST["gejala"]) ? $_POST["gejala"] : [];
    $poli_id           = $_POST["poli_id"];
    $deskripsi         = trim($_POST["deskripsi"]);
    $tanggal_kunjungan = $_POST["tanggal_kunjungan"];
    $file              = $_FILES["bukti_bayar"];

    // Kumpulkan data ke associative array untuk dikirim ke fungsi validasi
    $data = [
        "nama"              => $nama,
        "umur"              => $umur,
        "email"             => $email,
        "no_hp"             => $no_hp,
        "pin"             => $pin,
        "jk"                => $jk,
        "gejala"            => $gejala,
        "poli_id"           => $poli_id,
        "deskripsi"         => $deskripsi,
        "tanggal_kunjungan" => $tanggal_kunjungan,
    ];

    // array_merge = gabungkan error dari validasi data & validasi file
    $errors = array_merge(
        validasiPasien($data, $daftar_poli_id, "tambah"),
        validasiFile($file, true)
    );

    // Jika tidak ada error -> simpan
    if (empty($errors)) {
        $nama_file = uploadFile($file);

        if ($nama_file === false) {
            $errors[] = "Gagal menyimpan file.";
        } else {
            // Siapkan data sebelum masuk database
            $no_registrasi = buatNoRegistrasi();
            $pin_hash      = password_hash($pin, PASSWORD_DEFAULT); // PIN jangan disimpan mentah!
            $gejala_str    = implode(",", $gejala);                 // array -> "Pusing,Flu"

            // real_escape_string untuk SEMUA input teks dari user (cegah SQL Injection)
            $nama_aman      = $conn->real_escape_string(ucwords(strtolower($nama)));
            $email_aman     = $conn->real_escape_string(strtolower($email));
            $no_hp_aman     = $conn->real_escape_string($no_hp);
            $gejala_aman    = $conn->real_escape_string($gejala_str);
            $deskripsi_aman = $conn->real_escape_string($deskripsi);
            $tanggal_aman   = $conn->real_escape_string($tanggal_kunjungan);
            $umur_int       = (int) $umur;
            $poli_int       = (int) $poli_id;

            $sqladd = "INSERT INTO pasien
                (no_registrasi, nama, umur, email, no_hp, pin, jenis_kelamin, gejala, poli_id, deskripsi, bukti_bayar, tanggal_kunjungan)
                VALUES
                ('$no_registrasi', '$nama_aman', $umur_int, '$email_aman', '$no_hp_aman', '$pin_hash', '$jk',
                 '$gejala_aman', $poli_int, '$deskripsi_aman', '$nama_file', '$tanggal_aman')";

            if ($conn->query($sqladd) === TRUE) {
                // $conn->insert_id = id AUTO_INCREMENT dari data yang barusan masuk
                $id_baru = $conn->insert_id;
                echo "<script>
                        alert('Registrasi berhasil! No. Registrasi: $no_registrasi');
                        window.location = 'detail.php?id=$id_baru';
                      </script>";
                exit();
            } else {
                hapusFile($nama_file); // insert gagal -> file yang sudah terupload dihapus lagi
                $errors[] = "Gagal menyimpan data: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Pasien - Klinik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <span class="logo">Klinik Sehat</span>
        <a href="index.php">Data Pasien</a>
        <a href="tambah.php" class="aktif">+ Registrasi</a>
        <a href="poli.php">Data Poli</a>
        <a href="latihan.php">Latihan</a>
    </nav>

    <div class="container">
        <h1>Form Registrasi Klinik</h1>

        <?php
        // Tampilkan SEMUA error sekaligus (Pertemuan 6)
        if (!empty($errors)) {
            echo "<div class='alert alert-error'><b>Data belum valid:</b><ul>";
            foreach ($errors as $err) {
                echo "<li>" . $err . "</li>";
            }
            echo "</ul></div>";
        }
        ?>

        <!--
            enctype="multipart/form-data" WAJIB kalau form ada input file.
            Tanpa ini, $_FILES akan kosong.
            Atribut required, pattern, min, max = validasi sisi KLIEN (HTML5).
            Validasi sisi SERVER (PHP) tetap wajib karena validasi klien bisa dimatikan.
        -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>"
                               required minlength="3" maxlength="50" pattern="[a-zA-Z\s]+"
                               title="Hanya huruf dan spasi"></td>
                </tr>
                <tr>
                    <td>Umur</td>
                    <td>:</td>
                    <td><input type="number" name="umur" value="<?php echo htmlspecialchars($umur); ?>"
                               required min="0" max="120" class="input-pendek"></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>:</td>
                    <td><input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                               required maxlength="50"></td>
                </tr>
                <tr>
                    <td>Nomor HP</td>
                    <td>:</td>
                    <!-- type="tel" memunculkan keyboard angka di HP; pattern = validasi klien -->
                    <td><input type="tel" name="no_hp" value="<?php echo htmlspecialchars($no_hp); ?>"
                               required pattern="[0-9]{10,13}" title="10-13 digit angka" placeholder="08xxxxxxxxxx"></td>
                </tr>
                <tr>
                    <td>PIN</td>
                    <td>:</td>
                    <!-- password TIDAK diisi ulang (sticky) demi keamanan -->
                    <td><input type="password" name="pin" required pattern="[0-9]{4,6}"
                               title="4-6 digit angka" placeholder="4-6 digit angka"></td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>
                        <label><input type="radio" name="jk" value="L" required
                            <?php echo ($jk == "L") ? "checked" : ""; ?>> Laki-laki</label>
                        <label><input type="radio" name="jk" value="P"
                            <?php echo ($jk == "P") ? "checked" : ""; ?>> Perempuan</label>
                    </td>
                </tr>
                <tr>
                    <td>Gejala</td>
                    <td>:</td>
                    <td>
                        <?php
                        // name="gejala[]" -> tanda [] membuat PHP menerima nilai sebagai ARRAY
                        foreach ($daftar_gejala as $g) {
                            $checked = in_array($g, $gejala) ? "checked" : "";
                            echo "<label><input type='checkbox' name='gejala[]' value='$g' $checked> $g</label> ";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Poli Tujuan</td>
                    <td>:</td>
                    <td>
                        <select name="poli_id" required>
                            <option value="">-- Pilih Poli --</option>
                            <?php foreach ($daftar_poli as $p) { ?>
                                <option value="<?php echo $p["id"]; ?>"
                                    <?php echo ($poli_id == $p["id"]) ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($p["nama_poli"]) . " (" . formatRupiah($p["tarif"]) . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Kunjungan</td>
                    <td>:</td>
                    <td><input type="date" name="tanggal_kunjungan" required
                               min="<?php echo date("Y-m-d"); ?>"
                               value="<?php echo htmlspecialchars($tanggal_kunjungan); ?>"></td>
                </tr>
                <tr>
                    <td>Deskripsi</td>
                    <td>:</td>
                    <td><textarea name="deskripsi" rows="3" maxlength="500"><?php echo htmlspecialchars($deskripsi); ?></textarea></td>
                </tr>
                <tr>
                    <td>Bukti Bayar</td>
                    <td>:</td>
                    <td><input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png" required>
                        <br><small>JPG/PNG, maksimal 2MB</small></td>
                </tr>
            </table>
            <button type="submit">Kirim!</button>
            <button type="reset" class="btn-abu">Reset</button>
            <a href="index.php" class="btn btn-abu">Kembali</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
