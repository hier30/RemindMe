<?php
require 'config.php';

$error = '';
$success = $_GET['sukses'] ?? '';

// Jika sudah login, lempar ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim']);
    $password = $_POST['password'];

    // Cari user berdasarkan NIM
    // Menggunakan prepared statement untuk keamanan (Anti SQL Injection)
    $stmt = $pdo->prepare("SELECT * FROM user_mahasiswa WHERE nim = ?");
    $stmt->execute([$nim]);
    $user = $stmt->fetch();

    // Verifikasi Password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set Session Variabel
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['nim'] = $user['nim'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['prodi'] = $user['prodi'];
        
        // Redirect ke dashboard
        header("Location: index.php");
        exit();
    } else {
        $error = "NIM tidak ditemukan atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaskManager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-indigo-600">Task<span class="text-slate-800">Manager</span></h1>
                <p class="text-slate-500 text-sm mt-2">Silakan login untuk mengelola tugas kuliah.</p>
            </div>

            <?php if($success): ?>
                <div class="bg-green-50 text-green-600 px-4 py-3 rounded-lg text-sm mb-6 border border-green-100 text-center">
                    Registrasi berhasil! Silakan login.
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm mb-6 border border-red-100 flex items-center justify-center text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-2">NIM</label>
                    <input type="text" name="nim" required 
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                           placeholder="Contoh: 2001001">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                           placeholder="••••••••">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-all shadow-lg shadow-indigo-500/30">
                    Masuk Sekarang
                </button>
            </form>
            
            <!-- Info Login Demo -->
            <div class="mt-6 p-3 bg-indigo-50 rounded border border-indigo-100 text-xs text-indigo-800 text-center">
                <p class="font-bold">Akun Demo:</p>
                <p>NIM: 2001001 | Pass: password123</p>
            </div>

        </div>
        <div class="bg-slate-50 px-8 py-4 text-center border-t border-slate-100">
            <p class="text-xs text-slate-500">Belum punya akun? <a href="register.php" class="text-indigo-600 font-bold hover:underline">Daftar Disini</a></p>
        </div>
    </div>

</body>
</html>