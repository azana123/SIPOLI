<?php
$page_title = "Detail Reservasi";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | SIPOLI</title>
    <!-- Asumsi kompilasi Tailwind CSS / Flowbite -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama Mobile First -->
    <div class="w-full md:max-w-md mx-auto min-h-screen bg-page flex flex-col shadow-xl relative">

        <!-- App Bar / Header -->
        <header class="bg-primary-500 text-white px-5 py-4 flex items-center sticky top-0 z-20 shadow-sm">
            <a href="./reservasi.php" class="mr-4 hover:bg-primary-600 p-1 rounded-base transition-colors">
                <i class="fas fa-chevron-left text-lg"></i>
            </a>
            <h1 class="text-lg font-semibold tracking-wide"><?= $page_title ?></h1>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-5 py-6 overflow-y-auto">

            <!-- Card: Status Konfirmasi -->
            <div class="bg-yellow-100 border border-yellow-300 rounded-base p-4 flex items-center gap-3 mb-4">
                <i class="fas fa-history text-yellow-800 text-xl shrink-0"></i>
                <p class="font-semibold text-yellow-900 text-sm leading-tight">
                    Menunggu konfirmasi dari poliklinik
                </p>
            </div>

            <!-- Card: Info Poliklinik & Dokter -->
            <div class="bg-white border-2 border-default rounded-base p-4 flex items-center gap-4 mb-4">
                <!-- Icon Lingkaran -->
                <div class="w-12 h-12 rounded-full bg-primary-50 shrink-0 flex justify-center items-center">
                    <i class="fas fa-stethoscope text-primary-500 text-xl"></i>
                </div>
                <div class="flex-1 flex flex-col">
                    <h3 class="font-bold text-gray-900 ">Poli Gigi</h3>
                    <p class="text-xs text-gray-500 mt-0.5">drg. Nadia Putri, Sp.KG</p>
                </div>
            </div>

            <!-- Card: Detail Jadwal -->
            <div class="bg-white border-2 border-default rounded-base p-5 mb-4 flex flex-col gap-3">
                <!-- Row: Tanggal -->
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-medium">Tanggal</span>
                    <span class=" font-semibold text-gray-900">Kam, 24 Sep 2026</span>
                </div>

                <!-- Divider Dashed -->
                <hr class="border-t border-dashed border-gray-300 my-0.5">

                <!-- Row: Waktu -->
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-medium">Waktu</span>
                    <span class=" font-semibold text-gray-900">14.00</span>
                </div>

                <!-- Divider Dashed -->
                <hr class="border-t border-dashed border-gray-300 my-0.5">

                <!-- Row: Keluhan -->
                <div class="flex justify-between items-start gap-4">
                    <span class="text-sm text-gray-500 font-medium shrink-0">Keluhan</span>
                    <span class=" font-semibold text-gray-900 text-right">Pembersihan Karang Gigi</span>
                </div>
            </div>

            <!-- Card: Aksi Konfirmasi Kehadiran -->
            <div class="bg-white border-2 border-default rounded-base p-5 mb-6">
                <h3 class="font-bold text-gray-900  mb-2">Konfirmasi Kehadiran</h3>
                <p class="text-[13px] text-gray-500 mb-5 leading-relaxed">
                    Konfirmasi kehadiran, reservasi bisa ditandai tidak hadir.
                </p>

                <div class="flex flex-col gap-3">
                    <!-- Button: Hadir (Disabled State) -->
                    <button type="button" class="w-full py-2.5 bg-primary text-white cursor-pointer hover:bg-primary-700 transition-colors duration-300 disabled:bg-gray-200 disabled:text-gray-400 text-sm font-semibold rounded-full disabled:cursor-not-allowed">
                        Saya hadir
                    </button>
                    <!-- Button: Tidak Hadir (Outline State) -->
                    <button type="button" data-modal-target="modal-ketidakhadiran" data-modal-toggle="modal-ketidakhadiran" class="w-full py-2.5 bg-white border border-primary text-primary cursor-pointer disabled:border-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed hover:bg-primary disabled:hover:bg-white hover:text-white text-sm font-semibold rounded-full transition-colors duration-300">
                        Tidak bisa hadir
                    </button>
                </div>
            </div>

            <!-- Aksi Pembatalan -->
            <div class="text-center pb-4">
                <button type="button" data-modal-target="modal-konfirmasi-pembatalan" data-modal-toggle="modal-konfirmasi-pembatalan" class="text-sm font-bold text-red-700 hover:text-red-800 hover:underline transition-colors">
                    Batalkan reservasi
                </button>
            </div>

        </main>
    </div>

    <?php require_once __DIR__ . "/../components/reservasi/konfirmasi-ketidakhadiran.php" ?>
    <?php require_once __DIR__ . "/../components/reservasi/pembatalan-reservasi.php" ?>
    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>