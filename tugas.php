<?php
require 'config.php';
require 'layout.php';

checkAuth();
$user_id = $_SESSION['user_id'];

// Handle Pencarian & Filter User
$search = $_GET['q'] ?? '';
$search_param = "%$search%";

// Query mengambil tugas HANYA milik user yang login
// Kita join manual agar lebih fleksibel daripada view
$query = "
    SELECT t.*, m.nama as mata_kuliah 
    FROM tugas t
    LEFT JOIN mata_kuliah m ON t.mk_id = m.mk_id
    WHERE t.user_id = :uid 
    AND (t.judul LIKE :search OR m.nama LIKE :search)
    ORDER BY 
        CASE 
            WHEN t.status = 'belum' THEN 1 
            WHEN t.status = 'dikerjakan' THEN 2 
            ELSE 3 
        END,
        t.tanggal_deadline ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['uid' => $user_id, 'search' => $search_param]);
$tugas_list = $stmt->fetchAll();

renderHeader('Daftar Tugas');
renderSidebar('tugas.php');
?>

<div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Daftar Tugas</h2>
        <p class="text-slate-500">Kelola tugas-tugas kuliahmu di sini.</p>
    </div>
    
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        <form class="relative w-full md:w-64">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari tugas..." 
                   class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none shadow-sm">
        </form>
        <a href="tambah_tugas.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center justify-center gap-2 shadow-md transition-all whitespace-nowrap">
            <i class="ph ph-plus-circle text-lg"></i> Tambah Tugas Saya
        </a>
    </div>
</div>

<!-- Content -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-100 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4">Detail Tugas Saya</th>
                    <th class="px-6 py-4">Mata Kuliah</th>
                    <th class="px-6 py-4">Prioritas</th>
                    <th class="px-6 py-4">Deadline</th>
                    <th class="px-6 py-4">Status</th>
                    <!-- <th class="px-6 py-4 text-right">Aksi</th> -->
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($tugas_list)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400 gap-3">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                                    <i class="ph ph-clipboard-text text-3xl"></i>
                                </div>
                                <p>Belum ada tugas. <a href="tambah_tugas.php" class="text-indigo-600 font-bold hover:underline">Tambah sekarang?</a></p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($tugas_list as $t): ?>
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 text-base"><?= htmlspecialchars($t['judul']) ?></div>
                            <div class="text-xs text-slate-400 mt-1 line-clamp-1"><?= htmlspecialchars($t['deskripsi'] ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-medium">
                            <?= htmlspecialchars($t['mata_kuliah'] ?? 'Umum') ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                                $p_color = match($t['prioritas']) {
                                    'tinggi' => 'bg-red-50 text-red-700 border-red-200',
                                    'sedang' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-slate-50 text-slate-700 border-slate-200'
                                };
                            ?>
                            <span class="px-2.5 py-1 rounded text-xs font-bold border <?= $p_color ?> uppercase tracking-wide">
                                <?= ucfirst($t['prioritas']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                            <?= formatTanggal($t['tanggal_deadline']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                                $status_style = match($t['status']) {
                                    'selesai' => ['icon'=>'ph-check-circle', 'cls'=>'text-emerald-700 bg-emerald-50 border-emerald-100'],
                                    'dikerjakan' => ['icon'=>'ph-hourglass-medium', 'cls'=>'text-blue-700 bg-blue-50 border-blue-100'],
                                    default => ['icon'=>'ph-circle', 'cls'=>'text-slate-600 bg-slate-100 border-slate-200']
                                };
                            ?>
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full w-fit border <?= $status_style['cls'] ?>">
                                <i class="ph <?= $status_style['icon'] ?> text-lg"></i>
                                <span class="font-bold capitalize text-xs"><?= $t['status'] ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php renderFooter(); ?>