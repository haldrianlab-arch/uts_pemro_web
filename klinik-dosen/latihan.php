<?php
// =====================================================================
// latihan.php  ->  KUMPULAN JAWABAN LATIHAN DI SLIDE (non-CRUD)
//
// Bukan bagian CRUD, tapi logikanya bisa muncul DI DALAM soal CRUD,
// misalnya "CRUD karyawan + tampilkan total gaji" (Latihan 4 P4).
//
// Isi:
//   P4 Latihan 1  Volume tabung               -> operator aritmatika, M_PI, pow()
//   P4 Latihan 3  Jenis segitiga              -> if - else if, operator logika && ||
//   P4 Latihan 4  Gaji karyawan               -> switch, match, if - else if
//   P5 Latihan 1  Pengolahan string           -> strtoupper, strtolower, ucwords, strlen
//   P5 Latihan 2  Kalkulator kapasitas AC     -> if - else if berantai
//   P5 Latihan 3  Tahun kabisat               -> function + for + tabel
//   (P4 Latihan 2 kategori usia ada di fungsi.php -> kategoriUsia())
//
// Semua form mengirim ke halaman ini sendiri (POST). Input hidden "latihan"
// menandai form mana yang dikirim, lalu diproses dengan switch.
// =====================================================================


// ---------------------------------------------------------------------
// FUNGSI-FUNGSI LATIHAN
// ---------------------------------------------------------------------

// P4 Latihan 1: volume = pi x r^2 x t
function volumeTabung($jari_jari, $tinggi)
{
    return M_PI * pow($jari_jari, 2) * $tinggi;
}

// P4 Latihan 3: jenis segitiga
function jenisSegitiga($a, $b, $c)
{
    // Ketidaksamaan segitiga: jumlah 2 sisi harus LEBIH BESAR dari sisi ketiga
    if ($a <= 0 || $b <= 0 || $c <= 0 || $a + $b <= $c || $a + $c <= $b || $b + $c <= $a) {
        return "Tidak valid";
    } else if ($a == $b && $b == $c) {
        return "Sama sisi";
    } else if ($a == $b || $b == $c || $a == $c) {
        return "Sama kaki";
    } else {
        return "Sembarang";
    }
}

// P4 Latihan 4: gaji karyawan
function gajiPokok($jabatan)
{
    switch ($jabatan) {
        case "M":
            return 15000000;
        case "S":
            return 10000000;
        case "K":
            return 7500000;
        default:
            return 0;
    }
}

function tunjanganPendidikan($pendidikan)
{
    return match ($pendidikan) {
        "SMA"   => 1000000,
        "D3"    => 2000000,
        "S1"    => 3000000,
        default => 0,
    };
}

function honorLemburPerJam($lama_kerja)
{
    if ($lama_kerja <= 5) {
        return 50000;
    } else if ($lama_kerja <= 10) {
        return 100000;
    } else {
        return 150000;
    }
}

// Format rupiah: 15000000 -> "Rp15.000.000"
function formatRp($angka)
{
    return "Rp" . number_format($angka, 0, ",", ".");
}

// P5 Latihan 2: BTU -> PK
function kapasitasAC($btu)
{
    if ($btu <= 5000) {
        return "0.5 PK";
    } else if ($btu <= 7000) {
        return "0.75 PK";
    } else if ($btu <= 9000) {
        return "1 PK";
    } else if ($btu <= 12000) {
        return "1.5 PK";
    } else if ($btu <= 18000) {
        return "2 PK";
    } else if ($btu <= 24000) {
        return "2.5 PK";
    } else {
        return "Lebih dari 2.5 PK (butuh lebih dari 1 AC)";
    }
}

// P5 Latihan 3: tahun kabisat, mengikuti pseudocode di slide
function isKabisat($tahun)
{
    if ($tahun % 400 == 0) {
        return true;
    } else if ($tahun % 100 == 0) {
        return false;
    } else if ($tahun % 4 == 0) {
        return true;
    } else {
        return false;
    }
}


