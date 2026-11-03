<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Notifikasi";
$app_name = "SIPOLI";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= $app_name ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Fontawesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">

                <!-- Container Utama Notifikasi -->
                <div class="bg-white block p-6 md:p-8 border border-gray-200 rounded-base shadow-sm">
                    <h4 class="text-xl font-bold text-gray-900">Daftar pemberitahuan</h4>
                    <p class="text-sm text-gray-500 mt-2 mb-6">Kelola peringatan stok obat, kabar dari dokter, dan konfirmasi pasien</p>

                    <!-- List Notifikasi -->
                    <div class="flex flex-col gap-4">

                        <!-- Item Notifikasi 1 -->
                        <div class="bg-primary-50 border border-primary-100 rounded-base p-5">
                            <p class="text-xs text-gray-500 mb-2">10 Menit yang lalu</p>
                            <h5 class="text-base font-bold text-gray-900 mb-2">Peringatan: Stok obat menipis</h5>
                            <p class="text-sm text-gray-600">Stok Paracetamol 500mg telah mencapai batas minimum. Sisa saat ini: 15 tablet.</p>
                            <p class="text-xs font-bold text-primary-600 mt-4">Sistem obat</p>
                        </div>

                        <!-- Item Notifikasi 2 -->
                        <div class="bg-primary-50 border border-primary-100 rounded-base p-5">
                            <p class="text-xs text-gray-500 mb-2">1 Jam yang lalu</p>
                            <h5 class="text-base font-bold text-gray-900 mb-2">Pemberitahuan jadwal praktik dokter</h5>
                            <p class="text-sm text-gray-600">dr. Sarah Amelia, Sp.KJ mengonfirmasi akan datang terlambat 30 menit pada sesi hari ini.</p>
                            <p class="text-xs font-bold text-primary-600 mt-4">Info dokter</p>
                        </div>

                        <!-- Item Notifikasi 3 -->
                        <div class="bg-primary-50 border border-primary-100 rounded-base p-5">
                            <p class="text-xs text-gray-500 mb-2">Kemarin, 14.30</p>
                            <h5 class="text-base font-bold text-gray-900 mb-2">Konfirmasi kehadiran konseling</h5>
                            <p class="text-sm text-gray-600">Pasien Alya Prameswari (2341720101) membalas WhatsApp dan bersedia hadir besok.</p>
                            <p class="text-xs font-bold text-primary-600 mt-4">Respon pasien</p>
                        </div>

                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>