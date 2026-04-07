<?php
require 'config.php';
require 'layout.php';

checkAuth();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch Data Mata Kuliah untuk Dropdown
$stmt_mk = $pdo->query("SELECT * FROM mata_kuliah ORDER BY nama ASC");
$mk_list = $stmt_mk->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $mk_id = $_POST['mk_id'];
    $deadline = $_POST['deadline'];
    $prioritas = $_POST['prioritas'];
    $deskripsi = $_POST['deskripsi'];
    
    if(empty($judul) || empty($deadline)) {
        $error = "Judul dan Deadline wajib diisi.";
    } else {
        // Insert ke database
        $sql = "INSERT INTO tugas (user_id, mk_id, judul, deskripsi, tanggal_deadline, status, prioritas) 
                VALUES (?, ?, ?, ?, ?, 'belum', ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$user_id, $mk_id, $judul, $deskripsi, $deadline, $prioritas])) {
            // Redirect ke daftar tugas setelah sukses
            header("Location: tugas.php");
            exit();
        } else {
            $error = "Gagal menyimpan data.";
        }
    }
}

renderHeader('Tambah Tugas Baru');
renderSidebar('tugas.php');
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="tugas.php" class="text-slate-500 hover:text-slate-800 flex items-center gap-2 text-sm mb-2 transition-colors">
            <i class="ph ph-arrow-left"></i> Kembali ke Daftar
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Tambah Tugas Baru</h2>
        <p class="text-slate-500">Masukkan detail tugas kuliah yang perlu diselesaikan.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 md:p-8">
            
            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm font-medium flex items-center gap-2 border border-red-100">
                    <i class="ph ph-warning-circle text-lg"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                
                <!-- Judul -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Judul Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" required placeholder="Misal: Laporan Praktikum Modul 1"
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mata Kuliah -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Mata Kuliah</label>
                        <div class="relative">
                            <select name="mk_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none appearance-none bg-white">
                                <option value="">-- Pilih Mata Kuliah --</option>
                                <?php foreach($mk_list as $mk): ?>
                                    <option value="<?= $mk['mk_id'] ?>"><?= htmlspecialchars($mk['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Prioritas -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Prioritas</label>
                        <div class="relative">
                            <select name="prioritas" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none appearance-none bg-white">
                                <option value="rendah">Rendah</option>
                                <option value="sedang" selected>Sedang</option>
                                <option value="tinggi">Tinggi</option>
                            </select>
                            <i class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Deadline Pengumpulan <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="deadline" required
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-slate-600">
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Catatan / Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Tambahkan detail tugas di sini..."
                              class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
                </div>

                <!-- Tombol -->
                <div class="pt-4 flex items-center gap-4 border-t border-slate-100 mt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-lg shadow-lg shadow-indigo-200 transition-all flex justify-center items-center gap-2">
                        <i class="ph ph-floppy-disk text-xl"></i> Simpan Tugas
                    </button>
                    <a href="tugas.php" class="px-6 py-3.5 rounded-lg border border-slate-300 text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php renderFooter(); ?>