# CATATAN UTS PEMROGRAMAN WEB (Open Book)

Kisi-kisi: **(a)** halaman login tanpa database, pakai `if` · **(b)** CRUD: tampil tabel, tambah, edit, hapus.
Project acuan: folder `uts-latihan/` (Data Mahasiswa). Login: `admin` / `admin123`.

> **Baru buka file ini?** Langsung ke bagian **9 (apa yang di-print)** dan **10 (rencana belajar)** di paling bawah, lalu kembali ke atas.
> Versi **tanpa login & CSS minimal** ada di bagian **8**.

---

## 1. Langkah saat ujian (urutan kerja)

| # | Langkah | Cek berhasil |
|---|---|---|
| 1 | XAMPP: Start **Apache** + **MySQL** | Tulisan hijau "Running" |
| 2 | Buat folder `C:\xampp\htdocs\nama_folder` | — |
| 3 | phpMyAdmin → buat database + tabel (tab **SQL**, tempel `CREATE TABLE`) + isi 2–3 data contoh | Tabel muncul berisi data |
| 4 | `koneksi.php` | Buka di browser → layar **kosong** = OK |
| 5 | `login.php` + `cek_login.php` + `logout.php` | Password salah → error; benar → pindah ke index |
| 6 | `index.php` (tampil tabel) | Data contoh muncul |
| 7 | `tambah.php` | Data baru muncul di index |
| 8 | `edit.php` (copy dari tambah.php) | Form terisi data lama; perubahan tersimpan |
| 9 | `hapus.php` | Data hilang dari tabel |
| 10 | `style.css` (kalau ada waktu) | — |

> **Tes tiap langkah sebelum lanjut.** Kalau error, sumbernya pasti di file yang baru ditulis.

---

## 2. Kalau soalnya beda tema (misal: Data Buku / Produk / Pegawai)

Struktur & alur **tetap sama**. Yang diganti hanya:

| Ganti ini | Contoh Mahasiswa → Buku |
|---|---|
| Nama database di `koneksi.php` | `db_uts` → `db_perpus` |
| Nama tabel di semua query | `mahasiswa` → `buku` |
| Nama kolom (di SQL, `$_POST[...]`, `$row[...]`, `name="..."`) | `nim, nama, jurusan` → `kode, judul, kategori` |
| Isi `<th>` tabel & label form | "NIM" → "Kode Buku" |
| Pilihan select / radio | jurusan → kategori buku |

Tips: kerjakan `index.php` dulu sampai benar, lalu **copy** `tambah.php` menjadi `edit.php`.

---

## 3. Template inti (hafalkan polanya)

### Login (if, tanpa database)
```php
<?php
session_start();                                   // PALING ATAS, sebelum HTML
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    if ($username == "admin" && $password == "admin123") {
        $_SESSION["login"] = true;
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<form method="post" action="">
    <input type="text" name="username">
    <input type="password" name="password">
    <button type="submit">Login</button>
</form>
<?php echo $error; ?>
```
**cek_login.php** (include di baris 1 index/tambah/edit/hapus):
```php
<?php session_start();
if (!isset($_SESSION["login"])) { header("Location: login.php"); exit; } ?>
```
**logout.php**: `session_start(); session_destroy(); header("Location: login.php"); exit;`

> Kalau dosen hanya minta "login pakai if", versi paling minimal = form + `if` + `header("Location: index.php")`. Session dipakai supaya index.php tidak bisa dibuka tanpa login.

### Koneksi
```php
$conn = new mysqli("localhost", "root", "", "db_uts");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
```

### READ — tampil tabel
```php
$result = $conn->query("SELECT * FROM mahasiswa");
while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row["nama"]; ?></td>
        <td><a href="edit.php?id=<?php echo $row["id"]; ?>">Edit</a>
            <a href="hapus.php?id=<?php echo $row["id"]; ?>"
               onclick="return confirm('Yakin hapus?')">Hapus</a></td>
    </tr>
<?php } ?>
```

### CREATE — tambah
```php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST["nama"];
    $sql = "INSERT INTO mahasiswa (nim, nama) VALUES ('$nim', '$nama')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Berhasil'); window.location='index.php';</script>";
    }
}
```

### UPDATE — edit
```php
$id = $_GET["id"];
$data = $conn->query("SELECT * FROM mahasiswa WHERE id = $id")->fetch_assoc();
// form: value="<?php echo $data['nama']; ?>"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "UPDATE mahasiswa SET nama = '$nama', nim = '$nim' WHERE id = $id";   // WAJIB WHERE!
    ...
}
```

### DELETE — hapus
```php
$id = $_GET["id"];
$conn->query("DELETE FROM mahasiswa WHERE id = $id");                          // WAJIB WHERE!
echo "<script>alert('Terhapus'); window.location='index.php';</script>";
```

---

## 4. Isi ulang form saat EDIT (sering lupa!)

