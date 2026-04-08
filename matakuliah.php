<?php
require 'config.php';
require 'layout.php';

$query = "SELECT * FROM mata_kuliah ORDER BY kode ASC";
$mk_list = $pdo->query($query)->fetchAll();

renderHeader('Data Master - Mata Kuliah');
renderSidebar('matakuliah.php');
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Master Mata Kuliah</h2>
        <p class="text-slate-500">Referensi mata kuliah yang tersedia.</p>
    </div>
    <button class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-md">
        + Mata Kuliah Baru
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?php foreach($mk_list as $mk): ?>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-2">
                <span class="px-2 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded uppercase tracking-wide">
                    <?= htmlspecialchars($mk['kode']) ?>
                </span>
                <button class="text-slate-400 hover:text-slate-600"><i class="ph ph-dots-three-vertical font-bold"></i></button>
            </div>
            <h3 class="font-bold text-lg text-slate-800 leading-tight mb-1"><?= htmlspecialchars($mk['nama']) ?></h3>
        </div>
        <div class="mt-4 flex items-center gap-2 text-sm text-slate-500 border-t border-slate-100 pt-4">
            <i class="ph ph-book-open text-lg"></i>
            <span>Beban Studi: <strong class="text-slate-700"><?= $mk['sks'] ?> SKS</strong></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php renderFooter(); ?>