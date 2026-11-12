<?php
// =====================================================================
// fungsi.php
// Kumpulan FUNGSI BUATAN (user-defined function) - materi Pertemuan 5.
// Di-include di halaman yang butuh:  include "fungsi.php";
//
// Isi file ini:
//   1. Data array tetap (indexed array)        -> $daftar_gejala, $daftar_status
//   2. Fungsi tampilan (if, switch, match)     -> kategoriUsia(), labelStatus(), namaHari(), ...
//   3. Fungsi validasi form (Pertemuan 6)      -> validasiPasien(), validasiFile()
//   4. Fungsi upload file (Pertemuan 6)        -> uploadFile(), hapusFile()
// =====================================================================


// ---------------------------------------------------------------------
// 1. DATA TETAP (Indexed Array)
// ---------------------------------------------------------------------
// Pilihan checkbox gejala (sama seperti Tugas 1)
$daftar_gejala = ["Pusing", "Sakit Kepala", "Flu", "Demam"];

// Pilihan status kunjungan (sama dengan ENUM di database)
$daftar_status = ["Menunggu", "Diperiksa", "Selesai"];


// ---------------------------------------------------------------------
// 2. FUNGSI TAMPILAN
// ---------------------------------------------------------------------

// Menentukan kategori usia -> latihan IF - ELSE IF - ELSE (Pertemuan 4, Latihan 2)
function kategoriUsia($umur)
{
    if ($umur <= 12) {
        return "Anak-anak";
    } else if ($umur <= 17) {
        return "Remaja";
    } else if ($umur <= 59) {
        return "Dewasa";
    } else {
        return "Lansia";
    }
}

// Menentukan class CSS badge status -> contoh SWITCH (Pertemuan 4)
function labelStatus($status)
{
    switch ($status) {
        case "Menunggu":
            $kelas = "badge-kuning";
            break;
        case "Diperiksa":
            $kelas = "badge-biru";
            break;
        case "Selesai":
            $kelas = "badge-hijau";
            break;
        default:
            $kelas = "badge-abu";
    }
    return "<span class='badge $kelas'>$status</span>";
}

// Mengubah tanggal menjadi nama hari -> contoh MATCH (Pertemuan 4)
// date("N") menghasilkan 1 (Senin) sampai 7 (Minggu)
function namaHari($tanggal)
{
    $indexHari = (int) date("N", strtotime($tanggal));

    $namaHari = match ($indexHari) {
        1 => "Senin",
        2 => "Selasa",
        3 => "Rabu",
        4 => "Kamis",
        5 => "Jumat",
        6 => "Sabtu",
        7 => "Minggu",
        default => "Indeks tidak valid",
    };
    return $namaHari;
}

// Mengubah "2026-11-05" menjadi "05-11-2026" -> fungsi waktu strtotime() & date()
function formatTanggal($tanggal)
{
    return date("d-m-Y", strtotime($tanggal));
}

// Mengubah 50000 menjadi "Rp 50.000" -> fungsi angka number_format()
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ",", ".");
}

// Mengubah kode L/P menjadi teks -> operator ternary (if singkat)
function labelJk($jk)
{
    return ($jk == "L") ? "Laki-laki" : "Perempuan";
}

// ---------------------------------------------------------------------
// FUNGSI PERHITUNGAN BIAYA
// Polanya SAMA dengan Latihan 4 Pertemuan 4 (hitung gaji karyawan):
//   Gaji pokok (jabatan)        -> Biaya pokok   = tarif poli (dari database)
//   Tunjangan (pendidikan)      -> Biaya gejala  = jumlah biaya tiap gejala (switch)
//   Honor lembur (lama kerja)   -> Diskon usia   = persen berdasarkan kategori usia (if/else)
// Hasil perhitungan TIDAK disimpan di database, cukup dihitung saat ditampilkan.
// ---------------------------------------------------------------------

// Biaya pemeriksaan per gejala -> SWITCH
function biayaGejala($gejala)
{
    switch ($gejala) {
        case "Pusing":
            return 15000;
        case "Sakit Kepala":
            return 20000;
        case "Flu":
            return 25000;
        case "Demam":
            return 30000;
        default:
            return 0;
    }
}

// Persen diskon berdasarkan kategori usia -> IF ELSE
function persenDiskon($umur)
{
    $kategori = kategoriUsia($umur); // fungsi memanggil fungsi lain
    if ($kategori == "Anak-anak") {
        return 20;
    } else if ($kategori == "Lansia") {
        return 30;
    } else {
        return 0;
    }
}

