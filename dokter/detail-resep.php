<?php
require_once __DIR__ . "/../config/config.php";

$page_title = "Detail Resep";
$app_name = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Detail Resep') ?> | <?= e($app_name ?? 'SIPOLI') ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-page antialiased text-gray-800">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar Component -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar / Header Area -->
            <header class="bg-white border-b border-gray-200 px-8 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= e($page_title) ?></h1>
                    <p class="text-xs text-gray-500 mt-0.5">Kamis, 24 September 2026</p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-base">
                        <i class="far fa-clock text-gray-400"></i>
                        <span>09.40 WIB</span>
                    </div>
                    <div class="text-xs text-gray-700 font-medium border-l border-gray-200 pl-6">
                        Halo, <span class="font-bold text-gray-900">dr. Aditya</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-1 min-w-0 p-8 space-y-6">

                <!-- Back Link -->
                <div>
                    <a href="./riwayat-penanganan.php" class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke Riwayat Penanganan</span>
                    </a>
                </div>

                <!-- Top Summary Card: Patient & Prescription Header -->
                <div class="bg-white border border-gray-200 rounded-base p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                        <!-- Patient Profile -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-50 text-primary border border-primary-100 rounded-full font-bold text-xs flex items-center justify-center shrink-0">
                                SM
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Sinta Maharani</h2>
                                <p class="text-xs text-gray-500">NIM 2341720061 &bull; Mahasiswa</p>
                            </div>
                        </div>

                        <!-- Prescription Meta -->
                        <div class="pt-3 sm:pt-0 sm:pl-6">
                            <h3 class="text-sm font-bold text-gray-900">Resep RS-240926-001</h3>
                            <p class="text-xs text-gray-500">24 September 2026 &bull; Poli Umum</p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="shrink-0">
                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Diproses</span>
                    </div>
                </div>

                <!-- Two Column Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <!-- Left Column: Rincian Resep (7/12) -->
                    <div class="lg:col-span-7 bg-white border border-gray-200 rounded-base shadow-sm p-6 space-y-6">

                        <!-- Header Rincian -->
                        <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900">Rincian resep</h3>
                            <span class="text-xs text-gray-400">1 jenis obat</span>
                        </div>

                        <!-- Info Tanggal & Dokter -->
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <p class="text-gray-400 mb-1">Tanggal resep</p>
                                <p class="font-bold text-gray-900">24 September 2026</p>
                            </div>
                            <div>
                                <p class="text-gray-400 mb-1">Dokter penulis</p>
                                <p class="font-bold text-gray-900">dr. Aditya Pratama</p>
                            </div>
                        </div>

                        <!-- Obat Card Container -->
                        <div class="bg-gray-50/70 border border-gray-100 rounded-base p-4 space-y-4">
                            <!-- Title Nama Obat -->
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-primary-50 text-primary-500 border border-primary-100 rounded-base flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-pills text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Nama obat</p>
                                    <h4 class="text-xs font-bold text-gray-900">Tidak tercatat</h4>
                                </div>
                            </div>

                            <!-- Detail Spec Grid -->
                            <div class="grid grid-cols-2 gap-4 text-xs pt-2 border-t border-gray-200/60">
                                <div>
                                    <p class="text-gray-400 mb-0.5">Bentuk / satuan</p>
                                    <p class="font-medium text-gray-900">Tidak tercatat</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-0.5">Jumlah</p>
                                    <p class="font-medium text-gray-900">Tidak tercatat</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-0.5">Dosis</p>
                                    <p class="font-medium text-gray-900">Tidak tercatat</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-0.5">Aturan pakai</p>
                                    <p class="font-medium text-gray-900">Tidak tercatat</p>
                                </div>
                            </div>

                            <!-- Catatan Item -->
                            <div class="pt-2">
                                <p class="text-gray-400 text-xs mb-0.5">Catatan item</p>
                                <p class="text-xs font-medium text-gray-900">Tidak tercatat</p>
                            </div>
                        </div>

                        <!-- Catatan Resep -->
                        <div class="text-xs">
                            <p class="text-gray-400 mb-1">Catatan resep</p>
                            <p class="font-medium text-gray-900">Tidak tercatat</p>
                        </div>

                        <!-- Notice Banner -->
                        <div class="bg-primary-50/50 border border-primary-100 rounded-base p-4">
                            <h5 class="text-xs font-bold text-gray-900 mb-1">Rincian obat belum tersedia pada riwayat</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Bidang yang tidak tersedia ditampilkan "Tidak tercatat". Tidak ada dosis atau aturan pakai tambahan pada tampilan ini.
                            </p>
                        </div>

                    </div>

                    <!-- Right Column: Kunjungan Terkait (5/12) -->
                    <div class="lg:col-span-5 bg-white border border-gray-200 rounded-base shadow-sm p-6 space-y-5">

                        <!-- Header Status -->
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900">Kunjungan terkait</h3>
                            <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                        </div>

                        <!-- Meta Pemeriksaan -->
                        <div class="text-xs space-y-1">
                            <h4 class="font-bold text-gray-900">Pemeriksaan PM-240926-001</h4>
                            <p class="text-gray-500">24 September 2026 &bull; 09.00 WIB</p>
                            <p class="text-gray-500">Poli Umum &bull; dr. Aditya Pratama</p>
                            <p class="text-gray-400 text-xs">Reservasi online &bull; Ruang 01</p>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Diagnosis -->
                        <div class="text-xs">
                            <h4 class="font-bold text-gray-900 mb-1">Diagnosis</h4>
                            <p class="text-gray-600">Infeksi saluran napas atas (ISPA) akut.</p>
                        </div>

                        <!-- Keluhan -->
                        <div class="text-xs">
                            <h4 class="font-bold text-gray-900 mb-1">Keluhan</h4>
                            <p class="text-gray-600 leading-relaxed">Batuk dan pilek sejak 2 hari. Tidak ada sesak napas; aktivitas sehari-hari masih baik.</p>
                        </div>

                        <!-- Catatan Hasil Pemeriksaan -->
                        <div class="text-xs space-y-3">
                            <h4 class="font-bold text-gray-900">Catatan hasil pemeriksaan</h4>

                            <!-- Vitals Strip Box -->
                            <div class="bg-gray-50 border border-gray-100 rounded-base p-3 text-center text-gray-700 font-medium text-xs space-y-1">
                                <div>TD 110/70 mmHg &bull; Suhu 36,8 °C</div>
                                <div>Nadi 82 x/menit</div>
                            </div>

                            <p class="text-gray-600 leading-relaxed">
                                Keadaan umum baik, faring hiperemis ringan. Edukasi istirahat, cukup cairan, dan kembali kontrol bila keluhan memburuk.
                            </p>
                        </div>

                        <!-- Readonly Info Footer -->
                        <div class="pt-3 border-t border-gray-100 text-xs text-primary-600 font-medium">
                            Data kunjungan selesai &bull; Hanya baca
                        </div>

                    </div>

                </div>

                <!-- Bottom Action Banner Card -->
                <div class="bg-white border border-gray-200 rounded-base p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-50 border border-green-200 text-green-700 rounded-base flex items-center justify-center shrink-0">
                            <i class="far fa-clipboard text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900">Diproses admin pukul 09.25 WIB</h4>
                            <p class="text-xs text-gray-500">Status Diproses hanya dapat dilihat. Pemrosesan obat dilakukan oleh admin.</p>
                        </div>
                    </div>
                    <a href="./riwayat-penanganan.php" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-base px-4 py-2.5 transition-colors text-center shrink-0">
                        Kembali ke riwayat
                    </a>
                </div>
            </main>
        </div>
    </div>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>