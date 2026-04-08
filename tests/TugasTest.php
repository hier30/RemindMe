<?php
/**
 * TugasTest.php
 * Test operasi CRUD untuk fitur tugas RemindMe
 */

use PHPUnit\Framework\TestCase;

class TugasTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec("CREATE TABLE mata_kuliah (
            mk_id  INTEGER PRIMARY KEY AUTOINCREMENT,
            kode   VARCHAR(20) NOT NULL,
            nama   VARCHAR(100) NOT NULL,
            sks    INTEGER
        )");

        $this->pdo->exec("CREATE TABLE tugas (
            tugas_id         INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id          INTEGER NOT NULL,
            mk_id            INTEGER,
            judul            VARCHAR(200) NOT NULL,
            deskripsi        TEXT,
            tanggal_deadline DATETIME,
            status           VARCHAR(20) DEFAULT 'belum',
            prioritas        VARCHAR(20) DEFAULT 'sedang'
        )");

        // Seed mata kuliah & tugas dummy
        $this->pdo->exec("INSERT INTO mata_kuliah (kode, nama, sks) VALUES ('IF101', 'Pemrograman Web', 3)");
        $this->pdo->exec("INSERT INTO tugas (user_id, mk_id, judul, tanggal_deadline, status, prioritas)
            VALUES (1, 1, 'Membuat CRUD PHP', datetime('now', '+5 days'), 'belum', 'tinggi')");
    }

    /** Test 13: Tambah tugas baru berhasil tersimpan */
    public function testTambahTugasBerhasil(): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tugas (user_id, mk_id, judul, tanggal_deadline, status, prioritas)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([1, 1, 'Laporan Praktikum', '2025-12-31 23:59:00', 'belum', 'sedang']);

        $total = $this->pdo->query("SELECT COUNT(*) as total FROM tugas WHERE user_id = 1")->fetch();
        $this->assertEquals(2, $total['total'], 'Harus ada 2 tugas setelah insert');
    }

    /** Test 14: Update status tugas dari 'belum' ke 'dikerjakan' */
    public function testUpdateStatusTugasBerhasil(): void
    {
        $this->pdo->exec("UPDATE tugas SET status = 'dikerjakan' WHERE tugas_id = 1");

        $tugas = $this->pdo->query("SELECT status FROM tugas WHERE tugas_id = 1")->fetch();
        $this->assertEquals('dikerjakan', $tugas['status']);
    }

    /** Test 15: Hapus tugas berhasil */
    public function testHapusTugasBerhasil(): void
    {
        $this->pdo->exec("DELETE FROM tugas WHERE tugas_id = 1");

        $total = $this->pdo->query("SELECT COUNT(*) as total FROM tugas")->fetch();
        $this->assertEquals(0, $total['total'], 'Tugas harus terhapus');
    }

    /** Test 16: Query tugas hanya milik user yang login (isolasi data antar user) */
    public function testTugasHanyaMilikUserSendiri(): void
    {
        // Tambah tugas milik user lain (user_id = 2)
        $this->pdo->exec("INSERT INTO tugas (user_id, mk_id, judul, status, prioritas)
            VALUES (2, 1, 'Tugas Milik Orang Lain', 'belum', 'rendah')");

        $stmt = $this->pdo->prepare("SELECT * FROM tugas WHERE user_id = ?");
        $stmt->execute([1]);
        $tugas_user1 = $stmt->fetchAll();

        $this->assertCount(1, $tugas_user1, 'User 1 hanya boleh lihat tugasnya sendiri');
        $this->assertEquals('Membuat CRUD PHP', $tugas_user1[0]['judul']);
    }

    /** Test 17: Status tugas hanya boleh nilai yang valid */
    public function testStatusTugasValid(): void
    {
        $valid_status = ['belum', 'dikerjakan', 'selesai'];

        $tugas = $this->pdo->query("SELECT status FROM tugas WHERE tugas_id = 1")->fetch();
        $this->assertContains($tugas['status'], $valid_status, 'Status harus salah satu dari: belum, dikerjakan, selesai');
    }

    /** Test 18: Prioritas tugas hanya boleh nilai yang valid */
    public function testPrioritasTugasValid(): void
    {
        $valid_prioritas = ['rendah', 'sedang', 'tinggi'];

        $tugas = $this->pdo->query("SELECT prioritas FROM tugas WHERE tugas_id = 1")->fetch();
        $this->assertContains($tugas['prioritas'], $valid_prioritas, 'Prioritas harus salah satu dari: rendah, sedang, tinggi');
    }

    /** Test 19: Join tugas dengan mata_kuliah berhasil */
    public function testJoinTugasDanMataKuliah(): void
    {
        $stmt = $this->pdo->prepare("
            SELECT t.judul, m.nama as mata_kuliah
            FROM tugas t
            LEFT JOIN mata_kuliah m ON t.mk_id = m.mk_id
            WHERE t.user_id = ?
        ");
        $stmt->execute([1]);
        $result = $stmt->fetch();

        $this->assertEquals('Membuat CRUD PHP', $result['judul']);
        $this->assertEquals('Pemrograman Web', $result['mata_kuliah']);
    }
}