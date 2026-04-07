<?php
require 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form & Sanitasi
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $fakultas = trim($_POST['fakultas']);
    $prodi = trim($_POST['prodi']);

    // Validasi sederhana
    if (empty($nim) || empty($password) || empty($nama)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        // Cek apakah NIM sudah ada?
        $cek = $pdo->prepare("SELECT user_id FROM user_mahasiswa WHERE nim = ?");
        $cek->execute([$nim]);

        if ($cek->rowCount() > 0) {
            $error = "NIM tersebut sudah terdaftar!";
        } else {
            // Enkripsi Password biar aman
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan ke Database
            $sql = "INSERT INTO user_mahasiswa (nim, nama, email, password_hash, fakultas, prodi) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            try {
                if ($stmt->execute([$nim, $nama, $email, $hash, $fakultas, $prodi])) {
                    // Jika berhasil, langsung ke login dengan pesan sukses
                    header("Location: login.php?sukses=1");
                    exit();
                } else {
                    $error = "Gagal menyimpan data ke database.";
                }
            } catch (PDOException $e) {
                $error = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center py-10 px-4">

    <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="p-8">
            <h1 class="text-2xl font-bold text-indigo-600 mb-2">Buat Akun Baru</h1>
            <p class="text-slate-500 text-sm mb-6">Isi data diri mahasiswa untuk masuk ke sistem.</p>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm border border-red-100 flex items-center gap-2">
                    <span class="font-bold">Error:</span> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">NIM</label>
                        <input type="text" name="nim" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Fakultas</label>
                        <input type="text" name="fakultas" placeholder="cth: Teknik" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Prodi</label>
                        <input type="text" name="prodi" placeholder="cth: Informatika" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg mt-4 transition-all shadow-md">
                    Daftar Sekarang
                </button>
            </form>
        </div>
        <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
            <a href="login.php" class="text-sm text-indigo-600 font-medium hover:underline">Sudah punya akun? Login disini</a>
        </div>
    </div>

</body>
</html>