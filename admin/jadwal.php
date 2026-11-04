<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Jadwal & Kuota";
$app_name = "SIPOLI";
?>
<!DOCTYPE html>
<html lang="id">

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

                <!-- Page Header & Action -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4 border-b border-gray-200 pb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">Jadwal & Kuota</h1>
                        <p class="text-gray-600 font-medium">Kamis, 24 September 2026</p>
                    </div>
                    <button type="button" data-modal-target="modal-tambah-jadwal" data-modal-toggle="modal-tambah-jadwal" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-base hover:bg-primary-600 focus:ring-4 focus:ring-primary-300">
                        <i class="fas fa-plus mr-2"></i> Tambah Jadwal
                    </button>
                </div>

                <p class="text-sm text-gray-500 mb-3">Ringkasan hari ini &middot; seluruh poli</p>

                <!-- Stats Counter -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white flex flex-col p-5 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Jadwal hari ini</p>
                        <h4 class="text-4xl font-bold text-gray-900 mb-2">4</h4>
                        <p class="text-xs text-gray-500">Seluruh jadwal dipublikasikan</p>
                    </div>
                    <div class="bg-white flex flex-col p-5 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Kuota terisi</p>
                        <h4 class="text-4xl font-bold text-gray-900 mb-2">18<span class="text-2xl text-gray-400">/30</span></h4>
                        <p class="text-xs text-gray-500">Total gabungan dari 3 poli</p>
                    </div>
                    <div class="bg-white flex flex-col p-5 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Kuota tersedia</p>
                        <h4 class="text-4xl font-bold text-gray-900 mb-2">12</h4>
                        <p class="text-xs text-gray-500">Masih dapat direservasi</p>
                    </div>
                    <div class="bg-yellow-100 flex flex-col p-5 border border-yellow-400 rounded-base shadow-sm">
                        <p class="text-sm text-yellow-800 font-semibold mb-2">Jadwal penuh</p>
                        <h4 class="text-4xl font-bold text-yellow-800 mb-2">1</h4>
                        <p class="text-xs text-yellow-800">Konseling &middot; 5 dari 5 pasien</p>
                    </div>
                </div>

                <!-- Main Data Card -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6 mb-6">

                    <!-- Card Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-2 gap-4">
                        <h4 class="text-xl font-bold text-gray-900">Daftar jadwal</h4>
                        <span class="text-sm text-gray-500">4 jadwal &middot; 3 poli</span>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-col md:flex-row gap-4 my-6 items-end">
                        <div class="flex-1 w-full md:w-auto">
                            <label class="block mb-2 text-xs font-medium text-gray-500">Tanggal</label>
                            <div class="relative">
                                <input type="date" value="2026-09-24" class="block w-full p-2.5 text-sm text-gray-900 border border-primary-400-05 rounded-base focus:ring-primary-500 focus:border-primary-500" />
                                <div class="absolute inset-y-0 end-0 flex items-center pr-3 pointer-events-none">
                                    <i class="far fa-calendar-alt text-primary-500"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 w-full md:w-auto">
                            <label class="block mb-2 text-xs font-medium text-gray-500">Poli</label>
                            <select class="block w-full p-2.5 border border-primary text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option selected>Semua poli</option>
                                <option value="umum">Poli Umum</option>
                                <option value="gigi">Poli Gigi</option>
                                <option value="konseling">Konseling</option>
                            </select>
                        </div>
                        <div class="flex-1 w-full md:w-auto">
                            <label class="block mb-2 text-xs font-medium text-gray-500">Status</label>
                            <select class="block w-full p-2.5 border border-primary text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option selected>Semua status</option>
                                <option value="tersedia">Tersedia</option>
                                <option value="penuh">Penuh</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <button type="button" class="px-4 py-2.5 text-sm font-medium text-primary-700 bg-primary-50 rounded border border-primary-100 hover:bg-primary-100 focus:ring-4 focus:ring-primary-100">
                                Hari ini
                            </button>
                            <button type="button" class="text-sm font-medium text-primary-600 hover:underline">
                                Reset filter
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="relative overflow-x-auto border-t border-gray-100 pt-4">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-white border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">Dokter / Psikolog</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Poli</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Tanggal & waktu</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Kuota</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">dr. Andini Putri</div>
                                        <div class="text-xs text-gray-500">Dokter umum</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-gray-900">Poli Umum</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">24 Sep 2026</div>
                                        <div class="text-xs text-gray-500">09.00-12.00 WIB</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900 text-sm mb-1">6/10 terisi</div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1 max-w-[120px]">
                                            <div class="bg-primary-500 h-1.5 rounded-full" style="width: 60%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500">4 kuota tersedia</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Tersedia</span>
                                        <div class="text-xs text-gray-500">Dipublikasikan</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap text-right flex justify-end items-center gap-3">
                                        <button type="button" data-modal-target="modal-detail-jadwal" data-modal-toggle="modal-detail-jadwal" class="hover:underline cursor-pointer">Lihat</button>
                                        <button type="button" data-modal-target="modal-edit-jadwal" data-modal-toggle="modal-edit-jadwal" class="w-8 h-8 rounded bg-primary-50 text-primary-600 hover:bg-primary-100 flex justify-center items-center">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">drg. Bima Pratama</div>
                                        <div class="text-xs text-gray-500">Dokter gigi</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-gray-900">Poli Gigi</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">24 Sep 2026</div>
                                        <div class="text-xs text-gray-500">09.00-12.00 WIB</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900 text-sm mb-1">3/5 terisi</div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1 max-w-[120px]">
                                            <div class="bg-primary-500 h-1.5 rounded-full" style="width: 60%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500">2 kuota tersedia</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Tersedia</span>
                                        <div class="text-xs text-gray-500">Dipublikasikan</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap text-right flex justify-end items-center gap-3">
                                        <button type="button" data-modal-target="modal-detail-jadwal" data-modal-toggle="modal-detail-jadwal" class="hover:underline cursor-pointer">Lihat</button>
                                        <button type="button" data-modal-target="modal-edit-jadwal" data-modal-toggle="modal-edit-jadwal" class="w-8 h-8 rounded bg-primary-50 text-primary-600 hover:bg-primary-100 flex justify-center items-center">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Row 3 (Penuh) -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">Dewi Anggraini, M.Psi.</div>
                                        <div class="text-xs text-gray-500">Psikolog</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-gray-900">Konseling</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">24 Sep 2026</div>
                                        <div class="text-xs text-gray-500">09.00-12.00 WIB</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-red-600 text-sm mb-1">5/5 terisi</div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1 max-w-[120px]">
                                            <div class="bg-red-500 h-1.5 rounded-full" style="width: 100%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500">Batas harian tercapai</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-red-800 text-xs font-medium rounded bg-red-100">Penuh</span>
                                        <div class="text-xs text-gray-500">Dipublikasikan</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap text-right flex justify-end items-center gap-3">
                                        <button type="button" data-modal-target="modal-detail-jadwal" data-modal-toggle="modal-detail-jadwal" class="hover:underline cursor-pointer">Lihat</button>
                                        <button type="button" data-modal-target="modal-edit-jadwal" data-modal-toggle="modal-edit-jadwal" class="w-8 h-8 rounded bg-primary-50 text-primary-600 hover:bg-primary-100 flex justify-center items-center">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">dr. Rizal Pratama</div>
                                        <div class="text-xs text-gray-500">Dokter umum</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-gray-900">Poli Umum</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">24 Sep 2026</div>
                                        <div class="text-xs text-gray-500">13.00-16.00 WIB</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900 text-sm mb-1">4/10 terisi</div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1 max-w-[120px]">
                                            <div class="bg-primary-500 h-1.5 rounded-full" style="width: 40%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500">6 kuota tersedia</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Tersedia</span>
                                        <div class="text-xs text-gray-500">Dipublikasikan</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap text-right flex justify-end items-center gap-3">
                                        <button type="button" data-modal-target="modal-detail-jadwal" data-modal-toggle="modal-detail-jadwal" class="hover:underline cursor-pointer">Lihat</button>
                                        <button type="button" data-modal-target="modal-edit-jadwal" data-modal-toggle="modal-edit-jadwal" class="w-8 h-8 rounded bg-primary-50 text-primary-600 hover:bg-primary-100 flex justify-center items-center">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer -->
                    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 mt-4 gap-4">
                        <span>Menampilkan 1-4 dari 4 jadwal</span>
                        <span class="font-medium text-primary-700">Total kuota: 18 terisi / 30 pasien</span>
                    </div>

                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-base p-4 border border-gray-200 flex items-start gap-3 shadow-sm">
                        <i class="fas fa-info-circle text-primary-500 mt-1"></i>
                        <div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Batas kuota konseling</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Maksimal 5 pasien per hari, dihitung dari seluruh jadwal konseling pada tanggal yang sama.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-base p-4 border border-gray-200 flex items-start gap-3 shadow-sm">
                        <i class="far fa-calendar-alt text-primary-500 mt-1"></i>
                        <div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Jika jadwal berubah</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Beri tahu pasien yang terdampak setiap perubahan jadwal. Jika psikolog berhalangan, atur ulang jadwal pasien dan kirim informasi penggantinya.</p>
                        </div>
                    </div>
                </div>

            </main>

        </div>
    </div>
    <?php require_once __DIR__ . '/components/jadwal/detail-jadwal.php'; ?>
    <?php require_once __DIR__ . '/components/jadwal/form-tambah-jadwal.php'; ?>
    <?php require_once __DIR__ . '/components/jadwal/form-edit-jadwal.php'; ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>