// ---------------------------------------------------------------------
// PROSES FORM
// ---------------------------------------------------------------------
$hasil  = [];   // hasil tiap latihan, key = nama latihan
$errors = [];   // error tiap latihan, key = nama latihan
$input  = $_POST; // supaya isian tetap tampil (sticky)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $latihan = $_POST["latihan"];

    switch ($latihan) {
        case "tabung":
            $r = $_POST["jari_jari"];
            $t = $_POST["tinggi"];
            if (!is_numeric($r) || !is_numeric($t) || $r <= 0 || $t <= 0) {
                $errors["tabung"] = "Jari-jari dan tinggi harus angka lebih dari 0.";
            } else {
                $hasil["tabung"] = "Volume tabung = " . round(volumeTabung($r, $t), 2) . " cm³";
            }
            break;

        case "segitiga":
            $a = $_POST["a"];
            $b = $_POST["b"];
            $c = $_POST["c"];
            if (!is_numeric($a) || !is_numeric($b) || !is_numeric($c)) {
                $errors["segitiga"] = "Semua sisi harus angka.";
            } else {
                $hasil["segitiga"] = "Jenis segitiga: " . jenisSegitiga($a, $b, $c);
            }
            break;

        case "gaji":
            $jabatan    = $_POST["jabatan"];
            $pendidikan = $_POST["pendidikan"];
            $lama_kerja = $_POST["lama_kerja"];
            $jam_lembur = $_POST["jam_lembur"];

            if (!is_numeric($lama_kerja) || !is_numeric($jam_lembur) || $lama_kerja < 0 || $jam_lembur < 0) {
                $errors["gaji"] = "Lama kerja dan jam lembur harus angka, tidak boleh negatif.";
            } else {
                $pokok    = gajiPokok($jabatan);
                $tambahan = tunjanganPendidikan($pendidikan);
                $lembur   = honorLemburPerJam($lama_kerja) * $jam_lembur;
                // associative array berisi rincian
                $hasil["gaji"] = [
                    "Gaji pokok"          => $pokok,
                    "Gaji Tambahan"       => $tambahan,
                    "Gaji Lembur"         => $lembur,
                    "Total Gaji Karyawan" => $pokok + $tambahan + $lembur,
                ];
            }
            break;

        case "string":
            $nama = trim($_POST["nama"]);
            if (empty($nama)) {
                $errors["string"] = "Nama wajib diisi.";
            } else {
                $hasil["string"] = [
                    "Teks asli"          => $nama,
                    "Huruf besar"        => strtoupper($nama),
                    "Huruf kecil"        => strtolower($nama),
                    "Kapital awal kata"  => ucwords(strtolower($nama)),
                    "Jml Karakter"       => strlen($nama),
                ];
            }
            break;

        case "ac":
            $panjang = $_POST["panjang"];
            $lebar   = $_POST["lebar"];
            if (!is_numeric($panjang) || !is_numeric($lebar) || $panjang <= 0 || $lebar <= 0) {
                $errors["ac"] = "Panjang dan lebar harus angka lebih dari 0.";
            } else {
                $luas = $panjang * $lebar;
                $btu  = $luas * 500;
                $hasil["ac"] = [
                    "Luas Ruangan"    => $luas . " m²",
                    "BTU/h"           => $btu,
                    "Rekomendasi AC"  => kapasitasAC($btu),
                ];
            }
            break;

        case "kabisat":
            $mulai = $_POST["mulai"];
            $akhir = $_POST["akhir"];
            if (!is_numeric($mulai) || !is_numeric($akhir)) {
                $errors["kabisat"] = "Tahun harus angka.";
            } else if ($mulai > $akhir) {
                $errors["kabisat"] = "Tahun mulai tidak boleh lebih besar dari tahun akhir.";
            } else if ($akhir - $mulai > 200) {
                $errors["kabisat"] = "Rentang maksimal 200 tahun.";
            } else {
                $hasil["kabisat"] = [];
                for ($tahun = (int) $mulai; $tahun <= (int) $akhir; $tahun++) {
                    $hasil["kabisat"][$tahun] = isKabisat($tahun);
                }
            }
            break;
    }
}

