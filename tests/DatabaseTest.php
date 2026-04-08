<?php
/**
 * DatabaseTest.php
 * Test koneksi SQLite dan struktur tabel RemindMe
 */

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private PDO $pdo;

    // Dijalankan sebelum setiap test — buat DB in-memory bersih
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Buat struktur tabel (sama persis seperti di config.php)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS user_mahasiswa (
            user_id   INTEGER PRIMARY KEY AUTOINCREMENT,
            nim       VARCHAR(20) UNIQUE NOT NULL,
            nama      VARCHAR(100) NOT NULL,
            email     VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            fakultas  VARCHAR(50),
            prodi     VARCHAR(50)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS mata_kuliah (
            mk_id  INTEGER PRIMARY KEY AUTOINCREMENT,
            kode   VARCHAR(20) NOT NULL,
            nama   VARCHAR(100) NOT NULL,
            sks    INTEGER
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS tugas (
            tugas_id         INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id          INTEGER,
            mk_id            INTEGER,
            judul            VARCHAR(200) NOT NULL,
            deskripsi        TEXT,
            tanggal_deadline DATETIME,
            status           VARCHAR(20) DEFAULT 'belum',
            prioritas        VARCHAR(20) DEFAULT 'sedang'
        )");
    }

    /** Test 1: Koneksi SQLite berhasil dibuat */
    public function testKoneksiSQLiteBerhasil(): void
    {
        $this->assertInstanceOf(PDO::class, $this->pdo);
    }

    /** Test 2: Tabel user_mahasiswa berhasil dibuat */
    public function testTabelUserMahasiswaAda(): void
    {
        $result = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_mahasiswa'");
        $this->assertNotFalse($result->fetch(), 'Tabel user_mahasiswa harus ada');
    }

    /** Test 3: Tabel mata_kuliah berhasil dibuat */
    public function testTabelMataKuliahAda(): void
    {
        $result = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='mata_kuliah'");
        $this->assertNotFalse($result->fetch(), 'Tabel mata_kuliah harus ada');
    }

    /** Test 4: Tabel tugas berhasil dibuat */
    public function testTabelTugasAda(): void
    {
        $result = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tugas'");
        $this->assertNotFalse($result->fetch(), 'Tabel tugas harus ada');
    }

    /** Test 5: Insert user baru berhasil */
    public function testInsertUserBerhasil(): void
    {
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_mahasiswa (nim, nama, email, password_hash, fakultas, prodi)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute(['2001001', 'Budi Santoso', 'budi@univ.ac.id', $hash, 'Teknik', 'Informatika']);

        $this->assertEquals(1, $this->pdo->lastInsertId());
    }

    /** Test 6: NIM harus unik — insert duplikat harus error */
    public function testNIMHarusUnik(): void
    {
        $this->expectException(PDOException::class);

        $hash = password_hash('pass', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_mahasiswa (nim, nama, email, password_hash) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute(['9999', 'User A', 'a@test.com', $hash]);
        $stmt->execute(['9999', 'User B', 'b@test.com', $hash]); // harus error
    }
}