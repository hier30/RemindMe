<?php
/**
 * AuthTest.php
 * Test logika autentikasi dan helper functions RemindMe
 */

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec("CREATE TABLE user_mahasiswa (
            user_id   INTEGER PRIMARY KEY AUTOINCREMENT,
            nim       VARCHAR(20) UNIQUE NOT NULL,
            nama      VARCHAR(100) NOT NULL,
            email     VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            fakultas  VARCHAR(50),
            prodi     VARCHAR(50)
        )");

        // Seed: 1 user demo
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_mahasiswa (nim, nama, email, password_hash, fakultas, prodi)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute(['2001001', 'Mahasiswa Demo', 'demo@univ.ac.id', $hash, 'Teknik', 'Informatika']);
    }

    /** Test 7: Login dengan NIM & password benar → user ditemukan */
    public function testLoginPasswordBenarBerhasil(): void
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_mahasiswa WHERE nim = ?");
        $stmt->execute(['2001001']);
        $user = $stmt->fetch();

        $this->assertNotFalse($user, 'User harus ditemukan');
        $this->assertTrue(
            password_verify('password123', $user['password_hash']),
            'Password harus cocok'
        );
    }

    /** Test 8: Login dengan password salah → verify gagal */
    public function testLoginPasswordSalahGagal(): void
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_mahasiswa WHERE nim = ?");
        $stmt->execute(['2001001']);
        $user = $stmt->fetch();

        $this->assertFalse(
            password_verify('passwordSalah', $user['password_hash']),
            'Password salah harus ditolak'
        );
    }

    /** Test 9: Login dengan NIM tidak terdaftar → user tidak ditemukan */
    public function testLoginNIMTidakAdaGagal(): void
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_mahasiswa WHERE nim = ?");
        $stmt->execute(['9999999']);
        $user = $stmt->fetch();

        $this->assertFalse($user, 'NIM tidak terdaftar harus return false');
    }

    /** Test 10: Password tersimpan dalam bentuk hash, bukan plain text */
    public function testPasswordDisimpanSebagaiHash(): void
    {
        $stmt = $this->pdo->prepare("SELECT password_hash FROM user_mahasiswa WHERE nim = ?");
        $stmt->execute(['2001001']);
        $row = $stmt->fetch();

        $this->assertNotEquals('password123', $row['password_hash'], 'Password tidak boleh tersimpan plain text');
        $this->assertStringStartsWith('$2', $row['password_hash'], 'Harus pakai bcrypt (prefix $2)');
    }

    /** Test 11: Helper formatTanggal() menghasilkan format yang benar */
    public function testFormatTanggalBenar(): void
    {
        // Duplikasi fungsi dari config.php untuk di-test secara terisolasi
        $formatTanggal = function (string $date): string {
            return date('d M Y, H:i', strtotime($date));
        };

        $hasil = $formatTanggal('2025-12-31 23:59:00');
        $this->assertEquals('31 Dec 2025, 23:59', $hasil);
    }

    /** Test 12: Helper formatTanggal() tidak crash untuk string kosong */
    public function testFormatTanggalStringKosong(): void
    {
        $formatTanggal = function (string $date): string {
            return date('d M Y, H:i', strtotime($date));
        };

        $hasil = $formatTanggal('');
        $this->assertIsString($hasil, 'Harus tetap return string meski input kosong');
    }
}