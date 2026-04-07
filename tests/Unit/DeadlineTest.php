<?php
use PHPUnit\Framework\TestCase;

class DeadlineTest extends TestCase
{
    // Test: hitung sisa hari sampai deadline
    public function testHitungSisaHari()
    {
        $deadline = new DateTime('+3 days');
        $now = new DateTime();
        $diff = $now->diff($deadline);
        
        $this->assertEquals(3, $diff->days);
    }
    
    // Test: cek deadline mendesak (≤ 3 hari)
    public function testCekDeadlineMendesak()
    {
        $testCases = [
            ['deadline' => '+1 day', 'expected' => true],
            ['deadline' => '+2 days', 'expected' => true],
            ['deadline' => '+3 days', 'expected' => true],
            ['deadline' => '+4 days', 'expected' => false],
            ['deadline' => '+7 days', 'expected' => false],
        ];
        
        foreach ($testCases as $case) {
            $deadline = new DateTime($case['deadline']);
            $now = new DateTime();
            $diff = $now->diff($deadline);
            $isUrgent = ($diff->invert == 0 && $diff->days <= 3);
            
            $this->assertEquals($case['expected'], $isUrgent,
                "Deadline {$case['deadline']} mendesak: {$case['expected']}");
        }
    }
    
    // Test: cek tugas terlambat
    public function testCekTugasTerlambat()
    {
        $testCases = [
            ['deadline' => '-1 day', 'expected' => true],
            ['deadline' => '-5 days', 'expected' => true],
            ['deadline' => '+1 day', 'expected' => false],
            ['deadline' => 'now', 'expected' => false],
        ];
        
        foreach ($testCases as $case) {
            $deadline = new DateTime($case['deadline']);
            $now = new DateTime();
            $isLate = ($deadline < $now);
            
            $this->assertEquals($case['expected'], $isLate,
                "Deadline {$case['deadline']} terlambat: {$case['expected']}");
        }
    }
}