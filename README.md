# Persiapan Ujian Praktik CRUD — Pemrograman Web

Dua project dengan **fitur yang sama persis** (Registrasi Pasien Klinik, dikembangkan dari Tugas 1), ditulis dengan dua gaya:

| Folder | Gaya | Kapan dipakai |
|---|---|---|
| [`klinik-dosen/`](klinik-dosen/) | Prosedural, `mysqli`, 1 file = 1 halaman, mirip contoh dosen | **Untuk ujian.** Tulis seperti ini. |
| [`klinik-modern/`](klinik-modern/) | PDO + prepared statement, MVC sederhana, enum, CSRF, PRG | Untuk paham "cara yang benar" di dunia kerja |

> Saran belajar: kuasai `klinik-dosen` dulu sampai bisa menulis ulang tanpa melihat. Baru buka `klinik-modern` untuk membandingkan.

---

## 1. Hasil analisis materi & style dosen

### Materi yang sudah diajarkan
| Pertemuan | Materi | Dipakai di project |
|---|---|---|
| 1 | HTML dasar, struktur dokumen | Semua halaman |
| 3 | CSS: inline/internal/**external**, selector, box model, **flexbox**, pseudo-class | `style.css` |
| 4 | PHP dasar: variabel, operator, concat `.` `.=`, **if/else if**, **switch**, **match** | `fungsi.php` → `kategoriUsia()`, `labelStatus()`, `namaHari()` |
| 5 | **GET vs POST**, perulangan (for, while, foreach), fungsi bawaan & buatan, array (indexed/assoc/multi), string | Pencarian GET, pagination `for`, `while fetch_assoc`, `explode/implode` |
| 6 | Form handling, `REQUEST_METHOD`, validasi (`empty`, `strlen`, `is_numeric`, `filter_var`, `preg_match`), **array `$errors`**, upload `$_FILES` + validasi | `tambah.php`, `ubah.php`, `validasiPasien()`, `validasiFile()`, `uploadFile()` |
| Tugas 1 | Form registrasi klinik (text, number, email, password, radio, checkbox, select, textarea, file) | Field-field tabel `pasien` |
| Latihan 4 (P5) | Tabel warna `#81C784`, `#A5D6A7`, `#EEE`, `#E0E0E0` | Warna tabel di `style.css` |

### Pemetaan SEMUA tugas & latihan di slide → kode

| Sumber | Soal | Di mana jawabannya |
|---|---|---|
| Tugas 1 | Form Registrasi Klinik | Tema utama: `tambah.php` / `ubah.php` (semua field) |
| Tugas 2 | Portofolio HTML + CSS | Tidak dimasukkan (bukan CRUD); teknik CSS-nya ada di `style.css` |
| P1 Latihan | Form: text, password, radio, checkbox, select, textarea | Form registrasi pasien |
| P3 Latihan | (slide kosong — soal diberikan di kelas) | — |
| P4 Latihan 1 | Volume tabung | `latihan.php` → `volumeTabung()` |
| P4 Latihan 2 | Kategori usia (if-else) | `fungsi.php` → `kategoriUsia()`, dipakai di tabel & diskon |
| P4 Latihan 3 | Jenis segitiga | `latihan.php` → `jenisSegitiga()` |
| P4 Latihan 4 | Gaji karyawan (pokok + tunjangan + lembur) | `latihan.php` → `gajiPokok()`, `tunjanganPendidikan()`, `honorLemburPerJam()`. **Polanya juga dipakai di CRUD**: `hitungBiaya()` = tarif poli + biaya gejala − diskon usia, tampil sebagai kolom "Total Biaya" |
| P5 Latihan 1 | Pengolahan string | `latihan.php` (bagian string) + `detail.php` |
| P5 Latihan 2 | Kalkulator kapasitas AC | `latihan.php` → `kapasitasAC()` |
| P5 Latihan 3 | Tahun kabisat (function + tabel) | `latihan.php` → `isKabisat()` |
| P5 Latihan 4 | Tabel array asosiatif + warna | Warna tabel di `style.css`; data pasien contoh = nama-nama dari latihan ini |
| P6 Latihan Kontak | Form → cetak hasil di bawahnya | Pola sticky form + pesan di `tambah.php` dan `latihan.php` |
| P6 Latihan Event | No HP angka 10–13 digit, upload JPG/PNG | Kolom `no_hp` + `validasiFile()` |

> Catatan P4 Latihan 4: contoh tampilan di slide tidak konsisten (lembur Rp12.000.000 tapi total Rp19.200.000). Kemungkinan maksudnya lembur 8 jam × Rp150.000 = Rp1.200.000. Dengan input Manager, S1, kerja 12 tahun, lembur 8 jam, `latihan.php` menghasilkan total Rp19.200.000 — sama dengan slide.

Database belum muncul di slide (Pertemuan 1–6), tapi di folder `C:\xampp\htdocs\haldriansunata` ada **template CRUD dosen** (`index.php`, `ubah.php`, `hapus.php` — ada komentar `//ISI!` dan `//GANTI!`). Itu yang paling mungkin jadi acuan ujian, jadi gaya `klinik-dosen` mengikuti template itu.

### Ciri khas kode dosen (ditiru di `klinik-dosen`)
- Koneksi: `$conn = new mysqli($servername, $username, $password, $dbname);` + `if ($conn->connect_error) die(...)`
- Proses form: `if ($_SERVER["REQUEST_METHOD"] == "POST") { ... }` di **atas** file, HTML di bawah
- Query: string SQL lalu `$conn->query($sql) === TRUE`
- Baca data: `while ($row = $result->fetch_assoc())`, cek `$result->num_rows > 0`
- Halaman terpisah: `index.php` (tampil + link), `ubah.php?id=`, `hapus.php?id=` (halaman konfirmasi + POST)
- Feedback: `<script>alert('...')</script>` lalu kembali ke index
- Form pakai `<table>` dengan kolom `Label | : | input` (seperti Tugas 1)
- `action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"`
- Validasi: `$errors = [];` → `$errors[] = "..."` → `foreach ($errors as $err) echo $err . "<br>";`
- Upload: `$allowed_types`, `$max_size = 2 * 1024 * 1024`, `uniqid() . '-' . basename(...)`, `move_uploaded_file`
- Nama variabel `snake_case` (`$nama_upload`, `$target_path`), fungsi `camelCase` (`hitungLuasPersegi`)

### Yang sengaja **diperbaiki** dari template dosen (tetap gaya yang sama)
| Di template dosen | Masalah | Di `klinik-dosen` |
|---|---|---|
| `"... VALUES ('$nim', '$nama_depan' ..."` | **SQL Injection** (input `'` merusak query) | `$conn->real_escape_string()` untuk teks, `(int)` untuk angka |
| `<?php echo $row["nama"]?>` | **XSS** (input `<script>` ikut dijalankan) | `htmlspecialchars(...)` setiap echo data user |
| `ubah.php/?id=` | Slash setelah `.php` merusak path CSS | `ubah.php?id=` |
| Koneksi ditulis ulang di tiap file | Ganti DB = ganti banyak file | `include "koneksi.php";` |
| PIN/password disimpan mentah | Bocor kalau DB dicuri | `password_hash()` |
| PHP 8.1+ mysqli melempar exception | `else { alert('Gagal') }` tidak pernah jalan | `mysqli_report(MYSQLI_REPORT_OFF);` di `koneksi.php` |
| `strftime()` | Deprecated di PHP 8.1 | `date()` + `strtotime()` |

---

## 2. Gambaran besar aplikasi

**Klinik Sehat**: pasien registrasi → admin melihat, mencari, mengubah status, menghapus.

```
            ┌──────────────┐  1 poli punya banyak pasien   ┌─────────────────────────┐
            │     poli     │◄──────────────────────────────│         pasien          │
            ├──────────────┤        (FOREIGN KEY)          ├─────────────────────────┤
            │ id  (PK)     │                               │ id (PK)                 │
            │ nama_poli    │                               │ no_registrasi (UNIQUE)  │
            │ tarif        │                               │ nama, umur, email, pin  │
            └──────────────┘                               │ jenis_kelamin  (radio)  │
                                                           │ gejala      (checkbox)  │
                                                           │ poli_id     (select)    │
                                                           │ deskripsi   (textarea)  │
                                                           │ bukti_bayar (file)      │
                                                           │ status, tanggal_kunjungan│
                                                           └─────────────────────────┘
```

### Fitur (ada di kedua versi)
- **Create**: form registrasi lengkap (semua jenis input), validasi klien (HTML5) + server (PHP), upload gambar, *sticky form* (isian tidak hilang saat error)
- **Read**: tabel + JOIN, statistik (COUNT/GROUP BY), pencarian & filter via GET, pagination, halaman detail
- **Kolom hitungan** (pola gaji karyawan P4 Latihan 4): Total Biaya = tarif poli + biaya tiap gejala − diskon usia (Anak-anak 20%, Lansia 30%). Tidak disimpan di database, dihitung saat ditampilkan; pendapatan di statistik = jumlah total biaya pasien berstatus Selesai
- **Update**: form terisi data lama, checkbox/radio/select tercentang otomatis, PIN & file boleh dikosongkan, file lama dihapus kalau diganti
- **Delete**: lewat POST (bukan link GET), file upload ikut dihapus
- **CRUD tabel kedua (poli)** dalam satu file, termasuk menangani error *foreign key* (poli yang masih dipakai tidak bisa dihapus) dan *duplicate entry*

### Alur CRUD (versi dosen)
```
index.php ──link──► tambah.php ──POST ke dirinya sendiri──► validasi ──► INSERT ──► alert ──► detail.php
    │
    ├─link──► detail.php?id=3
    ├─link──► ubah.php?id=3  ──SELECT data lama──► form terisi ──POST──► validasi ──► UPDATE ──► alert
    └─link──► hapus.php?id=3 ──SELECT──► "Anda yakin?" ──POST──► DELETE + unlink file ──► alert ──► index.php
```

---

## 3. Cara menjalankan

1. Buka **XAMPP Control Panel** → Start **Apache** dan **MySQL**.
2. Salin folder `klinik-dosen` (dan/atau `klinik-modern`) ke `C:\xampp\htdocs\`.
3. Buka http://localhost/phpmyadmin → tab **Import** → pilih `database.sql` dari folder project → **Go**.
   - `klinik-dosen/database.sql` membuat database `klinik_dosen`
   - `klinik-modern/database.sql` membuat database `klinik_modern`
4. Buka di browser:
   - http://localhost/klinik-dosen/
   - http://localhost/klinik-modern/ (otomatis diarahkan ke `public/`)

Data contoh: 5 poli, 6 pasien (PIN semuanya `1234`). Bukti bayar data contoh kosong — coba upload lewat menu **Ubah**.

---

## 4. Peta file `klinik-dosen` (baca urut ini)

| File | Isi | Materi kunci |
|---|---|---|
| [`database.sql`](klinik-dosen/database.sql) | Struktur tabel + data awal | PRIMARY KEY, FOREIGN KEY, ENUM, UNIQUE |
| [`koneksi.php`](klinik-dosen/koneksi.php) | Koneksi mysqli | `new mysqli`, `connect_error`, `die` |
| [`fungsi.php`](klinik-dosen/fungsi.php) | Semua fungsi buatan | if/else, switch, match, validasi, upload |
| [`index.php`](klinik-dosen/index.php) | READ: tabel, cari, filter, statistik, pagination | `$_GET`, `while fetch_assoc`, `for`, JOIN, LIMIT |
| [`tambah.php`](klinik-dosen/tambah.php) | CREATE | `$_POST`, `$_FILES`, `isset`, `implode`, INSERT |
| [`ubah.php`](klinik-dosen/ubah.php) | UPDATE | `explode`, `in_array` untuk `checked`, UPDATE |
| [`hapus.php`](klinik-dosen/hapus.php) | DELETE dengan konfirmasi | DELETE, `unlink` |
| [`detail.php`](klinik-dosen/detail.php) | READ 1 data | fungsi string, `nl2br` |
| [`poli.php`](klinik-dosen/poli.php) | CRUD lengkap 1 tabel dalam 1 file | hidden input `aksi`, `$conn->errno` |
| [`latihan.php`](klinik-dosen/latihan.php) | Jawaban 6 latihan non-CRUD di slide | function, switch, match, if berantai, for |
| [`style.css`](klinik-dosen/style.css) | External CSS | flexbox, `:hover`, `:nth-child(even)` |

> **Kalau soal ujian cuma minta CRUD 1 tabel sederhana**, pola tercepat adalah `poli.php` (semua di 1 file). Kalau soal minta form lengkap + upload, pakai pola `tambah.php` / `ubah.php` / `hapus.php`.

---

## 4b. Urutan Baca & Urutan Membangun Project

Prinsipnya sama untuk kedua versi: **database dulu → koneksi → Read → Create → Update → Delete**.
Kenapa **Read dulu**, bukan Create? Karena tanpa halaman tampil data, kamu tidak bisa melihat apakah data hasil Create benar-benar masuk. Data contoh di `database.sql` membuat halaman Read langsung bisa dites.

> Aturan emas: **tes setiap langkah sebelum lanjut.** Kalau error muncul di langkah 3, kamu tahu pasti penyebabnya ada di langkah 3, bukan tercampur dengan 5 file lain.

### A. `klinik-dosen` (urutan ini juga yang dipakai saat ujian)

| # | File | Kenapa di urutan ini | Cara tes sebelum lanjut |
|---|---|---|---|
| 1 | `database.sql` | Semua file PHP bergantung pada tabel. Tabel induk (`poli`) dibuat sebelum tabel anak (`pasien`) karena ada FOREIGN KEY | Import di phpMyAdmin → tabel `poli` & `pasien` muncul dan berisi data contoh |
| 2 | `koneksi.php` | Dipakai oleh semua halaman | Buka `localhost/klinik-dosen/koneksi.php` → layar **kosong** = berhasil; kalau gagal muncul `Connection failed` |
| 3 | `style.css` (versi singkat dulu) | Cukup style tabel & form supaya hasil tes enak dilihat; dirapikan di akhir | — |
| 4 | `index.php` (**Read**) | Mulai dari yang paling sederhana: `SELECT` + `while fetch_assoc` + tabel. Fitur cari, filter, pagination, statistik ditambah **belakangan** | 6 data contoh tampil di tabel |
| 5 | `fungsi.php` | Mulai dari fungsi tampilan (`kategoriUsia`, `labelStatus`, `formatTanggal`), lalu fungsi validasi & upload saat masuk langkah 6. Boleh juga ditulis bertahap sambil jalan | Panggil fungsinya di `index.php`, mis. kolom umur jadi "21 th · Dewasa" |
| 6 | `tambah.php` (**Create**) | Bertahap: (a) form HTML → (b) `INSERT` tanpa validasi → (c) validasi `$errors` → (d) upload file → (e) sticky form | Isi form → data baru muncul di `index.php`; isi asal-asalan → muncul daftar error; file masuk ke `uploads/` |
| 7 | `detail.php` (**Read 1 data**) | Mengenalkan pola `?id=` + `SELECT ... WHERE id = $id` yang dipakai lagi di ubah & hapus | Klik "Detail" → data & gambar bukti bayar tampil; coba `detail.php?id=999` → alert "tidak ditemukan" |
| 8 | `ubah.php` (**Update**) | Gabungan detail (ambil data lama) + tambah (form & validasi). Copy `tambah.php` lalu modifikasi | Form terisi data lama (radio/checkbox/select tercentang); ubah tanpa ganti file → file lama tetap; ganti file → file lama terhapus dari `uploads/` |
| 9 | `hapus.php` (**Delete**) | Paling pendek, tapi harus terakhir karena butuh data yang bisa dibuat ulang lewat Create | Hapus 1 data → hilang dari tabel & filenya hilang dari `uploads/` |
| 10 | Fitur tambahan `index.php` | Cari (GET) → filter → pagination → statistik | Cari "agus" → 2 data; pindah halaman tetap membawa filter |
| 11 | `poli.php` | CRUD tabel kedua dalam 1 file | Tambah poli; tambah nama yang sama → error duplikat; hapus poli yang dipakai pasien → ditolak |
| 12 | `latihan.php` (opsional) | Latihan non-CRUD; terpisah dari aplikasi klinik | Gaji: Manager, S1, 12 tahun, lembur 8 jam → Rp19.200.000; AC 5×6 m → 2 PK |
| 13 | `style.css` (lengkap) | Kosmetik paling akhir — fungsi dulu, tampilan belakangan | Cek tampilan semua halaman |

**Urutan BACA** sama dengan urutan di atas: `database.sql → koneksi.php → fungsi.php → index.php → tambah.php → detail.php → ubah.php → hapus.php → poli.php → latihan.php → style.css`.

> Fungsi `hitungBiaya()` di `fungsi.php` ditambahkan saat langkah 4/7 (kolom Total Biaya & rincian di detail). Tes: Agus (20 th, Umum, Pusing+Demam) = Rp 95.000; Eko (65 th, Lansia) = Rp 59.500.

### B. `klinik-modern`

Di versi ini, satu fitur menyentuh beberapa lapisan. Bangun **dari bawah ke atas** (database → class → controller → view), satu fitur utuh dulu baru fitur berikutnya.

| # | File | Kenapa di urutan ini | Cara tes sebelum lanjut |
|---|---|---|---|
| 1 | `database.sql` | Sama seperti versi dosen | Import, cek tabel |
| 2 | `config.php` | Semua pengaturan (DB, upload) di satu tempat, dibaca file lain | — |
| 3 | `src/bootstrap.php` + `src/helpers.php` | Fondasi: autoload, session, `e()`, `url()`, `view()`, `redirect()` | — (dites di langkah 5) |
| 4 | `src/Support/Database.php` | Membuat koneksi PDO | — |
| 5 | `public/index.php` (router minimal) + `views/layout.php` | Pintu masuk. Mulai dengan 1 route saja: `pasien.index` | Buka `localhost:.../public/` → layout & navbar tampil |
| 6 | `src/Enums/*.php` | Nilai tetap (Status, JenisKelamin, Gejala) dipakai repository, validator, dan view | — |
| 7 | **Read**: `PasienRepository::paginate()` → `PasienController::index()` → `views/pasien/index.php` | Fitur pertama lengkap dari DB sampai HTML | 6 data contoh tampil |
| 8 | **Read 1**: `find()` → `show()` → `views/pasien/show.php` + `views/error.php` | Pola `findOrFail()` dipakai lagi di edit/update/destroy | Detail tampil; id salah → halaman 404 |
| 8b | `src/Support/BiayaCalculator.php` + `Gejala::biaya()` | Kolom hitungan dipisah ke class sendiri, dipakai di index, detail, dan statistik | Rincian biaya di detail sama dengan versi dosen |
| 9 | **Create**: `PasienValidator` → `FileUploader` → `PasienRepository::create()` → `PasienController::create()` & `store()` → `views/pasien/form.php` + CSRF & flash | Validator & uploader ditulis dulu karena dipakai controller | Simpan → redirect ke detail + pesan hijau; isian salah → error per field |
| 10 | **Update**: `update()` di repository & controller, `edit()` memakai **form yang sama** | Hanya menambah mode "ubah" ke `form.php` | Form terisi; ganti file → file lama hilang |
| 11 | **Delete**: `delete()` → `destroy()` + form POST di view | Paling akhir | Hapus → redirect + pesan; POST tanpa token → 419 |
| 12 | `PoliRepository` → `PoliController` → `views/poli/index.php` | Fitur kedua, pola sama | Duplikat & FK ditolak dengan pesan ramah |
| 13 | `public/css/app.css`, `.htaccess` | Tampilan & pengamanan folder di akhir | Cek tampilan, termasuk mode gelap |

**Urutan BACA** untuk memahami 1 request: `public/index.php → src/bootstrap.php → src/helpers.php → src/Controllers/PasienController.php → src/Repositories/PasienRepository.php → src/Support/PasienValidator.php → src/Support/FileUploader.php → views/layout.php → views/pasien/*.php`. Lalu `src/Enums/*`, `PoliController`, `PoliRepository`, `views/poli/index.php`.

---

## 5. Perbandingan dengan `klinik-modern`

| Aspek | `klinik-dosen` | `klinik-modern` | Kenapa lebih baik |
|---|---|---|---|
| Database | `mysqli` + `real_escape_string` | **PDO + prepared statement** (`:nama`) | Data & perintah SQL dipisah → SQL Injection mustahil, bukan cuma "di-escape" |
| Struktur | 1 file = 1 halaman (logika + SQL + HTML campur) | **Front controller** + Controller + Repository + View | Tiap file 1 tanggung jawab; SQL hanya di Repository, HTML hanya di View |
| Routing | `ubah.php?id=3` | `index.php?r=pasien.edit&id=3` + `match([$method, $route])` | Hanya 1 pintu masuk; hapus/ubah **hanya** menerima POST |
| Form tambah & ubah | Ditulis 2x | 1 template `views/pasien/form.php` | Tidak ada duplikasi |
| Pesan sukses | `alert()` JavaScript | **Flash message** di session + redirect 303 (pola **PRG**) | Refresh tidak mengirim ulang form; tidak butuh JS |
| Pesan error | Daftar di atas form | Per field, di bawah input yang salah | UX lebih jelas |
| Keamanan form | — | **CSRF token** di setiap POST | Situs lain tidak bisa memalsukan request hapus/ubah |
| Validasi file | `$_FILES['type']` + ekstensi | `finfo` membaca **isi** file, nama file acak `random_bytes` | `type` dikirim browser → bisa dipalsukan (`fake.png` berisi PHP lolos di versi dosen) |
| Nilai tetap | Array string `["Menunggu", ...]` | `enum Status: string` (PHP 8.1) | Salah ketik ketahuan; logika badge menempel di enum |
| Tipe data | Dinamis | `declare(strict_types=1)` + type hint | Bug tipe data ketahuan lebih awal |
| Validasi tanggal | `strtotime` | `DateTimeImmutable::createFromFormat` | `2026-02-31` ditolak (strtotime menerimanya) |
| Nama | `/^[a-zA-Z\s]+$/` | `/^[\p{L}\s.'-]+$/u` | Nama seperti "Nur'aini" atau "José" diterima |
| Folder publik | Semua file bisa dibuka browser | Hanya `public/` | `config.php` (password DB) tidak bisa diakses |
| Upload folder | — | `.htaccess` menolak `.php` | File berbahaya tidak bisa dieksekusi |
| CSS | Warna hard-coded | CSS variables, Grid, dark mode otomatis | Ganti tema cukup ubah variabel |

Alur 1 request di `klinik-modern`:
```
Browser ─► public/index.php ─► src/bootstrap.php (config, autoload, session)
                │
                ├─ verify_csrf()            (kalau POST)
                ├─ match([$method, $route]) ─► PasienController->update($id)
                │                                   ├─ PasienValidator::validate()
                │                                   ├─ FileUploader->validate()/store()
                │                                   ├─ PasienRepository->update()  ─► PDO ─► MySQL
                │                                   ├─ flash('success', ...)
                │                                   └─ redirect('pasien.show')  (303)
                └─ (GET) view('pasien/show') ─► views/layout.php + views/pasien/show.php
```

---

## 6. Hal yang sering bikin error saat ujian (sudah ditangani di kode)

1. **Lupa `enctype="multipart/form-data"`** → `$_FILES` kosong.
2. **Checkbox/radio tidak dicentang** → key tidak ada di `$_POST` → pakai `isset($_POST["gejala"]) ? ... : []`.
3. **Checkbox harus `name="gejala[]"`** (pakai `[]`) supaya diterima sebagai array.
4. **`empty("0")` bernilai `true`** → umur 0 dianggap kosong. Pakai `=== ""`.
5. **Array tidak bisa langsung masuk database** → `implode(",", $gejala)` saat simpan, `explode(",", ...)` saat baca.
6. **Edit form**: radio/checkbox/select harus diberi `checked`/`selected` sesuai data lama → `($jk == "L") ? "checked" : ""`.
7. **Ubah tanpa ganti file** → jangan timpa kolom file dengan string kosong; pakai nama file lama.
8. **Hapus data** → hapus juga file di folder `uploads/` dengan `unlink()`.
9. **`ubah.php` setelah POST kehilangan `?id=`** → `action` form harus membawa `?id=`.
10. Kutip `'` di input (mis. `Nur'aini`) merusak query → `real_escape_string`.

---

## 7. Latihan sebelum ujian

Kerjakan di salinan `klinik-dosen` (jangan lihat kode dulu, cek setelahnya):

1. **Dari nol (45 menit)**: buat CRUD tabel `buku` (judul, penulis, tahun, kategori `select`, sampul `file`) dengan pola `tambah/ubah/hapus.php`.
2. Tambah kolom **`alamat`** (textarea, wajib, maks 200 karakter) di `pasien` — tiru cara kolom `no_hp` ditambahkan (SQL, validasi, tambah, ubah, detail).
3. Tambah filter **rentang tanggal** (dari–sampai) di `index.php` pakai GET.
4. Tambah tombol **"Tandai Selesai"** di tabel yang langsung mengubah status lewat POST.
5. Tambah kolom **total biaya per poli** di `poli.php` (`SUM` + `LEFT JOIN`).
6. Ubah validasi PIN supaya **tidak boleh angka berulang** seperti `1111` (petunjuk: `preg_match('/^(\d)\1+$/', ...)`).
7. Tantangan: pindahkan soal no. 1 ke gaya `klinik-modern` (Repository + Controller + View).

Kalau ada bagian yang belum paham, tanyakan saja: sebut nama file dan nomor barisnya.
