<?php
// layout.php

function renderHeader($title) {
    // Pastikan user sudah login sebelum merender halaman (kecuali halaman login)
    if (basename($_SERVER['PHP_SELF']) != 'login.php') {
        checkAuth();
    }

    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $title . ' - RemindMe.</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
        <style>body { font-family: "Inter", sans-serif; }</style>
    </head>
    <body class="bg-slate-50 text-slate-800 antialiased">';
}

function renderSidebar($activePage) {
    // Ambil data user dari session
    $userName = $_SESSION['nama'] ?? 'Mahasiswa';
    $userProdi = $_SESSION['prodi'] ?? 'Umum';

    $menuItems = [
        'index.php' => ['icon' => 'ph-squares-four', 'label' => 'Dashboard'],
        'tugas.php' => ['icon' => 'ph-check-circle', 'label' => 'Daftar Tugas'],
        'matakuliah.php' => ['icon' => 'ph-books', 'label' => 'Mata Kuliah'],
        'mahasiswa.php' => ['icon' => 'ph-user', 'label' => 'Profil Saya'],
    ];

    echo '
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col shadow-xl z-20">
            <div class="p-6 border-b border-slate-800">
                <!-- LOGO / NAMA APLIKASI DENGAN TITIK -->
                <h1 class="text-2xl font-bold tracking-wide text-indigo-400 flex items-center gap-2">
                    <i class="ph ph-bell-ringing text-xl"></i>
                    RemindMe.</span>
                </h1>
            </div>
            <nav class="flex-1 p-4 space-y-2">';
            
            foreach ($menuItems as $link => $item) {
                $isActive = ($activePage == $link) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-slate-400 hover:bg-slate-800 hover:text-white';
                echo "<a href='$link' class='flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 $isActive'>
                        <i class='ph {$item['icon']} text-xl'></i>
                        <span class='font-medium text-sm'>{$item['label']}</span>
                      </a>";
            }

    echo '  </nav>
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-white text-lg">
                        '.substr($userName, 0, 1).'
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium truncate">'.$userName.'</p>
                        <p class="text-xs text-slate-500 truncate">'.$userProdi.'</p>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-2 rounded border border-slate-700 text-xs text-slate-400 hover:bg-red-900/20 hover:text-red-400 hover:border-red-900 transition-colors">
                    <i class="ph ph-sign-out"></i> Logout
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen overflow-y-auto bg-slate-50 relative">
            <main class="p-6 md:p-8 max-w-7xl mx-auto w-full">';
}

function renderFooter() {
    echo '
            </main>
        </div>
    </div>
    </body>
    </html>';
}
?>