// Helper kecil untuk mengisi ulang input (sticky form)
function nilai($key)
{
    global $input;
    return isset($input[$key]) ? htmlspecialchars($input[$key]) : "";
}

// Helper untuk menampilkan error/hasil berbentuk teks
function tampilPesan($key)
{
    global $errors, $hasil;
    if (isset($errors[$key])) {
        echo "<div class='alert alert-error'>" . $errors[$key] . "</div>";
    } else if (isset($hasil[$key]) && !is_array($hasil[$key])) {
        echo "<div class='alert alert-sukses'>" . $hasil[$key] . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kumpulan Latihan - Klinik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <span class="logo">Klinik Sehat</span>
        <a href="index.php">Data Pasien</a>
        <a href="tambah.php">+ Registrasi</a>
        <a href="poli.php">Data Poli</a>
        <a href="latihan.php" class="aktif">Latihan</a>
    </nav>

    <div class="container">
        <h1>Kumpulan Latihan dari Slide</h1>
        <p>Jawaban latihan Pertemuan 4 &amp; 5 yang bukan CRUD. Logikanya sering dipakai sebagai "kolom hitungan" di soal CRUD.</p>

        <div class="latihan-grid">

            <!-- ========== P4 Latihan 1 ========== -->
            <section class="latihan-box">
                <h3>P4 Latihan 1 &mdash; Volume Tabung</h3>
                <form method="post">
                    <input type="hidden" name="latihan" value="tabung">
                    <label>Jari-jari (cm)</label><br>
                    <input type="number" step="any" name="jari_jari" value="<?php echo nilai("jari_jari"); ?>" required><br>
                    <label>Tinggi (cm)</label><br>
                    <input type="number" step="any" name="tinggi" value="<?php echo nilai("tinggi"); ?>" required><br><br>
                    <button type="submit">Hitung</button>
                </form>
                <?php tampilPesan("tabung"); ?>
            </section>

            <!-- ========== P4 Latihan 3 ========== -->
            <section class="latihan-box">
                <h3>P4 Latihan 3 &mdash; Jenis Segitiga</h3>
                <form method="post">
                    <input type="hidden" name="latihan" value="segitiga">
                    <label>Sisi a, b, c</label><br>
                    <input type="number" step="any" name="a" value="<?php echo nilai("a"); ?>" class="input-pendek" required>
                    <input type="number" step="any" name="b" value="<?php echo nilai("b"); ?>" class="input-pendek" required>
                    <input type="number" step="any" name="c" value="<?php echo nilai("c"); ?>" class="input-pendek" required><br><br>
                    <button type="submit">Cek</button>
                </form>
                <?php tampilPesan("segitiga"); ?>
            </section>

            <!-- ========== P4 Latihan 4 ========== -->
            <section class="latihan-box">
                <h3>P4 Latihan 4 &mdash; Gaji Karyawan</h3>
                <form method="post">
                    <input type="hidden" name="latihan" value="gaji">
                    <label>Jabatan</label><br>
                    <select name="jabatan">
                        <?php
                        $daftar_jabatan = ["M" => "Manager", "S" => "Supervisor", "K" => "Karyawan Biasa"];
                        foreach ($daftar_jabatan as $kode => $label) {
                            $selected = (nilai("jabatan") == $kode) ? "selected" : "";
                            echo "<option value='$kode' $selected>$label ($kode)</option>";
                        }
                        ?>
                    </select><br>
                    <label>Pendidikan</label><br>
                    <select name="pendidikan">
                        <?php
                        foreach (["SMA", "D3", "S1"] as $p) {
                            $selected = (nilai("pendidikan") == $p) ? "selected" : "";
                            echo "<option value='$p' $selected>$p</option>";
                        }
                        ?>
                    </select><br>
                    <label>Lama kerja (tahun)</label><br>
                    <input type="number" name="lama_kerja" min="0" value="<?php echo nilai("lama_kerja"); ?>" required><br>
                    <label>Jam lembur</label><br>
                    <input type="number" name="jam_lembur" min="0" value="<?php echo nilai("jam_lembur"); ?>" required><br><br>
                    <button type="submit">Hitung Gaji</button>
                </form>
                <?php
                tampilPesan("gaji");
                if (isset($hasil["gaji"])) {
                    echo "<div class='alert alert-sukses'>";
                    foreach ($hasil["gaji"] as $label => $nominal) {
                        echo "$label: " . formatRp($nominal) . "<br>";
                    }
                    echo "</div>";
                }
                ?>
                <small>Catatan: contoh tampilan di slide (lembur Rp12.000.000, total Rp19.200.000) tidak konsisten
                    &mdash; 15jt + 3jt + 12jt = 30jt. Kemungkinan maksudnya lembur 8 jam &times; Rp150.000 = Rp1.200.000.</small>
            </section>

            <!-- ========== P5 Latihan 1 ========== -->
            <section class="latihan-box">
                <h3>P5 Latihan 1 &mdash; Pengolahan String</h3>
                <form method="post">
                    <input type="hidden" name="latihan" value="string">
                    <label>Nama</label><br>
                    <input type="text" name="nama" value="<?php echo nilai("nama"); ?>" required>
                    <button type="submit">Submit</button>
                </form>
                <?php
                tampilPesan("string");
                if (isset($hasil["string"])) {
                    echo "<ul>";
                    foreach ($hasil["string"] as $label => $isi) {
                        echo "<li><b>$label:</b> " . htmlspecialchars($isi) . "</li>";
                    }
                    echo "</ul>";
                }
                ?>
            </section>

            <!-- ========== P5 Latihan 2 ========== -->
            <section class="latihan-box">
                <h3>P5 Latihan 2 &mdash; Kalkulator Kapasitas AC</h3>
                <form method="post">
                    <input type="hidden" name="latihan" value="ac">
                    <label>Panjang Ruangan (meter)</label><br>
                    <input type="number" step="any" name="panjang" value="<?php echo nilai("panjang"); ?>" required><br>
                    <label>Lebar Ruangan (meter)</label><br>
                    <input type="number" step="any" name="lebar" value="<?php echo nilai("lebar"); ?>" required><br><br>
                    <button type="submit">Hitung</button>
                </form>
                <?php
                tampilPesan("ac");
                if (isset($hasil["ac"])) {
                    echo "<h4>Hasil Perhitungan</h4>";
                    foreach ($hasil["ac"] as $label => $isi) {
                        echo "<b>$label</b>: $isi<br>";
                    }
                }
                ?>
            </section>

            <!-- ========== P5 Latihan 3 ========== -->
            <section class="latihan-box">
                <h3>P5 Latihan 3 &mdash; Tahun Kabisat</h3>
                <form method="post">
                    <input type="hidden" name="latihan" value="kabisat">
                    <label>Tahun Mulai</label>
                    <input type="number" name="mulai" value="<?php echo nilai("mulai"); ?>" class="input-pendek" required>
                    <label>Tahun Akhir</label>
                    <input type="number" name="akhir" value="<?php echo nilai("akhir"); ?>" class="input-pendek" required>
                    <button type="submit">Submit</button>
                </form>
                <?php
                tampilPesan("kabisat");
                if (isset($hasil["kabisat"])) {
                    echo "<table class='tabel-kabisat'><tr><th>Tahun</th><th>Kabisat</th></tr>";
                    foreach ($hasil["kabisat"] as $tahun => $kabisat) {
                        // Baris kabisat diberi class khusus (warna biru seperti contoh slide)
                        $kelas = $kabisat ? "class='kabisat'" : "";
                        $teks  = $kabisat ? "Kabisat" : "Bukan Kabisat";
                        echo "<tr $kelas><td>$tahun</td><td>$teks</td></tr>";
                    }
                    echo "</table>";
                }
                ?>
            </section>

        </div>
    </div>
</body>
</html>