// Menghitung rincian biaya 1 pasien.
// $gejala_str = "Pusing,Flu" (format dari database)
// Return: associative array berisi rincian + total
function hitungBiaya($tarif_poli, $gejala_str, $umur)
{
    // Biaya gejala: jumlahkan dengan perulangan foreach
    $biaya_gejala = 0;
    foreach (explode(",", $gejala_str) as $g) {
        $biaya_gejala += biayaGejala($g); // operator assignment +=
    }

    $subtotal = $tarif_poli + $biaya_gejala;
    $persen   = persenDiskon($umur);
    $diskon   = round($subtotal * $persen / 100);

    return [
        "biaya_pokok"  => $tarif_poli,
        "biaya_gejala" => $biaya_gejala,
        "subtotal"     => $subtotal,
        "persen"       => $persen,
        "diskon"       => $diskon,
        "total"        => $subtotal - $diskon,
    ];
}

// Membuat nomor registrasi unik, contoh: REG-20261112-4821
// -> fungsi waktu date() + fungsi angka rand() + penggabungan string (concat)
function buatNoRegistrasi()
{
    return "REG-" . date("Ymd") . "-" . rand(1000, 9999);
}


// ---------------------------------------------------------------------
// 3. FUNGSI VALIDASI (Pertemuan 6)
// ---------------------------------------------------------------------
// Parameter:
//   $data  = array data input ($nama, $umur, dst - sudah di-trim)
//   $mode  = "tambah" atau "ubah" (parameter OPSIONAL / default parameter)
// Return:
//   array berisi pesan error. Kalau kosong berarti data VALID.
function validasiPasien($data, $daftar_poli_id, $mode = "tambah")
{
    // global = supaya variabel di luar fungsi bisa dipakai di dalam fungsi
    global $daftar_gejala, $daftar_status;

    $errors = [];

    // --- Nama: wajib, 3-50 karakter, hanya huruf & spasi ---
    if (empty($data["nama"])) {
        $errors[] = "Nama wajib diisi.";
    } else {
        if (strlen($data["nama"]) < 3 || strlen($data["nama"]) > 50) {
            $errors[] = "Nama kurang dari 3 atau lebih dari 50 karakter!";
        }
        if (!preg_match("/^[a-zA-Z\s]+$/", $data["nama"])) {
            $errors[] = "Nama hanya boleh berisi huruf dan spasi.";
        }
    }

    // --- Umur: wajib, angka, 0-120 ---
    // PERHATIAN: empty("0") bernilai TRUE! Jadi umur 0 (bayi) akan dianggap kosong.
    // Karena itu di sini pakai perbandingan  === ""  bukan empty().
    if ($data["umur"] === "") {
        $errors[] = "Umur wajib diisi.";
    } else if (!is_numeric($data["umur"])) {
        $errors[] = "Umur harus berupa angka.";
    } else if ($data["umur"] < 0 || $data["umur"] > 120) {
        $errors[] = "Umur harus antara 0 sampai 120 tahun.";
    }

    // --- Email: wajib, max 50 karakter, format valid ---
    if (empty($data["email"])) {
        $errors[] = "Email wajib diisi.";
    } else {
        if (strlen($data["email"]) > 50) {
            $errors[] = "Email lebih dari 50 karakter!";
        }
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid.";
        }
    }

    // --- Nomor HP: wajib, angka, 10-13 digit (Latihan Pendaftaran Event P6) ---
    if (empty($data["no_hp"])) {
        $errors[] = "Nomor HP wajib diisi.";
    } else if (!is_numeric($data["no_hp"]) || !preg_match("/^[0-9]+$/", $data["no_hp"])) {
        // is_numeric("1e5") = true, jadi ditambah preg_match supaya benar-benar hanya digit
        $errors[] = "Nomor HP hanya boleh berisi angka.";
    } else if (strlen($data["no_hp"]) < 10 || strlen($data["no_hp"]) > 13) {
        $errors[] = "Nomor HP harus antara 10 hingga 13 digit.";
    }

    // --- PIN: 4-6 digit angka. Wajib saat tambah, opsional saat ubah ---
    if ($mode == "tambah" && empty($data["pin"])) {
        $errors[] = "PIN wajib diisi.";
    }
    if (!empty($data["pin"]) && !preg_match("/^[0-9]{4,6}$/", $data["pin"])) {
        $errors[] = "PIN harus 4 sampai 6 digit angka.";
    }

    // --- Jenis kelamin (radio): wajib & harus L atau P ---
    if (empty($data["jk"])) {
        $errors[] = "Jenis kelamin wajib dipilih.";
    } else if (!in_array($data["jk"], ["L", "P"])) {
        $errors[] = "Jenis kelamin tidak valid.";
    }

    // --- Gejala (checkbox = array): minimal 1, dan isinya harus dari daftar ---
    if (count($data["gejala"]) == 0) {
        $errors[] = "Pilih minimal 1 gejala.";
    } else {
        foreach ($data["gejala"] as $g) {
            if (!in_array($g, $daftar_gejala)) {
                $errors[] = "Gejala '" . htmlspecialchars($g) . "' tidak dikenal.";
            }
        }
    }

    // --- Poli (select): wajib & harus ada di database ---
    if (empty($data["poli_id"])) {
        $errors[] = "Poli tujuan wajib dipilih.";
    } else if (!in_array($data["poli_id"], $daftar_poli_id)) {
        $errors[] = "Poli tujuan tidak ditemukan.";
    }

    // --- Deskripsi (textarea): opsional, max 500 karakter ---
    if (strlen($data["deskripsi"]) > 500) {
        $errors[] = "Deskripsi maksimal 500 karakter.";
    }

    // --- Tanggal kunjungan: wajib, format tanggal valid ---
    if (empty($data["tanggal_kunjungan"])) {
        $errors[] = "Tanggal kunjungan wajib diisi.";
    } else if (strtotime($data["tanggal_kunjungan"]) === false) {
        $errors[] = "Format tanggal tidak valid.";
    } else if ($mode == "tambah" && $data["tanggal_kunjungan"] < date("Y-m-d")) {
        // String "Y-m-d" bisa langsung dibandingkan karena urutannya tahun-bulan-hari
        $errors[] = "Tanggal kunjungan tidak boleh sebelum hari ini.";
    }

    // --- Status: hanya dicek saat ubah ---
    if ($mode == "ubah" && !in_array($data["status"], $daftar_status)) {
        $errors[] = "Status tidak valid.";
    }

    return $errors;
}

