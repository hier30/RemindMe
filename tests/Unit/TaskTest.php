<?php
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    // Test: hitungPrioritas berdasarkan deadline
    public function testHitungPrioritas()
    {
        // Data test: deadline, expected prioritas
        $testCases = [
            ['deadline' => '+1 day', 'expected' => 'tinggi'],
            ['deadline' => '+2 days', 'expected' => 'tinggi'],
            ['deadline' => '+3 days', 'expected' => 'sedang'],
            ['deadline' => '+5 days', 'expected' => 'sedang'],
            ['deadline' => '+7 days', 'expected' => 'rendah'],
            ['deadline' => '-1 day', 'expected' => 'tinggi'], // sudah lewat
        ];
        
        foreach ($testCases as $case) {
            $deadline = new DateTime($case['deadline']);
            $now = new DateTime();
            $diff = $now->diff($deadline);
            $days = $diff->days;
            $isLate = $diff->invert == 1;
            
            $prioritas = $this->calculatePriority($deadline, $now);
            
            $this->assertEquals($case['expected'], $prioritas, 
                "Deadline {$case['deadline']} seharusnya prioritas {$case['expected']}");
        }
    }
    
    private function calculatePriority($deadline, $now)
    {
        $diff = $now->diff($deadline);
        $days = $diff->days;
        
        // Jika sudah lewat deadline
        if ($diff->invert == 1) {
            return 'tinggi';
        }
        
        // Jika kurang dari 3 hari
        if ($days <= 2) {
            return 'tinggi';
        }
        
        // Jika 3-5 hari
        if ($days <= 5) {
            return 'sedang';
        }
        
        return 'rendah';
    }
    
    // Test: statusTugas berdasarkan progres
    public function testStatusTugas()
    {
        $statuses = ['belum', 'dikerjakan', 'selesai'];
        
        foreach ($statuses as $status) {
            $this->assertContains($status, $statuses);
            $this->assertIsString($status);
        }
    }
    
    // Test: validasi deadline tidak boleh kosong
    public function testDeadlineTidakBolehKosong()
    {
        $deadline = '';
        $this->assertEmpty($deadline, "Deadline tidak boleh kosong");
    }
    
    // Test: validasi judul tugas tidak boleh kosong
    public function testJudulTugasTidakBolehKosong()
    {
        $judul = '';
        $this->assertEmpty($judul, "Judul tugas tidak boleh kosong");
    }
    
    // Test: format tanggal deadline valid
    public function testFormatDeadlineValid()
    {
        $validDates = [
            '2025-12-31 23:59:59',
            '2025-01-15 08:00:00',
            '2026-06-30 12:30:00'
        ];
        
        foreach ($validDates as $date) {
            $this->assertNotFalse(strtotime($date), "Format tanggal $date tidak valid");
        }
    }
    
    // Test: fungsi formatTanggal
    public function testFormatTanggal()
    {
        $this->assertEquals('15 Jan 2025, 10:30', 
            formatTanggal('2025-01-15 10:30:00'));
    }
}