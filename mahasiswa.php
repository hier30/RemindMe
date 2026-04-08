<?php
require 'config.php';
require 'layout.php';

checkAuth();
$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = ''; // success / error

// 1. HANDLE UPDATE PROFIL
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $fakultas = trim($_POST['fakultas']);
    $prodi = trim($_POST['prodi']);
    $password_baru = $_POST['password_baru'];

    if (empty($nama) || empty($email)) {
        $message = "Nama dan Email tidak boleh kosong.";
        $msg_type = 'error';
    } else {
        try {
            if (!empty($password_baru)) {
                // Update dengan Password Baru
                $hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE user_mahasiswa SET nama=?, email=?, fakultas=?, prodi=?, password_hash=? WHERE user_id=?");
                $stmt->execute([$nama, $email, $fakultas, $prodi, $hash, $user_id]);
            } else {
                // Update Tanpa Ganti Password
                $stmt = $pdo->prepare("UPDATE user_mahasiswa SET nama=?, email=?, fakultas=?, prodi=? WHERE user_id=?");
                $stmt->execute([$nama, $email, $fakultas, $prodi, $user_id]);
            }

            // Update Session agar sidebar langsung berubah
            $_SESSION['nama'] = $nama;
            $_SESSION['prodi'] = $prodi;

            $message = "Profil berhasil diperbarui!";
            $msg_type = 'success';
        } catch (PDOException $e) {
            $message = "Gagal update: " . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

// 2. AMBIL DATA USER TERBARU
$stmt = $pdo->prepare("SELECT * FROM user_mahasiswa WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Hitung statistik tugas user ini untuk ditampilkan di kartu profil
$stmt_stats = $pdo->prepare("SELECT COUNT(*) as total FROM tugas WHERE user_id = ?");
$stmt_stats->execute([$user_id]);
$total_tugas = $stmt_stats->fetch()['total'];

renderHeader('Profil Mahasiswa');
renderSidebar('mahasiswa.php');
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Profil Mahasiswa</h2>
    <p class="text-slate-500">Kelola informasi akun dan data akademik.</p>
</div>

<?php if($message): ?>
    <div class="<?= $msg_type == 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' ?> border px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <i class="ph <?= $msg_type == 'success' ? 'ph-check-circle' : 'ph-warning-circle' ?> text-lg"></i>
        <?= $message ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- KARTU PROFIL (KIRI) -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-center h-fit">
            <div class="w-24 h-24 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                <?= substr($user['nama'], 0, 1) ?>
            </div>
            <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($user['nama']) ?></h3>
            <p class="text-slate-500 text-sm mb-4"><?= htmlspecialchars($user['nim']) ?></p>
            
            <div class="border-t border-slate-100 pt-4 mt-4 flex justify-between items-center text-sm">
                <span class="text-slate-500">Total Tugas</span>
                <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded"><?= $total_tugas ?></span>
            </div>
            <div class="border-t border-slate-100 pt-4 mt-2 flex justify-between items-center text-sm">
                <span class="text-slate-500">Status</span>
                <span class="font-bold text-emerald-600 flex items-center gap-1">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div> Aktif
                </span>
            </div>
        </div>
    </div>

    <!-- FORM EDIT DATA (KANAN) -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Edit Informasi Akun</h3>
            </div>
            
            <form method="POST" class="p-6 space-y-5">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- NIM (Read Only) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">NIM (Tidak bisa diubah)</label>
                        <input type="text" value="<?= htmlspecialchars($user['nim']) ?>" disabled
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 font-mono">
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Fakultas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fakultas</label>
                        <input type="text" name="fakultas" value="<?= htmlspecialchars($user['fakultas']) ?>"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    
                    <!-- Prodi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Program Studi</label>
                        <input type="text" name="prodi" value="<?= htmlspecialchars($user['prodi']) ?>"
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-5 mt-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ganti Password <span class="text-slate-400 font-normal normal-case">(Kosongkan jika tidak ingin mengganti)</span></label>
                    <input type="password" name="password_baru" placeholder="Masukkan password baru..."
                           class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-all flex items-center gap-2">
                        <i class="ph ph-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<?php renderFooter(); ?>