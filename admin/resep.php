<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Resep";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar (Diasumsikan komponen ada) -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar (Diasumsikan komponen ada) -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">

                <!-- Page Info & Action -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <p class="text-gray-600 font-medium">Tinjau resep dokter, cek stok, lalu proses penyiapan obat.</p>
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-primary-800 bg-primary-100 rounded-base">
                        Resep dibuat oleh dokter
                    </span>
                </div>

                <!-- Stats Counter -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white flex flex-col p-4 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Resep hari ini</p>
                        <h4 class="text-3xl font-bold text-gray-900 mb-2">6</h4>
                        <p class="text-xs text-gray-500">Kamis, 24 September 2026</p>
                    </div>
                    <div class="bg-orange-100 flex flex-col p-4 border border-orange-200 rounded-base shadow-sm">
                        <p class="text-sm text-orange-800 font-semibold mb-2">Menunggu</p>
                        <h4 class="text-3xl font-bold text-orange-800 mb-2">2</h4>
                        <p class="text-xs text-gray-500">Perlu pemeriksaan stok</p>
                    </div>
                    <div class="bg-blue-100 flex flex-col p-4 border border-blue-200 rounded-base shadow-sm">
                        <p class="text-sm text-blue-900 font-semibold mb-2">Diproses</p>
                        <h4 class="text-3xl font-bold text-blue-900 mb-2">1</h4>
                        <p class="text-xs text-gray-500">Dalam penyiapan obat</p>
                    </div>
                    <div class="bg-white flex flex-col p-4 border border-gray-200 rounded-base shadow-sm">
                        <p class="text-sm text-gray-600 font-semibold mb-2">Selesai</p>
                        <h4 class="text-3xl font-bold text-gray-900 mb-2">3</h4>
                        <p class="text-xs text-gray-500">Pemrosesan telah selesai</p>
                    </div>
                </div>

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Left Column: Antrean Resep -->
                    <div id="left-card-resep" class="lg:col-span-12 p-6 bg-white border border-gray-200 rounded-base shadow-sm flex flex-col h-full">
                        <div class="border-b border-gray-100 flex justify-between items-center">
                            <h4 class="text-xl font-bold text-gray-900">Antrean resep</h4>
                            <span class="text-sm text-gray-500">6 resep</span>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-col gap-4 my-3">
                            <div class="relative w-full">
                                <input type="search" id="search" class="block w-full p-2.5 pe-10 text-sm text-gray-900 border border-primary-400-05 rounded-base focus:ring-primary-500 focus:border-primary-500" placeholder="Cari pasien atau nomor resep" />
                                <div class="absolute inset-y-0 inset-e-0 flex items-center pe-3 pointer-events-none">
                                    <i class="fas fa-search text-primary"></i>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="date" value="2026-09-24" class="block w-full p-2.5 text-sm text-gray-900 border border-primary-400-05 rounded-base focus:ring-primary-500 focus:border-primary-500" />
                                </div>
                                <select class="block w-full p-2.5 text-sm text-gray-900 border border-primary-400-05 rounded-base focus:ring-primary-500 focus:border-primary-500">
                                    <option selected>Semua status</option>
                                    <option value="menunggu">Menunggu</option>
                                    <option value="diproses">Diproses</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                        </div>

                        <!-- List Antrean -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-xs text-gray-500 border-b border-gray-200">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 font-semibold w-1/2">Pasien / resep</th>
                                        <th scope="col" class="px-4 py-3 font-semibold">Dokter</th>
                                        <th scope="col" class="px-4 py-3 font-semibold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Row Active (Selected) -->
                                    <tr class="bg-primary-50 border-b border-s-4 border-s-primary border-primary-100 cursor-pointer transition-colors" onclick="detailDataResep(this)">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-base">Sinta Maharani</div>
                                            <div class="text-xs text-gray-500 mt-0.5">RSP-024 &middot; 09.10 &middot; 2 obat</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">dr. Andi Saputra</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-orange-100 text-orange-800">Menunggu</span>
                                        </td>
                                    </tr>
                                    <!-- Row 2 -->
                                    <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" onclick="detailDataResep(this)">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-base">Rizal Pratama</div>
                                            <div class="text-xs text-gray-500 mt-0.5">RSP-023 &middot; 09.00 &middot; 1 obat</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">dr. Andi Saputra</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-orange-100 text-orange-800">Menunggu</span>
                                        </td>
                                    </tr>
                                    <!-- Row 3 -->
                                    <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" onclick="detailDataResep(this)">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-base">Nadia Putri</div>
                                            <div class="text-xs text-gray-500 mt-0.5">RSP-022 &middot; 08.50 &middot; 2 obat</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">dr. Rina Putri</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="ml-1 inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-brand-softer text-fg-brand-strong">
                                                Diproses
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- Row 4 -->
                                    <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" onclick="detailDataResep(this)">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-base">Bima Setiawan</div>
                                            <div class="text-xs text-gray-500 mt-0.5">RSP-021 &middot; 08.40 &middot; 1 obat</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">dr. Andi Saputra</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Selesai</span>
                                        </td>
                                    </tr>
                                    <!-- Row 5 -->
                                    <tr class="bg-white border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" onclick="detailDataResep(this)">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-base">Ayu Lestari</div>
                                            <div class="text-xs text-gray-500 mt-0.5">RSP-020 &middot; 08.30 &middot; 2 obat</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">dr. Rina Putri</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Selesai</span>
                                        </td>
                                    </tr>
                                    <!-- Row 6 -->
                                    <tr class="bg-white hover:bg-gray-50 cursor-pointer transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-base">Fajar Ramadhan</div>
                                            <div class="text-xs text-gray-500 mt-0.5">RSP-019 &middot; 08.20 &middot; 1 obat</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">dr. Andi Saputra</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Selesai</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-0 mt-auto">
                            <p class="text-xs text-gray-500">Menampilkan 1-6 dari 6 resep &middot; Pilih baris untuk detail</p>
                        </div>
                    </div>

                    <!-- Right Column: Detail Resep -->
                    <div id="right-card-resep" class="hidden lg:col-span-5 bg-white border border-gray-200 rounded-base shadow-sm p-6 flex-col h-full">
                        <!-- Detail Header -->
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Detail RSP-024</h3>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-orange-100 text-orange-800">Menunggu</span>
                        </div>

                        <!-- Patient Info -->
                        <div class="mb-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Sinta Maharani</h4>
                            <p class="text-sm text-gray-500 mb-1">PSN-0142 &middot; 24 Sep 2026, 09.10</p>
                            <p class="text-sm text-gray-600">Dokter penulis: dr. Andi Saputra</p>
                        </div>

                        <!-- Doctor Note -->
                        <div class="bg-gray-50 border border-gray-100 p-4 rounded-base mb-6">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Catatan dokter</p>
                            <p class="text-sm text-gray-800">Siapkan sesuai jumlah dan instruksi yang tercatat.</p>
                        </div>

                        <!-- Medicine List -->
                        <div class="mb-3 flex-1">
                            <h5 class="text-sm font-bold text-gray-900 mb-4">2 obat &middot; Instruksi tercatat dari dokter</h5>

                            <!-- Item 1 -->
                            <div class="bg-page border border-primary-50 p-4 rounded-base mb-4">
                                <div class="flex justify-between items-start mb-1">
                                    <h6 class="font-bold text-gray-900 text-base">Paracetamol 500 mg</h6>
                                    <span class="font-bold text-primary-600 text-sm">10 tablet</span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">OBT-001 &middot; Stok 240 tablet &middot; Sisa setelah proses 230</p>
                                <p class="text-sm text-gray-800">Dosis: 1 tablet, 3 kali sehari bila demam.</p>
                                <p class="text-sm text-gray-800">Aturan pakai: sesudah makan.</p>
                            </div>

                            <!-- Item 2 -->
                            <div class="bg-page border border-primary-50 p-4 rounded-base">
                                <div class="flex justify-between items-start mb-1">
                                    <h6 class="font-bold text-gray-900 text-base">Cetirizine 10 mg</h6>
                                    <span class="font-bold text-primary-600 text-sm">5 tablet</span>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">OBT-002 &middot; Stok 80 tablet &middot; Sisa setelah proses 75</p>
                                <p class="text-sm text-gray-800">Dosis: 1 tablet, 1 kali sehari.</p>
                                <p class="text-sm text-gray-800">Aturan pakai: malam hari. Catatan: dapat mengantuk.</p>
                            </div>
                        </div>

                        <!-- Action Area -->
                        <div class="border-t border-gray-100">
                            <div class="bg-green-50 border border-green-100 p-3 rounded-base mb-4 flex items-center">
                                <i class="far fa-check-circle text-green-600 mr-2"></i>
                                <span class="text-sm font-semibold text-green-800">Stok cukup untuk seluruh item resep</span>
                            </div>

                            <div class="flex flex-col md:flex-row md:gap-0 gap-3 justify-between items-center mb-6">
                                <p class="text-xs text-gray-500">Stok dikurangi setelah proses dikonfirmasi.</p>
                                <button type="button" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-base hover:bg-primary-600 focus:ring-4 focus:ring-primary-300 transition-colors">
                                    <i class="fas fa-check mr-2"></i> Proses resep
                                </button>
                            </div>

                            <p class="text-xs text-gray-400 leading-relaxed">
                                Admin hanya memproses resep. Dosis dan aturan pakai tidak dapat diubah; klarifikasi kepada dokter jika diperlukan.
                            </p>
                        </div>

                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
    <!-- Script App.js -->
    <script src="./../assets/js/app.js"></script>
</body>

</html>