// Validasi file upload (Pertemuan 6: "Membuat Form Upload" + validasi)
// $wajib = true  -> file harus diupload (saat tambah)
// $wajib = false -> boleh tidak upload (saat ubah, file lama dipakai)
function validasiFile($file, $wajib = true)
{
    $allowed_types = ["image/jpeg", "image/png"];
    $allowed_ext   = ["jpg", "jpeg", "png"];
    $max_size      = 2 * 1024 * 1024; // 2MB
    $errors        = [];

    // UPLOAD_ERR_NO_FILE (kode 4) = user tidak memilih file
    if ($file["error"] == UPLOAD_ERR_NO_FILE) {
        if ($wajib) {
            $errors[] = "Bukti bayar wajib diunggah.";
        }
        return $errors; // tidak ada file, tidak perlu cek lanjut
    }

    // pathinfo() mengambil ekstensi file, contoh "foto.PNG" -> "PNG" -> strtolower -> "png"
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Gagal mengunggah file.";
    } elseif (!in_array($file["type"], $allowed_types) || !in_array($ext, $allowed_ext)) {
        $errors[] = "Hanya file JPG dan PNG yang diizinkan.";
    } elseif ($file["size"] > $max_size) {
        $errors[] = "Ukuran file maksimum 2MB.";
    }

    return $errors;
}


// ---------------------------------------------------------------------
// 4. FUNGSI UPLOAD
// ---------------------------------------------------------------------

// Memindahkan file dari folder sementara (tmp) ke folder uploads/
// Return: nama file baru (string) kalau berhasil, false kalau gagal
function uploadFile($file)
{
    // uniqid() supaya nama file tidak bentrok kalau ada 2 file dengan nama sama
    // Spasi diganti "_" supaya aman dipakai di URL
    $nama_upload = uniqid() . "-" . str_replace(" ", "_", basename($file["name"]));
    $target_path = "uploads/" . $nama_upload;

    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return $nama_upload;
    }
    return false;
}

// Menghapus file lama dari folder uploads/ (dipakai saat ubah & hapus)
function hapusFile($nama_file)
{
    // file_exists() cek dulu filenya ada, baru unlink() (hapus file)
    if (!empty($nama_file) && file_exists("uploads/" . $nama_file)) {
        unlink("uploads/" . $nama_file);
    }
}
?>