| Input | Cara mengisi data lama |
|---|---|
| text / number / date | `value="<?php echo $nama; ?>"` |
| textarea | `<textarea name="alamat"><?php echo $alamat; ?></textarea>` (BUKAN value) |
| radio | `<input type="radio" name="jk" value="L" <?php echo ($jk == "L") ? "checked" : ""; ?>>` |
| select | `<option value="TI" <?php echo ($jurusan == "TI") ? "selected" : ""; ?>>` |
| checkbox (banyak) | `name="hobi[]"` → simpan `implode(",", $hobi)` → baca `explode(",", $row["hobi"])` → `in_array("Membaca", $hobi) ? "checked" : ""` |
| date | format harus `YYYY-MM-DD` (sama dengan MySQL) |

Radio/checkbox yang tidak dipilih **tidak terkirim** → pakai `isset($_POST["jk"]) ? $_POST["jk"] : ""`.

---

## 5. SQL cepat

```sql
CREATE DATABASE db_uts;
CREATE TABLE mahasiswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nim VARCHAR(10) NOT NULL,
  nama VARCHAR(50) NOT NULL,
  tgl_lahir DATE,
  alamat TEXT
);
INSERT INTO mahasiswa (nim, nama) VALUES ('20231001', 'Agus');
SELECT * FROM mahasiswa WHERE id = 1;
UPDATE mahasiswa SET nama = 'Budi' WHERE id = 1;
DELETE FROM mahasiswa WHERE id = 1;
```
Teks pakai kutip `'...'`, angka tidak perlu kutip.

---

## 6. Error yang sering muncul & solusinya

| Pesan / gejala | Penyebab | Solusi |
|---|---|---|
| `Connection failed: Unknown database` | Nama DB salah / belum dibuat | Cek nama di phpMyAdmin & `koneksi.php` |
| `Connection refused` / tidak bisa konek | MySQL XAMPP belum Start | Start MySQL |
| `Undefined array key "jk"` | Radio/checkbox tidak dipilih | `isset($_POST["jk"]) ? ... : ""` |
| `Undefined array key "id"` | Buka edit.php tanpa `?id=` | Buka lewat link dari index |
| `Cannot modify header information - headers already sent` | Ada output/spasi/HTML sebelum `header()` atau `session_start()` | Taruh di **baris paling atas**, tidak ada spasi sebelum `<?php` |
| `Unknown column 'xxx' in 'field list'` | Nama kolom di SQL beda dengan tabel | Samakan ejaan |
| `You have an error in your SQL syntax` | Kurang kutip / koma / ada `'` di input | `echo $sql;` untuk melihat query; pakai `$conn->real_escape_string()` |
| Data tidak tersimpan, tidak ada error | `name="..."` di form beda dengan `$_POST["..."]` | Samakan nama |
| Semua data ikut berubah/terhapus | Lupa `WHERE id = $id` | Selalu pakai WHERE |
| Edit: form kosong | Lupa `value="..."` / SELECT data lama | Lihat bagian 4 |
| Login berhasil tapi index tetap minta login | `session_start()` tidak dipanggil di cek_login | Tambahkan |
| Halaman blank putih | Error PHP tersembunyi | Tambah `error_reporting(E_ALL); ini_set('display_errors', 1);` di atas |

Debug cepat: `var_dump($_POST);` · `echo $sql;` · `echo $conn->error;`

---

## 7. Checklist sebelum dikumpulkan
- [ ] Login salah → pesan error; login benar → masuk index
- [ ] Buka `index.php` langsung tanpa login → dilempar ke login
- [ ] Tabel menampilkan semua data
- [ ] Tambah data → muncul di tabel
- [ ] Edit → form terisi data lama (termasuk radio/select) → tersimpan
- [ ] Hapus → ada konfirmasi → data hilang
- [ ] Logout → kembali ke login
- [ ] Export database (phpMyAdmin → Export) kalau diminta dikumpulkan

---

## 8. Kalau soal TIDAK minta login & CSS-nya minimal: apa yang tidak perlu ditulis?

Di file project sudah kuberi **penanda** (tidak ada yang dihapus). Cari dengan `Ctrl+F`:

| Penanda | Arti |
|---|---|
| `[SKIP-LOGIN]` | Tidak perlu ditulis kalau tanpa login |
| `[SKIP-CSS]` | Hiasan, tidak perlu ditulis kalau CSS minimal |
| `[WAJIB-CSS]` | Kalau diminta "CSS minimal", tulis bagian ini saja (±10 baris) |

### Tanpa login — yang di-SKIP

| File | Yang tidak ditulis |
|---|---|
| `login.php` | **Seluruh file** |
| `cek_login.php` | **Seluruh file** |
| `logout.php` | **Seluruh file** |
| `index.php` | Baris `include "cek_login.php";` **dan** blok `<div class="header">` (Halo, admin + tombol Logout) |
| `tambah.php`, `edit.php`, `hapus.php` | Baris `include "cek_login.php";` saja |

⚠️ Di `index.php`, kalau `include "cek_login.php"` dihapus, blok header **wajib ikut dihapus**. Kalau tidak, `$_SESSION["username"]` error karena session tidak pernah dimulai.

