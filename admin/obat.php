<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Obat & Stok";
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
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <p class="text-gray-600 font-medium">Kelola data obat dan ketersediaan stok poliklinik.</p>
                    <button type="button" data-modal-target="modal-tambah-obat" data-modal-toggle="modal-tambah-obat" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-base hover:bg-primary-600 focus:ring-4 focus:ring-primary-300">
                        <i class="fas fa-plus mr-2"></i> Tambah Obat
                    </button>
                </div>

                <!-- Stats Counter -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white flex flex-col p-4 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Total jenis obat</p>
                        <h4 class="text-3xl font-bold text-gray-900 mb-2">8</h4>
                        <p class="text-xs text-gray-500">Seluruh data obat terdaftar</p>
                    </div>
                    <div class="bg-white flex flex-col p-4 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Obat aktif</p>
                        <h4 class="text-3xl font-bold text-gray-900 mb-2">7</h4>
                        <p class="text-xs text-gray-500">Dapat digunakan pada resep</p>
                    </div>
                    <div class="bg-orange-50 flex flex-col p-4 border border-orange-200 rounded-base shadow-sm">
                        <p class="text-sm text-orange-800 font-semibold mb-2">Perlu perhatian</p>
                        <h4 class="text-3xl font-bold text-orange-800 mb-2">2</h4>
                        <p class="text-xs text-gray-500">1 stok menipis • 1 stok habis</p>
                    </div>
                    <div class="bg-white flex flex-col p-4 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Obat nonaktif</p>
                        <h4 class="text-3xl font-bold text-gray-900 mb-2">1</h4>
                        <p class="text-xs text-gray-500">Tidak tersedia untuk resep baru</p>
                    </div>
                </div>

                <!-- Main Data Card -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">

                    <!-- Card Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                        <h4 class="text-xl font-bold text-gray-900">Daftar obat</h4>
                        <span class="text-xs text-gray-500">Terakhir diperbarui hari ini, 09.15</span>
                    </div>

                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 my-6">
                        <div class="relative col-span-2">
                            <input type="search" id="search" class="block w-full md: p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500" placeholder="Cari data pasien..." required />
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                        </div>
                        <select id="bentuk-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500">
                            <option selected>Semua Bentuk</option>
                            <option value="laptop">Laptop</option>
                            <option value="desktop">Desktop</option>
                        </select>
                        <select id="stok-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500">
                            <option selected>Semua Stok</option>
                            <option value="laptop">Laptop</option>
                            <option value="desktop">Desktop</option>
                        </select>
                        <select id="status-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500">
                            <option selected>Semua Status</option>
                            <option value="laptop">Laptop</option>
                            <option value="desktop">Desktop</option>
                        </select>
                    </div>

                    <!-- Table -->
                    <div class="relative overflow-x-auto mb-4">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-700 bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Kode</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Nama obat</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Bentuk</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Satuan</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Stok & ketersediaan</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4 text-gray-500">OBT-001</td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">Paracetamol</div>
                                        <div class="text-xs text-gray-500">500 mg</div>
                                    </td>
                                    <td class="px-4 py-4">Tablet</td>
                                    <td class="px-4 py-4">Tablet</td>
                                    <td class="px-4 py-4 font-bold text-gray-900 flex items-center gap-2 mt-2 md:mt-0">
                                        240 <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Tersedia</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Aktif</span>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap">
                                        <button data-modal-target="modal-detail-obat" data-modal-toggle="modal-detail-obat" class="hover:underline mr-3 cursor-pointer">Detail</button>
                                        <button type="button" data-modal-target="modal-atur-stok" data-modal-toggle="modal-atur-stok" class="hover:underline">Atur stok</button>
                                    </td>
                                </tr>
                                <!-- Row 2 (Menipis) -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4 text-gray-500">OBT-006</td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">Oralit</div>
                                        <div class="text-xs text-gray-500">Serbuk oral</div>
                                    </td>
                                    <td class="px-4 py-4">Serbuk</td>
                                    <td class="px-4 py-4">Sachet</td>
                                    <td class="px-4 py-4 font-bold text-gray-900 flex items-center gap-2 mt-2 md:mt-0">
                                        5 <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-orange-100 text-orange-800">Menipis</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Aktif</span>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap">
                                        <button data-modal-target="modal-detail-obat" data-modal-toggle="modal-detail-obat" class="hover:underline mr-3 cursor-pointer">Detail</button>
                                        <button type="button" data-modal-target="modal-atur-stok" data-modal-toggle="modal-atur-stok" class="hover:underline">Atur stok</button>
                                    </td>
                                </tr>
                                <!-- Row 3 (Habis) -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4 text-gray-500">OBT-007</td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">Sirup batuk</div>
                                        <div class="text-xs text-gray-500">Botol 60 ml</div>
                                    </td>
                                    <td class="px-4 py-4">Sirup</td>
                                    <td class="px-4 py-4">Botol</td>
                                    <td class="px-4 py-4 font-bold text-gray-900 flex items-center gap-2 mt-2 md:mt-0">
                                        0 <span class="inline-flex items-center px-2 py-1 text-fg-danger-strong text-xs font-medium rounded bg-danger-soft">Habis</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Aktif</span>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap">
                                        <button data-modal-target="modal-detail-obat" data-modal-toggle="modal-detail-obat" class="hover:underline mr-3 cursor-pointer">Detail</button>
                                        <button type="button" data-modal-target="modal-atur-stok" data-modal-toggle="modal-atur-stok" class="hover:underline">Atur stok</button>
                                    </td>
                                </tr>
                                <!-- Row 4 (Nonaktif) -->
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4 text-gray-500">OBT-008</td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-900">Ibuprofen</div>
                                        <div class="text-xs text-gray-500">200 mg</div>
                                    </td>
                                    <td class="px-4 py-4">Tablet</td>
                                    <td class="px-4 py-4">Tablet</td>
                                    <td class="px-4 py-4 font-bold text-gray-900 flex items-center gap-2 mt-2 md:mt-0">
                                        45 <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Tersedia</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Nonaktif</span>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap">
                                        <button data-modal-target="modal-detail-obat" data-modal-toggle="modal-detail-obat" class="hover:underline mr-3 cursor-pointer">Detail</button>
                                        <button type="button" data-modal-target="modal-atur-stok" data-modal-toggle="modal-atur-stok" class="hover:underline">Atur stok</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 mb-6 gap-4">
                        <span>Menampilkan 1-8 dari 8 obat</span>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-gray-400 hover:text-gray-900 disabled:opacity-50" disabled>&#8249; Sebelum</button>
                            <span class="px-2.5 py-1 rounded bg-primary-100 text-primary-700 font-medium">1</span>
                            <button type="button" class="text-gray-600 hover:text-gray-900">Berikut &#8250;</button>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="bg-primary-50 rounded-base p-4 text-sm text-gray-600 border border-primary-100">
                        Contoh ambang peringatan: Oralit &le; 20 sachet. Ambang dapat diatur; status aktif terpisah dari ketersediaan stok.
                    </div>

                </div>
            </main>

        </div>
    </div>

    <?php require_once __DIR__ . '/components/obat/form-tambah-obat.php'; ?>
    <?php require_once __DIR__ . '/components/obat/detail-obat.php'; ?>
    <?php require_once __DIR__ . '/components/obat/atur-stok.php'; ?>
    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>