<?php
// =====================================================================
// validasi.php -> semua VALIDASI Pertemuan 6 dikumpulkan di sini sebagai FUNGSI
// (fungsi buatan = Pertemuan 5). Dipanggil di tambah.php & edit.php dengan:
//     include "validasi.php";
//
// Kenapa dibuat fungsi? Karena tambah.php dan edit.php butuh validasi yang SAMA.
// Ditulis sekali di sini, dipakai 2x -> kalau aturan berubah, cukup ubah di sini.
//
// Peta materi Pertemuan 6 -> baris di file ini:
//   Input kosong ................ empty()
//   Panjang input ............... strlen()
//   Hanya angka ................. is_numeric()
//   Format email ................ filter_var(..., FILTER_VALIDATE_EMAIL)
//   Hanya huruf (regex) ......... preg_match()
//   Simpan & tampilkan semua error  $errors[] = "...";
//   Upload: error, tipe, ukuran . $_FILES["foto"]["error" / "type" / "size"]
// =====================================================================

// Pilihan select jurusan (indexed array). Dipakai di form & validasi.
$daftar_jurusan = ["Teknik Informatika", "Sistem Informasi", "Desain Komunikasi Visual"];

// ---------------------------------------------------------------------
// Validasi data teks. $data = associative array dari form.
// Return: array error (kosong = valid)
// ---------------------------------------------------------------------
function validasiMahasiswa($data)
{
    global $daftar_jurusan; // pakai variabel di luar fungsi
    $errors = [];

    // NIM: wajib, angka, tepat 8 digit
    if (empty($data["nim"])) {
        $errors[] = "NIM wajib diisi.";
    } else if (!is_numeric($data["nim"])) {
        $errors[] = "NIM hanya boleh berisi angka.";
    } else if (strlen($data["nim"]) != 8) {
        $errors[] = "NIM harus tepat 8 digit.";
    }

    // Nama: wajib, 3-50 karakter, hanya huruf & spasi
    if (empty($data["nama"])) {
        $errors[] = "Nama wajib diisi.";
    } else if (strlen($data["nama"]) < 3 || strlen($data["nama"]) > 50) {
        $errors[] = "Nama kurang dari 3 atau lebih dari 50 karakter!";
    } else if (!preg_match("/^[a-zA-Z\s]+$/", $data["nama"])) {
        $errors[] = "Nama hanya boleh berisi huruf dan spasi.";
    }

    // Email: wajib, maks 50 karakter, format valid
    if (empty($data["email"])) {
        $errors[] = "Email wajib diisi.";
    } else if (strlen($data["email"]) > 50) {
        $errors[] = "Email lebih dari 50 karakter!";
    } else if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Jenis kelamin (radio): wajib & hanya L / P
    if (empty($data["jk"])) {
        $errors[] = "Jenis kelamin wajib dipilih.";
    } else if (!in_array($data["jk"], ["L", "P"])) {
        $errors[] = "Jenis kelamin tidak valid.";
    }

    // Jurusan (select): wajib & harus salah satu pilihan
    if (empty($data["jurusan"])) {
        $errors[] = "Jurusan wajib dipilih.";
    } else if (!in_array($data["jurusan"], $daftar_jurusan)) {
        $errors[] = "Jurusan tidak valid.";
    }

    // Tanggal lahir: wajib & format tanggal benar
    if (empty($data["tgl_lahir"])) {
        $errors[] = "Tanggal lahir wajib diisi.";
    } else if (strtotime($data["tgl_lahir"]) === false) {
        $errors[] = "Format tanggal lahir tidak valid.";
    }

    // Alamat (textarea): boleh kosong, maks 200 karakter
    if (strlen($data["alamat"]) > 200) {
        $errors[] = "Alamat maksimal 200 karakter.";
    }

    return $errors;
}

// ---------------------------------------------------------------------
// Validasi upload foto (persis pola slide Pertemuan 6)
// $wajib = true  -> saat tambah (harus upload)
// $wajib = false -> saat edit (boleh kosong = pakai foto lama)
// ---------------------------------------------------------------------
function validasiFoto($foto, $wajib)
{
    $allowed_types = ["image/jpeg", "image/png"];
    $max_size      = 2 * 1024 * 1024; // 2MB
    $errors        = [];

    // UPLOAD_ERR_NO_FILE = user tidak memilih file
    if ($foto["error"] == UPLOAD_ERR_NO_FILE) {
        if ($wajib) {
            $errors[] = "Foto wajib diunggah.";
        }
        return $errors;
    }

    if ($foto["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Gagal mengunggah file.";
    } elseif (!in_array($foto["type"], $allowed_types)) {
        $errors[] = "Hanya file JPG dan PNG yang diizinkan.";
    } elseif ($foto["size"] > $max_size) {
        $errors[] = "Ukuran file maksimum 2MB.";
    }

    return $errors;
}

// Pindahkan file ke folder uploads/. Return nama file baru, atau false kalau gagal.
function uploadFoto($foto)
{
    // uniqid() supaya nama tidak bentrok; spasi diganti _ supaya aman di URL
    $nama_upload = uniqid() . "-" . str_replace(" ", "_", basename($foto["name"]));
    $target_path = "uploads/" . $nama_upload;

    if (move_uploaded_file($foto["tmp_name"], $target_path)) {
        return $nama_upload;
    }
    return false;
}

// Hapus file lama dari folder uploads/ (dipakai saat edit ganti foto & saat hapus data)
function hapusFoto($nama_file)
{
    if (!empty($nama_file) && file_exists("uploads/" . $nama_file)) {
        unlink("uploads/" . $nama_file);
    }
}
?>
