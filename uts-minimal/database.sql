-- =====================================================
-- DATABASE UTS: db_uts   (import lewat phpMyAdmin > Import)
-- =====================================================
CREATE DATABASE IF NOT EXISTS db_uts;
USE db_uts;

DROP TABLE IF EXISTS mahasiswa;

CREATE TABLE mahasiswa (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nim           VARCHAR(10)  NOT NULL UNIQUE,
    nama          VARCHAR(50)  NOT NULL,
    jenis_kelamin VARCHAR(1)   NOT NULL,   -- 'L' / 'P'      (radio)
    jurusan       VARCHAR(30)  NOT NULL,   -- pilihan        (select)
    tgl_lahir     DATE         NOT NULL,   --                (date)
    alamat        TEXT                     --                (textarea)
);

INSERT INTO mahasiswa (nim, nama, jenis_kelamin, jurusan, tgl_lahir, alamat) VALUES
('20231001', 'Agus Suherman',  'L', 'Teknik Informatika', '2005-03-12', 'Jl. Melati No. 1, Tangerang'),
('20231002', 'Bagas Herman',   'L', 'Sistem Informasi',   '2006-07-25', 'Jl. Mawar No. 5, Jakarta'),
('20231003', 'Candra Agustin', 'P', 'Teknik Informatika', '2004-11-02', 'Jl. Kenanga No. 9, Serpong');
