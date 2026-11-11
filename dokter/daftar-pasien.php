<?php
require_once __DIR__ . "/../config/config.php";

$page_title = "Daftar Pasien";
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

                <!-- Header Bar -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Daftar Pasien</h1>
                        <p class="text-sm text-gray-500 font-medium">Kamis, 24 September 2026</p>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-gray-600 bg-white md:bg-transparent p-3 md:p-0 rounded-base border md:border-none border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="far fa-clock text-gray-400"></i>
                            <span class="font-semibold text-gray-700">09.40 WIB</span>
                        </div>
                        <div class="h-4 w-px bg-gray-300 hidden md:block"></div>
                        <div class="font-medium text-gray-800">
                            Halo, <span class="font-bold">dr. Aditya</span>
                        </div>
                    </div>
                </div>

                <!-- Banner Informasi Sesi Praktik -->
                <div class="bg-white border border-gray-200 rounded-base p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary-50 border border-primary-100 text-primary-600 rounded-base flex items-center justify-center shrink-0">
                            <i class="fas fa-user-nurse text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Pasien saya hari ini</h2>
                            <p class="text-sm text-gray-500 mt-0.5">Poli Umum &bull; 09.00–12.00 WIB &bull; Ruang pemeriksaan 01 &bull; Kuota 10</p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Sedang berlangsung
                        </span>
                    </div>
                </div>

                <!-- Counter Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Terjadwal -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-gray-700">Pasien terjadwal</p>
                            <i class="fas fa-user-friends text-gray-400 text-base"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold text-gray-900 mb-2">6</h3>
                        <p class="text-xs text-gray-500">5 online &bull; 1 offline</p>
                    </div>

                    <!-- Menunggu Pemeriksaan -->
                    <div class="bg-yellow-50/70 border border-yellow-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-gray-700">Menunggu pemeriksaan</p>
                            <i class="far fa-clock text-yellow-600 text-base"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold text-yellow-700 mb-2">3</h3>
                        <p class="text-xs text-yellow-800">Pasien dalam antrean saya</p>
                    </div>

                    <!-- Sedang Diperiksa -->
                    <div class="bg-primary-50/70 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-primary-900">Sedang diperiksa</p>
                            <i class="fas fa-stethoscope text-primary-600 text-base"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold text-primary-600 mb-2">1</h3>
                        <p class="text-xs text-primary-800">Pemeriksaan belum selesai</p>
                    </div>

                    <!-- Pemeriksaan Selesai -->
                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-emerald-900">Pemeriksaan selesai</p>
                        </div>
                        <h3 class="text-3xl font-extrabold text-emerald-800 mb-2">2</h3>
                        <p class="text-xs text-emerald-800">Penanganan telah dicatat</p>
                    </div>
                </div>

                <!-- Main Card: Daftar Reservasi Pasien -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm mb-6">
                    
                    <!-- Card Header -->
                    <div class="px-6 pt-6 pb-3 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar reservasi pasien</h3>
                            <p class="text-sm text-gray-500">Pasien yang terjadwal untuk dr. Aditya Pratama</p>
                        </div>
                        <span class="text-sm font-medium text-primary-600 px-3 py-1 self-start md:self-auto">
                            6 reservasi terkonfirmasi
                        </span>
                    </div>

                    <!-- Filter Controls -->
                    <div class="p-6 pt-3 border-b border-gray-100 bg-gray-50/30">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Input Search -->
                            <div class="relative">
                                <input type="text" placeholder="Cari nama, NIM, atau NIP" class="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-300 rounded-base text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700 placeholder-gray-400">
                                <i class="fas fa-search absolute right-3.5 top-1/2 -translate-y-1/2 text-primary text-sm"></i>
                            </div>

                            <!-- Input Date -->
                            <div class="relative">
                                <input id="filter-tanggal" name="tanggal" type="date" value="" class="block w-full border border-primary-400-05 text-heading text-sm rounded-base focus:ring-primary focus:border-primary px-3 py-2.5 shadow-xs" />
                                <i class="far fa-calendar-alt absolute right-3.5 top-1/2 -translate-y-1/2 text-primary text-sm"></i>
                            </div>

                            <!-- Dropdown Status -->
                            <div class="relative">
                                <select class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none pr-8 cursor-pointer">
                                    <option>Semua status</option>
                                    <option>Menunggu</option>
                                    <option>Diperiksa</option>
                                    <option>Selesai</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <!-- Dropdown Asal -->
                            <div class="relative">
                                <select class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none pr-8 cursor-pointer">
                                    <option>Semua asal</option>
                                    <option>Online</option>
                                    <option>Offline</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/80 border-b border-gray-200 uppercase font-semibold">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">Pasien / NIM atau NIP</th>
                                    <th scope="col" class="px-6 py-3.5">Jam</th>
                                    <th scope="col" class="px-6 py-3.5">Keluhan awal</th>
                                    <th scope="col" class="px-6 py-3.5">Asal</th>
                                    <th scope="col" class="px-6 py-3.5">Pemeriksaan</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                
                                <!-- Row 1: Sinta Maharani -->
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">Sinta Maharani</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2341720061 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">09.00</td>
                                    <td class="px-6 py-4 text-gray-600">Batuk dan pilek</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">Online</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button data-modal-target="modal-ringkasan-pemeriksaan" data-modal-toggle="modal-ringkasan-pemeriksaan" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded px-4 py-2 text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                                            Lihat Riwayat
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 2: Rizal Pratama -->
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">Rizal Pratama</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2341720084 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">09.20</td>
                                    <td class="px-6 py-4 text-gray-600">Nyeri ulu hati</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">Online</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button data-modal-target="modal-ringkasan-pemeriksaan" data-modal-toggle="modal-ringkasan-pemeriksaan" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                                            Lihat Riwayat
                                        </button>
                                    </td>
                                </tr>

                                <!-- Row 3: Dewi Anggraini (Sedang Diperiksa) -->
                                <tr class="bg-primary-50/20 hover:bg-primary-50/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">Dewi Anggraini</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2441720032 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">09.40</td>
                                    <td class="px-6 py-4 text-gray-600">Demam sejak kemarin</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">Online</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-primary-700 bg-primary-100/70 border border-primary-200 rounded-base">
                                            Diperiksa
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="bg-primary text-white hover:bg-primary-700 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                                                Lanjutkan Pemeriksaan
                                            </button>
                                            <a href="#" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                                                Lihat riwayat pasien <i class="fas fa-arrow-right text-2xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Row 4: Ahmad Fauzi -->
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">Ahmad Fauzi</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2441720057 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">10.00</td>
                                    <td class="px-6 py-4 text-gray-600">Sakit kepala</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">Online</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                                                Mulai Pemeriksaan
                                            </button>
                                            <a href="#" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                                                Lihat riwayat pasien <i class="fas fa-arrow-right text-2xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Row 5: Budi Santoso -->
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">Budi Santoso</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIP 198705122019031006 &bull; Pegawai</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">10.20</td>
                                    <td class="px-6 py-4 text-gray-600">Nyeri punggung</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">Offline</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                                                Mulai Pemeriksaan
                                            </button>
                                            <a href="#" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                                                Lihat riwayat pasien <i class="fas fa-arrow-right text-2xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Row 6: Nadia Putri -->
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 text-base">Nadia Putri</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2341720105 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">10.40</td>
                                    <td class="px-6 py-4 text-gray-600">Sakit tenggorokan</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">Online</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-3.5 py-2 text-xs font-semibold transition-colors shadow-sm cursor-pointer">
                                                Mulai Pemeriksaan
                                            </button>
                                            <a href="#" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                                                Lihat riwayat pasien <i class="fas fa-arrow-right text-2xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400 font-medium">
                        <span>Menampilkan seluruh 6 pasien terjadwal hari ini</span>
                    </div>
                </div>

                <!-- Footer Active Examination Card -->
                <div class="bg-primary-50/50 border border-primary-200/60 rounded-base p-5 flex items-start gap-4">
                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-base flex items-center justify-center shrink-0">
                        <i class="fas fa-stethoscope text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 mb-0.5">
                            Pemeriksaan aktif: Dewi Anggraini &bull; 09.40 WIB
                        </h4>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Lanjutkan pencatatan hasil pemeriksaan dan diagnosis melalui tindakan di atas. Reservasi offline dan perubahan jadwal pasien ditangani oleh admin.
                        </p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php require_once __DIR__ . "/components/dashboard/ringkasan-pemeriksaan.php" ?>
    <?php require_once __DIR__ . "/components/dashboard/form-pemeriksaan.php" ?>
    <?php require_once __DIR__ . "/components/dashboard/perubahan-jadwal.php" ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>