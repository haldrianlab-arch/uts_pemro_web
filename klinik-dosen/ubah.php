<?php
// =====================================================================
// ubah.php  ->  UPDATE (mengubah data)
// Dibuka dari index.php lewat link:  ubah.php?id=3
// Alur:
//   1. Ambil id dari URL ($_GET["id"]), cari datanya di database (SELECT)
//   2. Isi form dengan data lama
//   3. Saat disubmit (POST): validasi -> (upload file baru) -> UPDATE
// Perbedaan dengan tambah.php:
//   - PIN & Bukti Bayar boleh dikosongkan (artinya: pakai yang lama)
//   - Ada pilihan Status
// =====================================================================
include "koneksi.php";
include "fungsi.php";

// (int) memaksa id jadi angka -> ubah.php?id=1 OR 1=1 akan jadi 1 (aman)
$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

// 1. Ambil data lama
$sql    = "SELECT * FROM pasien WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location = 'index.php';</script>";
    exit();
}
$data_lama = $result->fetch_assoc();

// Daftar poli untuk <select>
$daftar_poli    = [];
$daftar_poli_id = [];
$res_poli = $conn->query("SELECT * FROM poli ORDER BY nama_poli");
while ($row = $res_poli->fetch_assoc()) {
    $daftar_poli[]    = $row;
    $daftar_poli_id[] = $row["id"];
}

// 2. Isi variabel form dengan data lama
$nama              = $data_lama["nama"];
$umur              = $data_lama["umur"];
$email             = $data_lama["email"];
$no_hp             = $data_lama["no_hp"];
$jk                = $data_lama["jenis_kelamin"];
$gejala            = explode(",", $data_lama["gejala"]); // "Pusing,Flu" -> ["Pusing","Flu"]
$poli_id           = $data_lama["poli_id"];
$deskripsi         = $data_lama["deskripsi"];
$tanggal_kunjungan = $data_lama["tanggal_kunjungan"];
$status            = $data_lama["status"];
$errors            = [];

