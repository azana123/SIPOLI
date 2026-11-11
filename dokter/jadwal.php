<?php
require_once __DIR__ . "/../config/config.php";

$page_title = "Jadwal Saya";
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
    <title><?= e($page_title ?? 'Jadwal Saya') ?> | <?= e($app_name ?? 'SIPOLI') ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Fontawesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-page">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar / Header -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 p-8">

                <!-- Banner Praktik Saya Hari Ini -->
                <div class="bg-white border border-gray-200 rounded-base p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 md:w-12 md:h-12 bg-primary-50 border border-primary-100 text-primary rounded-base flex items-center justify-center shrink-0">
                            <i class="far fa-calendar-check text-base md:text-xl"></i>
                        </div>
                        <div>
                            <div class="flex flex-col-reverse md:flex-row md:items-center md:gap-3 mb-1">
                                <h2 class="text-lg font-bold text-gray-900">Praktik saya hari ini</h2>
                                <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Sedang berlangsung</span>
                            </div>
                            <p class="text-sm text-gray-500">Poli Umum &bull; 09.00–12.00 WIB &bull; Ruang pemeriksaan 01</p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-col md:items-end">
                        <button type="button" data-modal-target="modal-informasi-perubahan" data-modal-toggle="modal-informasi-perubahan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 mb-2 transition-colors cursor-pointer">
                            <i class="far fa-comment-alt"></i> Informasikan perubahan jadwal
                        </button>
                        <p class="text-xs text-gray-500 text-center md:text-left">Perubahan jadwal dikonfirmasi oleh admin</p>
                    </div>
                </div>

                <!-- Metrics Summary (3 Columns) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Sesi Praktik -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Sesi praktik</p>
                            <i class="far fa-calendar text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1">5</h3>
                        <p class="text-xs text-gray-500">21–25 September 2026</p>
                    </div>
                    <!-- Total Reservasi -->
                    <div class="bg-primary-50/40 border border-primary-100 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Total reservasi</p>
                            <i class="fas fa-user-friends text-primary-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary mb-1">31</h3>
                        <p class="text-xs text-gray-500">Dari 50 kuota pada periode ini</p>
                    </div>
                    <!-- Durasi Praktik -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Durasi praktik</p>
                            <i class="far fa-clock text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1">15 jam</h3>
                        <p class="text-xs text-gray-500">5 sesi &bull; masing-masing 3 jam</p>
                    </div>
                </div>

                <!-- Tabel Jadwal Praktik Saya -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm mb-6">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Jadwal praktik saya</h3>
                            <p class="text-sm text-gray-500">dr. Aditya Pratama &bull; Poli Umum</p>
                        </div>
                        <span class="text-sm font-semibold text-primary">Minggu ini &bull; 5 sesi</span>
                    </div>

                    <!-- Filters & Pagination Bar -->
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Tanggal Dropdown / Button -->
                            <div class="relative">
                                <button type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-base px-3 py-2 text-sm font-medium flex items-center gap-2 transition-colors cursor-pointer">
                                    <span>21–25 September 2026</span>
                                    <i class="far fa-calendar text-primary"></i>
                                </button>
                            </div>
                            <!-- Poli Filter -->
                            <select class="border border-gray-300 text-gray-700 bg-white rounded-base px-5 py-2 text-sm font-medium focus:ring-primary focus:border-primary">
                                <option>Poli Umum</option>
                            </select>
                            <!-- Status Filter -->
                            <select class="border border-gray-300 text-gray-700 bg-white rounded-base px-3 py-2 text-sm font-medium focus:ring-primary focus:border-primary">
                                <option>Semua status</option>
                                <option>Selesai</option>
                                <option>Berlangsung</option>
                                <option>Terjadwal</option>
                            </select>
                        </div>

                        <!-- Prev / Next Navigation -->
                        <div class="flex items-center gap-2">
                            <button type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-base px-3 py-2 text-sm font-medium transition-colors cursor-pointer">
                                Sebelumnya
                            </button>
                            <button type="button" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-base px-3 py-2 text-sm font-medium transition-colors cursor-pointer">
                                Berikutnya
                            </button>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/50 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Tanggal</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Poli</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Waktu praktik</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Kuota</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Reservasi</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Status</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <!-- Row 1 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 font-bold text-gray-900">Senin, 21 Sep 2026</td>
                                    <td class="px-6 py-4 text-gray-500">Poli Umum</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">09.00–12.00 WIB</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">10</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 w-28">
                                            <span class="text-xs font-semibold text-gray-900">6 dari 10</span>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: 60%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 text-fg-success-strong text-xs font-semibold rounded-base bg-success-soft">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">Pelayanan selesai</td>
                                </tr>

                                <!-- Row 2 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 font-bold text-gray-900">Selasa, 22 Sep 2026</td>
                                    <td class="px-6 py-4 text-gray-500">Poli Umum</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">09.00–12.00 WIB</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">10</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 w-28">
                                            <span class="text-xs font-semibold text-gray-900">8 dari 10</span>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: 80%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 text-fg-success-strong text-xs font-semibold rounded-base bg-success-soft">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">Pelayanan selesai</td>
                                </tr>

                                <!-- Row 3 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 font-bold text-gray-900">Rabu, 23 Sep 2026</td>
                                    <td class="px-6 py-4 text-gray-500">Poli Umum</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">09.00–12.00 WIB</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">10</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 w-28">
                                            <span class="text-xs font-semibold text-gray-900">7 dari 10</span>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: 70%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 text-fg-success-strong text-xs font-semibold rounded-base bg-success-soft">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">Pelayanan selesai</td>
                                </tr>

                                <!-- Row 4 (Hari Ini / Berlangsung) -->
                                <tr class="bg-primary-50/20 hover:bg-primary-50/30">
                                    <td class="px-6 py-4 font-bold text-gray-900">Kamis, 24 Sep 2026</td>
                                    <td class="px-6 py-4 text-gray-500">Poli Umum</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">09.00–12.00 WIB</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">10</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 w-28">
                                            <span class="text-xs font-semibold text-gray-900">6 dari 10</span>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: 60%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 text-primary-600 text-xs font-semibold rounded bg-primary-50">Berlangsung</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">Ruang pemeriksaan 01</td>
                                </tr>

                                <!-- Row 5 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 font-bold text-gray-900">Jumat, 25 Sep 2026</td>
                                    <td class="px-6 py-4 text-gray-500">Poli Umum</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">09.00–12.00 WIB</td>
                                    <td class="px-6 py-4 text-gray-900 font-medium">10</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 w-28">
                                            <span class="text-xs font-semibold text-gray-900">4 dari 10</span>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: 40%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-gray-700 text-xs font-medium rounded bg-gray-200">Terjadwal</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">Sesuai jadwal praktik</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Table -->
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                        <span>Menampilkan seluruh 5 sesi pada periode ini</span>
                        <span>Data jadwal ilustratif</span>
                    </div>
                </div>

                <!-- Bottom Cards Area -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Perlu perubahan jadwal praktik? (Span 2 Cols) -->
                    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-base shadow-sm p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Perlu perubahan jadwal praktik?</h3>
                            <p class="text-sm text-gray-500 mb-6 max-w-xl">
                                Sampaikan tanggal, alasan, dan usulan waktu kepada admin. Jadwal dan kuota tetap dikelola oleh admin.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <button type="button" data-modal-target="modal-informasi-perubahan" data-modal-toggle="modal-informasi-perubahan" class="bg-primary-500 text-white hover:bg-primary-600 rounded-base px-5 py-2.5 text-sm font-medium flex items-center justify-center gap-2 transition-colors cursor-pointer w-fit">
                                <i class="far fa-comment-alt"></i> Kirim informasi ke admin
                            </button>
                            <span class="text-xs text-gray-400">Tidak mengubah jadwal secara langsung</span>
                        </div>
                    </div>

                    <!-- Praktik Berikutnya (Span 1 Col) -->
                    <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Praktik berikutnya</h3>
                            <h4 class="text-base font-bold text-gray-900 mb-1">Jumat, 25 September 2026</h4>
                            <p class="text-sm text-gray-500 mb-3">Poli Umum &bull; 09.00–12.00 WIB &bull; Ruang 01</p>
                        </div>
                        <p class="text-sm font-bold text-primary">
                            4 reservasi terdaftar dari 10 kuota
                        </p>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php require_once __DIR__ . "/components/dashboard/perubahan-jadwal.php" ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>