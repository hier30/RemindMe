-- =========================
-- DATABASE MANAJEMEN TUGAS MAHASISWA
-- =========================
CREATE DATABASE remindme; USE remindme;
-- TABEL USER MAHASISWA
CREATE TABLE user_mahasiswa (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fakultas VARCHAR(50),
    prodi VARCHAR(50)
);

-- =========================
-- TABEL MATA KULIAH
-- =========================
CREATE TABLE mata_kuliah (
    mk_id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    sks INTEGER
);

-- =========================
-- TABEL TUGAS
-- =========================
CREATE TABLE tugas (
    tugas_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    mk_id INTEGER,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    tanggal_deadline DATETIME,
    status VARCHAR(20) DEFAULT 'belum',
    prioritas VARCHAR(20) DEFAULT 'sedang',
    FOREIGN KEY (user_id) REFERENCES user_mahasiswa(user_id),
    FOREIGN KEY (mk_id) REFERENCES mata_kuliah(mk_id)
);

-- =========================
-- DATA USER
-- =========================
INSERT INTO user_mahasiswa 
(nim, nama, email, password_hash, fakultas, prodi)
VALUES
('2315061009', 'ananda fahmuzna', 'nandafmznaa@gmail.com', '$2y$10$XybNh9EwVw168vZVaEQifeY8bNmd8pYBjCo858FbPK6.1E/m3YDsW', 'Teknik', 'Informatika'),
('2001001', 'Mahasiswa Demo', 'demo@univ.ac.id', '$2y$10$YA6wwuz9Gp65u0Y/pa2btOlFDnixri1ab5ilp21WBW8XspUgEeRJy', 'Teknik', 'Informatika');

-- =========================
-- DATA MATA KULIAH
-- =========================
INSERT INTO mata_kuliah 
(kode, nama, sks)
VALUES
('IF101', 'Pemrograman Web', 3),
('IF102', 'Basis Data', 4),
('IF103', 'Algoritma', 3);

-- =========================
-- DATA TUGAS
-- =========================
INSERT INTO tugas
(user_id, mk_id, judul, deskripsi, tanggal_deadline, status, prioritas)
VALUES
(1, 1, 'LAPRAK 1 PEMWEB', 'buat dapus', '2025-11-22 07:51:00', 'belum', 'tinggi'),
(1, 2, 'Normalisasi Database', 'kerjakan sampai 3NF', '2025-11-25 00:42:17', 'belum', 'sedang'),
(1, 3, 'Membuat CRUD PHP', 'selesaikan fitur tambah edit hapus', '2025-11-22 00:42:17', 'dikerjakan', 'tinggi');

-- =========================
-- VIEW DAFTAR TUGAS
-- =========================
CREATE VIEW v_daftar_tugas AS
SELECT 
    t.*,
    m.nama AS mata_kuliah,
    u.nama AS nama_mahasiswa,
    t.status AS nama_status
FROM tugas t
LEFT JOIN mata_kuliah m ON t.mk_id = m.mk_id
LEFT JOIN user_mahasiswa u ON t.user_id = u.user_id;

-- =========================
-- VIEW REPORT STATUS TUGAS
-- =========================
CREATE VIEW report_tugas_per_status AS
SELECT 
    status AS nama_status,
    COUNT(*) AS total_tugas
FROM tugas
GROUP BY status;

-- =========================
-- VIEW DEADLINE MEDET (VERSI SQLITE)
-- =========================
CREATE VIEW report_tugas_deadline_mepet AS
SELECT 
    t.judul,
    m.nama AS mata_kuliah,
    u.nama AS mahasiswa,
    t.tanggal_deadline,
    CAST((julianday(t.tanggal_deadline) - julianday('now')) * 24 AS INTEGER) AS sisa_jam
FROM tugas t
JOIN mata_kuliah m ON t.mk_id = m.mk_id
JOIN user_mahasiswa u ON t.user_id = u.user_id
WHERE t.status != 'selesai'
AND (julianday(t.tanggal_deadline) - julianday('now')) BETWEEN 0 AND 3;