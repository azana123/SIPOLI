<?php
require_once __DIR__ . "/../config/config.php";

$page_title = "Riwayat Penanganan";
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
    <title><?= e($page_title) ?> | <?= e($app_name) ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Fontawesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-page text-gray-800 antialiased">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Main Content -->
            <main class="flex-1 min-w-0 p-6 md:p-8">

                <!-- Banner Pemeriksaan Aktif -->
                <div class="bg-white border border-gray-200 rounded-base p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-primary-50 border border-primary-100 text-primary-600 rounded-base flex items-center justify-center shrink-0">
                            <i class="fas fa-stethoscope text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Pemeriksaan aktif &bull; Dewi Anggraini</h2>
                            <p class="text-xs text-gray-500 mt-0.5">09.40 WIB &bull; Demam sejak kemarin &bull; Hasil pemeriksaan belum selesai</p>
                        </div>
                    </div>
                    <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="bg-primary text-white hover:bg-primary-700 rounded-base px-4 py-2.5 text-xs font-semibold flex items-center justify-center gap-2 transition-colors cursor-pointer shrink-0">
                        <i class="fas fa-notes-medical"></i> Catat Pemeriksaan & Diagnosis
                    </button>
                </div>

                <!-- Filter Controls Bar -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 flex-1">
                        <!-- Input Search -->
                        <div class="relative">
                            <input type="text" placeholder="Cari nama, NIM, atau NIP" class="w-full pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-base text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700 placeholder-gray-400">
                            <i class="fas fa-search absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>

                        <!-- Date Range Picker -->
                        <div class="relative">
                            <input type="text" value="1–24 September 2026" readonly class="w-full pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none cursor-pointer">
                            <i class="far fa-calendar-alt absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>

                        <!-- Select Poli -->
                        <div class="relative">
                            <select class="w-full px-4 py-2 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 appearance-none pr-8 cursor-pointer">
                                <option>Poli Umum</option>
                                <option>Poli Gigi</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>

                        <!-- Select Pasien -->
                        <div class="relative">
                            <select class="w-full px-4 py-2 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 appearance-none pr-8 cursor-pointer">
                                <option>Semua pasien</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 font-medium text-right self-end lg:self-center shrink-0">
                        4 kunjungan
                    </div>
                </div>

                <!-- Main Content Grid (2 Columns) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Left Column: Daftar Riwayat Kunjungan (4 Cols) -->
                    <div id="left-card-penanganan" class="lg:col-span-12 flex flex-col">
                        <div class="bg-white border border-gray-200 rounded-base shadow-sm flex-1 flex flex-col">
                            <div class="p-5 border-b border-gray-100">
                                <h3 class="text-base font-bold text-gray-900">Riwayat kunjungan</h3>
                                <p class="text-xs text-gray-500 mt-0.5">2 hari ini &bull; 2 kunjungan sebelumnya</p>
                            </div>

                            <!-- Visit Items List -->
                            <div class="divide-y divide-gray-100 flex-1">

                                <!-- Item 1 (Selected) -->
                                <div onclick="detailDataPenanganan(this)" class="items-card p-4 transition-colors cursor-pointer border-l-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-gray-900 text-sm">Sinta Maharani</h4>
                                        <i class="fas fa-chevron-right text-lg text-primary-600"></i>
                                    </div>
                                    <p class="text-xs text-gray-400 mb-1">NIM 2341720061</p>
                                    <p class="text-xs text-gray-500 mb-2">24 Sep 2026 &bull; 09.00 WIB</p>
                                    <p class="text-xs font-bold text-primary-600 mb-1">Infeksi saluran napas atas</p>
                                    <p class="text-[11px] text-gray-400">Selesai &bull; Poli Umum</p>
                                </div>

                                <!-- Item 2 -->
                                <div onclick="detailDataPenanganan(this)" class="items-card p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-gray-900 text-sm">Rizal Pratama</h4>
                                        <i class="far fa-check-circle text-xl text-emerald-600"></i>
                                    </div>
                                    <p class="text-xs text-gray-400 mb-1">NIM 2341720084</p>
                                    <p class="text-xs text-gray-500 mb-2">24 Sep 2026 &bull; 09.20 WIB</p>
                                    <p class="text-xs font-bold text-gray-800 mb-1">Dispepsia</p>
                                    <p class="text-[11px] text-gray-400">Selesai &bull; Poli Umum</p>
                                </div>

                                <!-- Item 3 -->
                                <div onclick="detailDataPenanganan(this)" class="items-card p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-gray-900 text-sm">Sinta Maharani</h4>
                                        <i class="far fa-check-circle text-xl text-emerald-600"></i>
                                    </div>
                                    <p class="text-xs text-gray-400 mb-1">NIM 2341720061</p>
                                    <p class="text-xs text-gray-500 mb-2">10 Sep 2026 &bull; 09.20 WIB</p>
                                    <p class="text-xs font-bold text-gray-800 mb-1">Nyeri kepala</p>
                                    <p class="text-[11px] text-gray-400">Selesai &bull; Poli Umum</p>
                                </div>

                                <!-- Item 4 -->
                                <div onclick="detailDataPenanganan(this)" class="items-card p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-gray-900 text-sm">Nadia Putri</h4>
                                        <i class="far fa-check-circle text-xl text-emerald-600"></i>
                                    </div>
                                    <p class="text-xs text-gray-400 mb-1">NIM 2341720105</p>
                                    <p class="text-xs text-gray-500 mb-2">03 Sep 2026 &bull; 10.00 WIB</p>
                                    <p class="text-xs font-bold text-gray-800 mb-1">Faringitis akut</p>
                                    <p class="text-[11px] text-gray-400">Selesai &bull; Poli Umum</p>
                                </div>

                            </div>

                            <!-- Left Footer -->
                            <div class="p-4 border-t border-gray-100 text-[11px] text-gray-400 leading-relaxed">
                                <p class="mb-1">Menampilkan seluruh 4 kunjungan selesai pada periode yang dipilih.</p>
                                <p>Data pasien dan penanganan ilustratif</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Detail Penanganan Pasien (7 Cols) -->
                    <div id="right-card-penanganan" class="lg:col-span-7 hidden">
                        <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">

                            <!-- Detail Header -->
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-lg font-bold text-gray-900">Detail penanganan</h3>
                                <div class="flex gap-3 items-center">
                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" type="button" class="border border-primary hover focus:ring-4 focus:ring-primary-400-05 shadow-xs font-medium leading-5 rounded-full text-sm px-1 py-1 focus:outline-none cursor-pointer hover:bg-primary-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#1f1f1f">
                                            <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Patient Info Box -->
                            <div class="bg-gray-50/70 border border-gray-100 rounded-base p-4 mb-6 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 text-primary-700 rounded-full font-bold flex items-center justify-center text-sm shrink-0">
                                        SM
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm">Sinta Maharani</h4>
                                        <p class="text-xs text-gray-500">NIM 2341720061 &bull; Mahasiswa</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-gray-700">2 kunjungan</span>
                            </div>

                            <!-- Metadata Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-6 mb-6 border-b border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Tanggal penanganan</p>
                                    <p class="text-sm font-bold text-gray-900">24 September 2026 &bull; 09.00 WIB</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Pemeriksaan PM-240926-001</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 mb-1">Poli / dokter</p>
                                    <p class="text-sm font-bold text-gray-900">Poli Umum &bull; dr. Aditya Pratama</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Reservasi online &bull; Ruang 01</p>
                                </div>
                            </div>

                            <!-- Section: Keluhan -->
                            <div class="mb-6">
                                <h5 class="text-sm font-bold text-gray-900 mb-2">Keluhan</h5>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    Batuk dan pilek sejak 2 hari. Tidak ada sesak napas; aktivitas sehari-hari masih baik.
                                </p>
                            </div>

                            <!-- Section: Diagnosis -->
                            <div class="mb-6">
                                <h5 class="text-sm font-bold text-gray-900 mb-2">Diagnosis</h5>
                                <p class="text-sm text-gray-700 font-medium">
                                    Infeksi saluran napas atas (ISPA) akut.
                                </p>
                            </div>

                            <!-- Section: Catatan Hasil Pemeriksaan -->
                            <div class="mb-6">
                                <h5 class="text-sm font-bold text-gray-900 mb-3">Catatan hasil pemeriksaan</h5>

                                <!-- Vitals Grid -->
                                <div class="bg-gray-50/80 border border-gray-200/60 rounded-base p-3 mb-3 grid grid-cols-3 text-center text-xs text-gray-700 font-medium">
                                    <div>TD 110/70 mmHg</div>
                                    <div class="border-x border-gray-200">Suhu 36,8 °C</div>
                                    <div>Nadi 82 x/menit</div>
                                </div>

                                <p class="text-sm text-gray-600 leading-relaxed">
                                    Keadaan umum baik, faring hiperemis ringan. Edukasi istirahat, cukup cairan, dan kembali kontrol bila keluhan memburuk.
                                </p>
                            </div>

                            <!-- Section: Resep Box -->
                            <div class="bg-gray-50/70 border border-gray-200/70 rounded-base p-4 mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <h6 class="font-bold text-gray-900 text-sm">Resep RS-240926-001</h6>
                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Diproses</span>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <p class="text-xs text-gray-500">1 jenis obat &bull; Diproses admin pukul 09.25 WIB</p>
                                    <a href="./detail-resep.php" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-3.5 py-1.5 text-xs font-semibold transition-colors shadow-sm cursor-pointer self-start sm:self-auto">
                                        Lihat resep
                                    </a>
                                </div>
                            </div>

                            <!-- Detail Footer -->
                            <div class="text-[11px] text-gray-400">
                                Catatan ini merupakan contoh desain, bukan panduan medis.
                            </div>

                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php require_once __DIR__ . "/components/dashboard/ringkasan-pemeriksaan.php" ?>
    <?php require_once __DIR__ . "/components/dashboard/form-pemeriksaan.php" ?>

    <!-- App JS -->
    <script src="./../assets/js/app.js"></script>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>