-- =====================================================================
-- DATABASE: klinik_dosen
-- Cara pakai:
--   1. Nyalakan Apache + MySQL di XAMPP
--   2. Buka http://localhost/phpmyadmin
--   3. Klik tab "Import" -> pilih file ini -> Go
--      (atau tab "SQL" -> copy-paste isi file ini -> Go)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS klinik_dosen
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE klinik_dosen;

-- Hapus tabel lama (urutan penting: tabel anak dulu, baru tabel induk)
DROP TABLE IF EXISTS pasien;
DROP TABLE IF EXISTS poli;

-- ---------------------------------------------------------------------
-- Tabel MASTER: poli
-- Dipakai untuk isi <select> "Poli Tujuan" secara dinamis dari database
-- ---------------------------------------------------------------------
CREATE TABLE poli (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nama_poli VARCHAR(50) NOT NULL UNIQUE,
    tarif     INT NOT NULL DEFAULT 0
);

-- ---------------------------------------------------------------------
-- Tabel TRANSAKSI: pasien (registrasi klinik, dari Tugas 1)
-- ---------------------------------------------------------------------
CREATE TABLE pasien (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    no_registrasi     VARCHAR(20)  NOT NULL UNIQUE,
    nama              VARCHAR(50)  NOT NULL,
    umur              INT          NOT NULL,
    email             VARCHAR(50)  NOT NULL,
    no_hp             VARCHAR(13)  NOT NULL,          -- angka 10-13 digit (Latihan Pendaftaran Event P6)
    pin               VARCHAR(255) NOT NULL,          -- disimpan dalam bentuk hash
    jenis_kelamin     ENUM('L','P') NOT NULL,         -- L = Laki-laki, P = Perempuan
    gejala            VARCHAR(255) NOT NULL,          -- checkbox, disimpan "Pusing,Flu,Demam"
    poli_id           INT          NOT NULL,          -- relasi ke tabel poli
    deskripsi         TEXT,
    bukti_bayar       VARCHAR(255),                   -- nama file di folder uploads/
    status            ENUM('Menunggu','Diperiksa','Selesai') NOT NULL DEFAULT 'Menunggu',
    tanggal_kunjungan DATE         NOT NULL,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    -- FOREIGN KEY: poli yang masih dipakai pasien TIDAK BISA dihapus (RESTRICT)
    CONSTRAINT fk_pasien_poli FOREIGN KEY (poli_id) REFERENCES poli(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- ---------------------------------------------------------------------
-- Data awal (seed)
-- ---------------------------------------------------------------------
INSERT INTO poli (nama_poli, tarif) VALUES
('Umum', 50000),
('Gigi', 75000),
('Anak', 60000),
('Mata', 80000),
('THT', 70000);

-- PIN semua data contoh = 1234  (hash dibuat dengan password_hash('1234', PASSWORD_DEFAULT))
INSERT INTO pasien (no_registrasi, nama, umur, email, no_hp, pin, jenis_kelamin, gejala, poli_id, deskripsi, bukti_bayar, status, tanggal_kunjungan) VALUES
('REG-20261101-1001', 'Agus Suherman',  20, 'agus@mail.com', '081234567001', '$2y$10$Z/v77jDAnWVWnz36dv1QYOc4RtShBqsstMFYO/99PJLajzsLAEtPy', 'L', 'Pusing,Demam',      1, 'Demam sejak 2 hari lalu.',        NULL, 'Selesai',   '2026-11-01'),
('REG-20261102-1002', 'Bagas Herman',   19, 'bagas@mail.com', '081234567002', '$2y$10$Z/v77jDAnWVWnz36dv1QYOc4RtShBqsstMFYO/99PJLajzsLAEtPy', 'L', 'Sakit Kepala',      4, 'Mata sering berair.',             NULL, 'Diperiksa', '2026-11-02'),
('REG-20261103-1003', 'Candra Agustin', 21, 'candra@mail.com', '081234567003', '$2y$10$Z/v77jDAnWVWnz36dv1QYOc4RtShBqsstMFYO/99PJLajzsLAEtPy', 'P', 'Flu',               5, 'Hidung tersumbat.',               NULL, 'Menunggu',  '2026-11-03'),
('REG-20261104-1004', 'Dewi Lestari',    8, 'dewi@mail.com', '081234567004', '$2y$10$Z/v77jDAnWVWnz36dv1QYOc4RtShBqsstMFYO/99PJLajzsLAEtPy', 'P', 'Flu,Demam',         3, 'Anak rewel dan demam.',           NULL, 'Menunggu',  '2026-11-04'),
('REG-20261105-1005', 'Eko Prasetyo',   65, 'eko@mail.com', '081234567005', '$2y$10$Z/v77jDAnWVWnz36dv1QYOc4RtShBqsstMFYO/99PJLajzsLAEtPy', 'L', 'Pusing,Sakit Kepala', 1, 'Tekanan darah tinggi.',         NULL, 'Menunggu',  '2026-11-05'),
('REG-20261106-1006', 'Fitri Handayani',15, 'fitri@mail.com', '081234567006', '$2y$10$Z/v77jDAnWVWnz36dv1QYOc4RtShBqsstMFYO/99PJLajzsLAEtPy', 'P', 'Sakit Kepala',      2, 'Gigi geraham berlubang.',         NULL, 'Selesai',   '2026-11-06');