### CSS minimal — yang di-SKIP

| Pilihan | Yang ditulis |
|---|---|
| **Tanpa CSS sama sekali** | Tidak perlu `style.css` dan baris `<link rel="stylesheet" ...>`. Cukup `<table border="1">` (sudah ada di `index.php`). Atribut `class="..."` boleh dibiarkan atau dihapus, tidak berpengaruh |
| **CSS minimal** | Hanya blok `[WAJIB-CSS]` di `style.css`: `border-collapse`, `border`, `padding`, warna header |
| **CSS lengkap** | Semua isi `style.css` |

### Jadi, versi paling ringkas (tanpa login + tanpa CSS) cukup **6 file**:

```
database.sql   -> buat tabel di phpMyAdmin
koneksi.php    -> 8 baris
index.php      -> tampil tabel   (tanpa include cek_login & blok header)
tambah.php     -> tambah         (tanpa include cek_login)
edit.php       -> edit           (tanpa include cek_login)
hapus.php      -> hapus          (tanpa include cek_login)
```

Kalau masih mau lebih cepat:
- Validasi di `tambah.php` / `edit.php` boleh dikurangi jadi `empty()` saja. Pola `preg_match` & `is_numeric` hanya kalau soal memintanya.
- Halaman konfirmasi di `hapus.php` boleh diganti versi cepat 4 baris (lihat komentar di atas `hapus.php`).

---

## 9. Apa yang di-print untuk ujian (open book)

Terlalu banyak kertas justru bikin lambat mencari saat ujian. Cukup print ini:

1. **`CATATAN_UTS.md`** — file ini (sekitar 5–6 halaman). Contekan paling penting:
   - urutan kerja saat ujian, dari menyalakan XAMPP sampai siap dikumpulkan (bagian 1)
   - cara menyesuaikan kalau soalnya beda tema, misalnya Data Buku atau Pegawai (bagian 2)
   - template singkat login dan CRUD (bagian 3)
   - tabel cara mengisi ulang form edit: radio, select, textarea, checkbox (bagian 4)
   - **tabel error yang sering muncul beserta solusinya** (bagian 6)
   - checklist sebelum dikumpulkan (bagian 7)
   - versi tanpa login & CSS minimal (bagian 8)
2. **Semua file `.php` dan `database.sql` di `uts-latihan/`** (sekitar 10 halaman). Print lewat **Notepad++** (*File → Print*) supaya ada warna kode & nomor baris. Penanda `[SKIP-LOGIN]` / `[SKIP-CSS]` ikut tercetak, jadi saat ujian langsung kelihatan bagian mana yang boleh dilewati.
3. *(Opsional)* `klinik-dosen/tambah.php` dan `klinik-dosen/ubah.php` — hanya kalau ingin pegang contoh **checkbox (array)** dan **upload file**.

Tidak perlu di-print: `klinik-modern/` (gaya berbeda dari yang diajarkan), `klinik-dosen/` selain dua file di atas, `README.md`.

Cara print file `.md` ini: buka di VS Code → `Ctrl+Shift+V` (preview) → blok semua → copy → paste ke Word → print. Tabel & kode ikut rapi.

Urutan menyusun printan (biar gampang dibalik saat ujian):
`CATATAN_UTS` → `database.sql` → `koneksi.php` → `login.php` → `cek_login.php` → `logout.php` → `index.php` → `tambah.php` → `edit.php` → `hapus.php` → `style.css`.

---

## 10. Rencana belajar (mulai dari sini setelah bangun)

| Waktu | Kegiatan |
|---|---|
| 15 menit | Jalankan project: salin folder `uts-latihan` ke `C:\xampp\htdocs\`, nyalakan Apache + MySQL, buka http://localhost/uts-latihan/login.php (database `db_uts` sudah ada; kalau belum, import `database.sql` lewat phpMyAdmin). Coba login, tambah, edit, hapus. |
| 30 menit | Baca kode berurutan: `koneksi` → `login` → `cek_login` → `index` → `tambah` → `edit` → `hapus`. Baca komentarnya. Tandai bagian yang belum paham, lalu tanyakan. |
| 15 menit | Baca bagian 4 (isi ulang form edit) dan bagian 6 (tabel error) di catatan ini. |
| 10 menit | Print (lihat bagian 9). |
| 60 menit | **Simulasi ujian**: buat folder baru `htdocs/latihan_buku`, tulis dari nol CRUD **Data Buku** (kode, judul, penulis, kategori `select`, tahun terbit) + login, **sambil hanya melihat printan**. Pakai bagian 1 sebagai urutan kerja dan bagian 2 untuk mengganti nama kolom. |
| 10 menit | Cek pakai checklist bagian 7. Kalau ada error, cari di bagian 6. |

Kalau simulasi sudah lancar di bawah 60 menit, kamu siap. 💪
Kalau ada yang bingung, tanya dengan menyebut **nama file + nomor baris** atau **pesan error lengkapnya**.
