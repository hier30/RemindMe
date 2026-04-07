<?php
require 'config.php';
require 'layout.php';

// Pastikan user login
checkAuth();
$user_id = $_SESSION['user_id'];

// 1. INISIALISASI STATISTIK DEFAULT 0
$stats = [
    'belum' => 0,
    'dikerjakan' => 0,
    'selesai' => 0
];
$total_tugas = 0;

// 2. QUERY HITUNG TUGAS (HANYA MILIK USER YANG LOGIN)
// Kita tidak pakai VIEW global, tapi query langsung dengan WHERE user_id
$stmt = $pdo->prepare("SELECT status, COUNT(*) as jumlah FROM tugas WHERE user_id = ? GROUP BY status");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll();

// Update angka statistik berdasarkan hasil database
foreach ($rows as $row) {
    $status_key = strtolower($row['status']); // pastikan lowercase
    if (isset($stats[$status_key])) {
        $stats[$status_key] = $row['jumlah'];
        $total_tugas += $row['jumlah'];
    }
}

// 3. QUERY TUGAS MENDESAK (DEADLINE < 3 HARI)
// Query ini kompatibel untuk SQLite dan MySQL (menggunakan logika umum atau PHP filtering jika perlu)
// Kita ambil tugas yang statusnya belum selesai dan milik user ini
$query_urgent = "
    SELECT t.*, m.nama as mata_kuliah 
    FROM tugas t
    LEFT JOIN mata_kuliah m ON t.mk_id = m.mk_id
    WHERE t.user_id = ? 
    AND t.status != 'selesai'
    ORDER BY t.tanggal_deadline ASC
";
$stmt_urgent = $pdo->prepare($query_urgent);
$stmt_urgent->execute([$user_id]);
$all_tasks = $stmt_urgent->fetchAll();

// Filter urgent menggunakan PHP agar aman untuk semua jenis Database (SQLite/MySQL)
$urgent_tasks = [];
$now = new DateTime();
foreach ($all_tasks as $task) {
    $deadline = new DateTime($task['tanggal_deadline']);
    $diff = $now->diff($deadline);
    $days = $diff->days;
    
    // Jika deadline sudah lewat atau kurang dari 3 hari
    // invert = 0 berarti future, 1 berarti past
    if ($diff->invert == 0 && $days <= 3) {
        $task['sisa_jam'] = ($days * 24) + $diff->h;
        $urgent_tasks[] = $task;
    } elseif ($diff->invert == 1) {
        // Jika sudah lewat deadline (terlambat)
        $task['sisa_jam'] = -1; // Penanda telat
        $urgent_tasks[] = $task;
    }
    
    if (count($urgent_tasks) >= 5) break; // Limit 5
}

renderHeader('Dashboard');
renderSidebar('index.php');
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard Overview</h2>
    <p class="text-slate-500">Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>! Berikut rekap tugasmu.</p>
</div>

<!-- GRID STATISTIK RESPONSIF -->
<!-- Menggunakan grid-cols-1 (HP) -> sm:grid-cols-2 (Tablet) -> xl:grid-cols-4 (Laptop) -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-8">
    
    <!-- Card Total -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-indigo-300 transition-colors">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Tugas</p>
                <h3 class="text-3xl font-bold text-slate-800"><?= $total_tugas ?></h3>
            </div>
            <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg"><i class="ph ph-stack text-xl"></i></span>
        </div>
    </div>

    <!-- Card Belum -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-amber-300 transition-colors">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">Belum</p>
                <h3 class="text-3xl font-bold text-slate-800"><?= $stats['belum'] ?></h3>
            </div>
            <span class="p-2.5 bg-amber-50 text-amber-600 rounded-lg"><i class="ph ph-circle text-xl"></i></span>
        </div>
    </div>

    <!-- Card Dikerjakan -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-blue-300 transition-colors">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1">Dikerjakan</p>
                <h3 class="text-3xl font-bold text-slate-800"><?= $stats['dikerjakan'] ?></h3>
            </div>
            <span class="p-2.5 bg-blue-50 text-blue-600 rounded-lg"><i class="ph ph-hourglass-medium text-xl"></i></span>
        </div>
    </div>

    <!-- Card Selesai -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-emerald-300 transition-colors">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-1">Selesai</p>
                <h3 class="text-3xl font-bold text-slate-800"><?= $stats['selesai'] ?></h3>
            </div>
            <span class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg"><i class="ph ph-check-circle text-xl"></i></span>
        </div>
    </div>
</div>

<!-- TABLE SECTION -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-5 md:p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h3 class="font-bold text-slate-800 flex items-center gap-2">
            <i class="ph ph-warning-circle text-red-500 text-xl"></i> 
            Deadline Mendesak (≤ 3 Hari)
        </h3>
        <a href="tugas.php" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            Lihat Semua <i class="ph ph-arrow-right"></i>
        </a>
    </div>
    
    <!-- Wrapper untuk scroll horizontal di mobile -->
    <div class="overflow-x-auto w-full">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4">Judul Tugas</th>
                    <th class="px-6 py-4">Mata Kuliah</th>
                    <th class="px-6 py-4">Sisa Waktu</th>
                    <th class="px-6 py-4">Deadline</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($urgent_tasks)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="ph ph-smiley text-4xl mb-2"></i>
                                <p>Tidak ada tugas yang deadline. Aman!</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($urgent_tasks as $task): 
                        $is_late = $task['sisa_jam'] == -1;
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-800">
                            <?= htmlspecialchars($task['judul']) ?>
                            <?php if($is_late): ?>
                                <span class="ml-2 text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-bold uppercase">Telat</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($task['mata_kuliah']) ?></td>
                        <td class="px-6 py-4">
                            <?php if($is_late): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    -
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                    <i class="ph ph-clock"></i> <?= $task['sisa_jam'] ?> Jam
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-slate-500 font-mono text-xs"><?= formatTanggal($task['tanggal_deadline']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php renderFooter(); ?>