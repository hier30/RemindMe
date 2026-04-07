<?php
// config.php
session_start();

// KONFIGURASI DATABASE (Menggunakan SQLite agar langsung jalan tanpa setup XAMPP)
// Jika ingin pakai MySQL, ubah $driver ke 'mysql' dan isi $host, $dbname, dll.
$driver = 'sqlite'; 

// Untuk MySQL (XAMPP/Laragon)
$host = 'localhost';
$dbname = 'db_pengingat_tugas';
$username = 'root';
$password = '';

try {
    if ($driver === 'sqlite') {
        // Membuat file database lokal
        $pdo = new PDO("sqlite:database.sqlite");
    } else {
        // Koneksi ke MySQL Server
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- AUTO SETUP (Hanya berjalan jika tabel belum ada) ---
    // Ini memastikan database memiliki struktur tabel yang benar saat pertama kali dijalankan
    initializeDatabase($pdo, $driver);

} catch (PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage() . "<br>Pastikan driver database benar.");
}

// Helper: Cek Login
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function formatTanggal($date) {
    return date('d M Y, H:i', strtotime($date));
}

// FUNGSI UNTUK MEMBUAT STRUKTUR DATABASE OTOMATIS
function initializeDatabase($pdo, $driver) {
    // Cek apakah tabel user sudah ada
    try {
        $result = $pdo->query("SELECT 1 FROM user_mahasiswa LIMIT 1");
    } catch (Exception $e) {
        // Jika error, berarti tabel belum ada. Buat tabelnya.
        
        // 1. Tabel User Mahasiswa
        $autoInc = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_mahasiswa (
            user_id $autoInc,
            nim VARCHAR(20) UNIQUE NOT NULL,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            fakultas VARCHAR(50),
            prodi VARCHAR(50)
        )");

        // 2. Tabel Mata Kuliah
        $pdo->exec("CREATE TABLE IF NOT EXISTS mata_kuliah (
            mk_id $autoInc,
            kode VARCHAR(20) NOT NULL,
            nama VARCHAR(100) NOT NULL,
            sks INTEGER
        )");

        // 3. Tabel Tugas
        $pdo->exec("CREATE TABLE IF NOT EXISTS tugas (
            tugas_id $autoInc,
            user_id INTEGER,
            mk_id INTEGER,
            judul VARCHAR(200) NOT NULL,
            deskripsi TEXT,
            tanggal_deadline DATETIME,
            status VARCHAR(20) DEFAULT 'belum', -- belum, dikerjakan, selesai
            prioritas VARCHAR(20) DEFAULT 'sedang' -- rendah, sedang, tinggi
        )");

        // 4. Membuat VIEW untuk Laporan (Penyederhanaan Query)
        // View Daftar Tugas Lengkap
        $pdo->exec("CREATE VIEW IF NOT EXISTS v_daftar_tugas AS
            SELECT t.*, m.nama as mata_kuliah, u.nama as nama_mahasiswa, 
                   t.status as nama_status
            FROM tugas t
            LEFT JOIN mata_kuliah m ON t.mk_id = m.mk_id
            LEFT JOIN user_mahasiswa u ON t.user_id = u.user_id
        ");

        // View Report Status (Kompatibel SQLite & MySQL)
        $pdo->exec("CREATE VIEW IF NOT EXISTS report_tugas_per_status AS
            SELECT status as nama_status, COUNT(*) as total_tugas
            FROM tugas
            GROUP BY status
        ");

        // View Deadline Mepet (Menangani perbedaan fungsi waktu SQLite vs MySQL)
        if ($driver === 'sqlite') {
            // Syntax SQLite untuk selisih waktu (Julianday)
            $pdo->exec("CREATE VIEW IF NOT EXISTS report_tugas_deadline_mepet AS
                SELECT t.judul, m.nama as mata_kuliah, u.nama as mahasiswa, t.tanggal_deadline,
                       CAST((julianday(t.tanggal_deadline) - julianday('now')) * 24 AS INTEGER) as sisa_jam
                FROM tugas t
                JOIN mata_kuliah m ON t.mk_id = m.mk_id
                JOIN user_mahasiswa u ON t.user_id = u.user_id
                WHERE status != 'selesai' 
                AND (julianday(t.tanggal_deadline) - julianday('now')) BETWEEN 0 AND 3
            ");
        } else {
            // Syntax MySQL
            $pdo->exec("CREATE VIEW IF NOT EXISTS report_tugas_deadline_mepet AS
                SELECT t.judul, m.nama as mata_kuliah, u.nama as mahasiswa, t.tanggal_deadline,
                       TIMESTAMPDIFF(HOUR, NOW(), t.tanggal_deadline) as sisa_jam
                FROM tugas t
                JOIN mata_kuliah m ON t.mk_id = m.mk_id
                JOIN user_mahasiswa u ON t.user_id = u.user_id
                WHERE status != 'selesai' 
                AND DATEDIFF(t.tanggal_deadline, NOW()) <= 3 AND t.tanggal_deadline > NOW()
            ");
        }

        // --- SEEDING DATA DUMMY (Agar tidak kosong saat pertama buka) ---
        
        // Buat Akun Demo: NIM 2001001, Pass: password123
        $passHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user_mahasiswa (nim, nama, email, password_hash, fakultas, prodi) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['2001001', 'Mahasiswa Demo', 'demo@univ.ac.id', $passHash, 'Teknik', 'Informatika']);

        // Buat Mata Kuliah Dummy
        $pdo->exec("INSERT INTO mata_kuliah (kode, nama, sks) VALUES 
            ('IF101', 'Pemrograman Web', 3),
            ('IF102', 'Basis Data', 4),
            ('IF103', 'Algoritma', 3)");

        // Buat Tugas Dummy
        $pdo->exec("INSERT INTO tugas (user_id, mk_id, judul, tanggal_deadline, status, prioritas) VALUES 
            (1, 1, 'Membuat CRUD PHP', datetime('now', '+2 days'), 'dikerjakan', 'tinggi'),
            (1, 2, 'Normalisasi Database', datetime('now', '+5 days'), 'belum', 'sedang')");
    }
}
?>