// 3. Proses form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama              = trim($_POST["nama"]);
    $umur              = trim($_POST["umur"]);
    $email             = trim($_POST["email"]);
    $no_hp             = trim($_POST["no_hp"]);
    $pin              = trim($_POST["pin"]);
    $jk                = isset($_POST["jk"]) ? $_POST["jk"] : "";
    $gejala            = isset($_POST["gejala"]) ? $_POST["gejala"] : [];
    $poli_id           = $_POST["poli_id"];
    $deskripsi         = trim($_POST["deskripsi"]);
    $tanggal_kunjungan = $_POST["tanggal_kunjungan"];
    $status            = $_POST["status"];
    $file              = $_FILES["bukti_bayar"];

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
        "status"            => $status,
    ];

    // mode "ubah": PIN opsional, file opsional (false = tidak wajib)
    $errors = array_merge(
        validasiPasien($data, $daftar_poli_id, "ubah"),
        validasiFile($file, false)
    );

    if (empty($errors)) {
        // --- File: kalau user upload file baru, ganti. Kalau tidak, pakai yang lama ---
        $nama_file = $data_lama["bukti_bayar"];
        $file_baru = false;

        if ($file["error"] == UPLOAD_ERR_OK) {
            $nama_file = uploadFile($file);
            $file_baru = true;
            if ($nama_file === false) {
                $errors[] = "Gagal menyimpan file.";
            }
        }
    }

    if (empty($errors)) {
        $nama_aman      = $conn->real_escape_string(ucwords(strtolower($nama)));
        $email_aman     = $conn->real_escape_string(strtolower($email));
        $no_hp_aman     = $conn->real_escape_string($no_hp);
        $gejala_aman    = $conn->real_escape_string(implode(",", $gejala));
        $deskripsi_aman = $conn->real_escape_string($deskripsi);
        $tanggal_aman   = $conn->real_escape_string($tanggal_kunjungan);
        $file_aman      = $conn->real_escape_string($nama_file);
        $umur_int       = (int) $umur;
        $poli_int       = (int) $poli_id;

        // Susun query UPDATE. Operator .= untuk menyambung string (Pertemuan 4)
        $sqlupdate  = "UPDATE pasien SET ";
        $sqlupdate .= "nama = '$nama_aman', ";
        $sqlupdate .= "umur = $umur_int, ";
        $sqlupdate .= "email = '$email_aman', ";
        $sqlupdate .= "no_hp = '$no_hp_aman', ";
        $sqlupdate .= "jenis_kelamin = '$jk', ";
        $sqlupdate .= "gejala = '$gejala_aman', ";
        $sqlupdate .= "poli_id = $poli_int, ";
        $sqlupdate .= "deskripsi = '$deskripsi_aman', ";
        $sqlupdate .= "tanggal_kunjungan = '$tanggal_aman', ";
        $sqlupdate .= "status = '$status', ";
        $sqlupdate .= "bukti_bayar = '$file_aman' ";

        // PIN hanya di-update kalau diisi
        if (!empty($pin)) {
            $pin_hash   = password_hash($pin, PASSWORD_DEFAULT);
            $sqlupdate .= ", pin = '$pin_hash' ";
        }
        $sqlupdate .= "WHERE id = $id";

        if ($conn->query($sqlupdate) === TRUE) {
            // Update berhasil + ada file baru -> file lama dihapus supaya tidak menumpuk
            if ($file_baru) {
                hapusFile($data_lama["bukti_bayar"]);
            }
            echo "<script>
                    alert('Data pasien berhasil diubah');
                    window.location = 'detail.php?id=$id';
                  </script>";
            exit();
        } else {
            if ($file_baru) {
                hapusFile($nama_file); // gagal update -> buang file baru
            }
            $errors[] = "Gagal menyimpan: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Data Pasien - Klinik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <span class="logo">Klinik Sehat</span>
        <a href="index.php">Data Pasien</a>
        <a href="tambah.php">+ Registrasi</a>
        <a href="poli.php">Data Poli</a>
        <a href="latihan.php">Latihan</a>
    </nav>

    <div class="container">
        <h1>Ubah Data Pasien</h1>
        <p>No. Registrasi: <b><?php echo $data_lama["no_registrasi"]; ?></b></p>

        <?php
        if (!empty($errors)) {
            echo "<div class='alert alert-error'><b>Data belum valid:</b><ul>";
            foreach ($errors as $err) {
                echo "<li>" . $err . "</li>";
            }
            echo "</ul></div>";
        }
        ?>

        <!-- action mengarah ke file ini lagi + membawa ?id= supaya id tidak hilang -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>"
                               required minlength="3" maxlength="50" pattern="[a-zA-Z\s]+"></td>
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
                    <td><input type="tel" name="no_hp" value="<?php echo htmlspecialchars($no_hp); ?>"
                               required pattern="[0-9]{10,13}" title="10-13 digit angka"></td>
                </tr>
                <tr>
                    <td>PIN Baru</td>
                    <td>:</td>
                    <td><input type="password" name="pin" pattern="[0-9]{4,6}" placeholder="Kosongkan jika tidak diganti"></td>
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
                        foreach ($daftar_gejala as $g) {
                            // in_array: kalau gejala ini ada di data lama -> centang
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
                               value="<?php echo htmlspecialchars($tanggal_kunjungan); ?>"></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>
                        <select name="status">
                            <?php foreach ($daftar_status as $s) { ?>
                                <option value="<?php echo $s; ?>" <?php echo ($status == $s) ? "selected" : ""; ?>><?php echo $s; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Deskripsi</td>
                    <td>:</td>
                    <td><textarea name="deskripsi" rows="3" maxlength="500"><?php echo htmlspecialchars($deskripsi); ?></textarea></td>
                </tr>
                <tr>
                    <td>Bukti Bayar</td>
                    <td>:</td>
                    <td>
                        <?php if (!empty($data_lama["bukti_bayar"])) { ?>
                            <img src="uploads/<?php echo htmlspecialchars($data_lama["bukti_bayar"]); ?>" class="thumb" alt="Bukti bayar lama"><br>
                        <?php } else { ?>
                            <small>(belum ada file)</small><br>
                        <?php } ?>
                        <input type="file" name="bukti_bayar" accept=".jpg,.jpeg,.png">
                        <br><small>Kosongkan jika tidak diganti. JPG/PNG, maks 2MB</small>
                    </td>
                </tr>
            </table>
            <button type="submit">Update</button>
            <a href="index.php" class="btn btn-abu">Batal</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
