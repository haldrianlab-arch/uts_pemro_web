-- =====================================================
-- DATABASE UTS: db_uts   (import lewat phpMyAdmin > Import)
-- Dipakai oleh uts-latihan DAN uts-minimal (struktur sama)
-- =====================================================
CREATE DATABASE IF NOT EXISTS db_uts;
USE db_uts;

DROP TABLE IF EXISTS mahasiswa;

CREATE TABLE mahasiswa (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nim           VARCHAR(8)   NOT NULL UNIQUE, -- 8 digit angka   (text + pattern)
    nama          VARCHAR(50)  NOT NULL,        -- huruf & spasi   (text + pattern)
    email         VARCHAR(50)  NOT NULL,        --                 (email + filter_var)
    jenis_kelamin VARCHAR(1)   NOT NULL,        -- 'L' / 'P'       (radio)
    jurusan       VARCHAR(30)  NOT NULL,        -- pilihan         (select)
    tgl_lahir     DATE         NOT NULL,        --                 (date)
    alamat        TEXT,                         --                 (textarea)
    foto          VARCHAR(255)                  -- nama file di folder uploads/ (file)
);

INSERT INTO mahasiswa (nim, nama, email, jenis_kelamin, jurusan, tgl_lahir, alamat, foto) VALUES
('20231001', 'Agus Suherman',  'agus@mail.com',   'L', 'Teknik Informatika', '2005-03-12', 'Jl. Melati No. 1, Tangerang', NULL),
('20231002', 'Bagas Herman',   'bagas@mail.com',  'L', 'Sistem Informasi',   '2006-07-25', 'Jl. Mawar No. 5, Jakarta',    NULL),
('20231003', 'Candra Agustin', 'candra@mail.com', 'P', 'Teknik Informatika', '2004-11-02', 'Jl. Kenanga No. 9, Serpong',  NULL);
