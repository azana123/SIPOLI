<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Konfirmasi H-1";
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

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">

                <!-- Page Header (Opsional jika belum ada di navbar) -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Konfirmasi H-1</h1>
                    <p class="text-gray-500 mt-1">Kamis, 24 September 2026</p>
                </div>

                <!-- Banner Informasi Tanggal -->
                <div class="bg-white border border-gray-200 rounded-base p-4 flex flex-col md:flex-row items-center md:justify-between shadow-xs mb-6 gap-4">
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <div class="text-primary-600 text-xl px-2">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal konfirmasi &middot; hari ini</p>
                            <p class="font-bold text-gray-900 text-sm">Kamis, 24 September 2026</p>
                        </div>
                        <div class="text-gray-400 px-2 md:px-4">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal konseling &middot; besok</p>
                            <p class="font-bold text-gray-900 text-sm">Jumat, 25 September 2026</p>
                        </div>
                    </div>
                    <div class="w-full md:w-auto flex justify-start md:justify-end">
                        <span class="bg-primary-50 text-primary-700 text-xs font-bold px-3 py-1.5 rounded-base">Konseling &middot; 5 pasien</span>
                    </div>
                </div>

                <!-- Ringkasan Cards -->
                <div class="mb-6">
                    <p class="text-xs text-gray-500 mb-3 font-medium">Ringkasan H-1 &middot; kunjungan 25 September 2026 &middot; respons dicatat admin</p>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Card 1 -->
                        <div class="bg-white p-5 border border-gray-200 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-gray-700 font-semibold">Pasien konseling besok</p>
                            <h4 class="text-4xl font-bold my-3 text-gray-900 flex-1">5</h4>
                            <p class="text-xs text-gray-500">Batas maksimal 5 pasien per hari</p>
                        </div>
                        <!-- Card 2 (Highlighted) -->
                        <div class="bg-yellow-100 p-5 border border-yellow-400 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-yellow-900 font-semibold">Menunggu respons</p>
                            <h4 class="text-4xl font-bold my-3 text-yellow-900 flex-1">3</h4>
                            <p class="text-xs text-yellow-900">Belum mengonfirmasi kehadiran</p>
                        </div>
                        <!-- Card 3 -->
                        <div class="bg-white p-5 border border-gray-200 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-gray-700 font-semibold">Konfirmasi hadir</p>
                            <h4 class="text-4xl font-bold my-3 text-gray-900 flex-1">1</h4>
                            <p class="text-xs text-gray-500">Pasien bersedia hadir besok</p>
                        </div>
                        <!-- Card 4 -->
                        <div class="bg-white p-5 border border-gray-200 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-gray-700 font-semibold">Tidak bisa hadir</p>
                            <h4 class="text-4xl font-bold my-3 text-gray-900 flex-1">1</h4>
                            <p class="text-xs text-gray-500">Perlu jadwal pengganti</p>
                        </div>
                    </div>
                </div>

                <!-- Main Data Table Section -->
                <div class="bg-white p-6 border border-gray-200 rounded-base shadow-xs mb-6">
                    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-2">
                        <h4 class="text-lg font-bold text-gray-900">Daftar konfirmasi konseling</h4>
                        <span class="text-xs text-gray-500">Hubungi via WhatsApp, lalu catat respons pasien</span>
                    </div>

                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 items-end">
                        <div class="flex flex-col md:col-span-4">
                            <label class="text-xs text-gray-600 mb-1.5">Pencarian pasien</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-primary-700">
                                    <i class="fas fa-search"></i>
                                </div>
                                <input type="text" class="block w-full ps-9 pe-3 py-2 text-sm text-gray-900 border border-primary-400-05 rounded-base focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400" placeholder="Cari nama pasien atau NIM / NIP">
                            </div>
                        </div>
                        <div class="flex flex-col md:col-span-3">
                            <label for="datepicker-autohide" class="text-sm text-gray-600">Tanggal kunjungan</label>
                            <div class="relative max-w-sm mt-1">
                                <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-primary-700" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                                    </svg>
                                </div>
                                <input id="datepicker-autohide" datepicker datepicker-autohide type="text" class="block w-full ps-3 pe-3  border border-primary-400-05 text-heading text-sm rounded-base focus:ring-primary focus:border-primary px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Pilih Tanggal">
                            </div>
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label class="text-xs text-gray-600 mb-1.5">Konfirmasi</label>
                            <select class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 focus:ring-primary-500 focus:border-primary-500">
                                <option>Semua status</option>
                            </select>
                        </div>
                        <div class="flex flex-col md:col-span-3">
                            <label class="text-xs text-gray-600 mb-1.5">Kontak</label>
                            <div class="flex gap-3 items-center">
                                <select class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 focus:ring-primary-500 focus:border-primary-500">
                                    <option>Semua kontak</option>
                                </select>
                                <a href="#" class="text-xs text-primary-600 font-medium hover:underline whitespace-nowrap">Reset filter</a>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="py-3 font-semibold w-1/5">Pasien / kontak</th>
                                    <th scope="col" class="py-3 font-semibold w-1/5">Jadwal / psikolog</th>
                                    <th scope="col" class="py-3 font-semibold w-1/5">Konfirmasi</th>
                                    <th scope="col" class="py-3 font-semibold w-1/5">Kontak terakhir</th>
                                    <th scope="col" class="py-3 font-semibold w-1/5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr class="border-b border-gray-100">
                                    <td class="py-4 pe-3 align-top">
                                        <div class="font-bold text-gray-900 text-sm mb-0.5">Intan Permata</div>
                                        <div class="text-xs text-gray-500 mb-0.5">NIM &middot; 2341720107</div>
                                        <div class="text-xs text-gray-500">08** **** 0107</div>
                                    </td>
                                    <td class="py-4 pe-3 align-top">
                                        <div class="font-medium text-gray-900 text-xs mb-1">25 Sep 2026 &middot; 09.00–09.30 WIB</div>
                                        <div class="text-xs text-gray-500 mb-0.5">Dewi Anggraini, M.Psi.</div>
                                        <div class="text-xs text-gray-400">Konseling</div>
                                    </td>
                                    <td class="py-4 pe-3 align-top">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu Respons</span>
                                        <div class="text-xs text-gray-500">Belum ada balasan</div>
                                    </td>
                                    <td class="py-4 pe-3 align-top">
                                        <div class="font-medium text-gray-900 text-xs mb-1">24 Sep &middot; 08.30 WIB</div>
                                        <div class="text-xs text-primary-600 mb-0.5">WhatsApp &middot; Terkirim</div>
                                        <div class="text-xs text-gray-500">Belum ada respons</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <div class="flex flex-col gap-2.5 items-start">
                                            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-primary-700 bg-primary-50 hover:bg-primary-100 text-xs font-semibold rounded-base transition-colors">
                                                <i class="far fa-comment-alt"></i> Notifikasi
                                            </button>
                                            <button type="button" data-modal-target="modal-catat-respons" data-modal-toggle="modal-catat-respons" class="text-primary-700 text-xs font-bold hover:underline px-1">Catat Respons</button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="border-b border-gray-100">
                                    <td class="py-4 pe-3 align-top">
                                        <div class="font-bold text-gray-900 text-sm mb-0.5">Yoga Pratama</div>
                                        <div class="text-xs text-gray-500 mb-0.5">NIM &middot; 2341720108</div>
                                        <div class="text-xs text-gray-500">08** **** 0108</div>
                                    </td>
                                    <td class="py-4 pe-3 align-top">
                                        <div class="font-medium text-gray-900 text-xs mb-1">25 Sep 2026 &middot; 09.30–10.00 WIB</div>
                                        <div class="text-xs text-gray-500 mb-0.5">Dewi Anggraini, M.Psi.</div>
                                        <div class="text-xs text-gray-400">Konseling</div>
                                    </td>
                                    <td class="py-4 pe-3 align-top">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Konfirmasi Hadir</span>
                                        <div class="text-xs text-gray-500">Dikonfirmasi 08.55</div>
                                    </td>
                                    <td class="py-4 pe-3 align-top">
                                        <div class="font-medium text-gray-900 text-xs mb-1">24 Sep &middot; 08.55 WIB</div>
                                        <div class="text-xs text-primary-600 mb-0.5">WhatsApp &middot; Terkirim</div>
                                        <div class="text-xs text-gray-500">Pasien bersedia hadir</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <div class="flex flex-col gap-2.5 items-start">
                                            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-primary-700 bg-primary-50 hover:bg-primary-100 text-xs font-semibold rounded-base transition-colors">
                                                <i class="far fa-comment-alt"></i> Notifikasi
                                            </button>
                                            <button type="button" class="text-primary-700 text-xs font-bold hover:underline px-1">Catat Respons</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Table (Pagination/Summary Info) -->
                    <div class="flex flex-col md:flex-row justify-between md:items-center mt-6 pt-4 border-t border-gray-100 text-xs gap-3">
                        <p class="text-gray-400">Menampilkan 1-5 dari 5 pasien &middot; data ilustrasi</p>
                        <p class="font-bold text-primary-700">3 menunggu &middot; 1 hadir &middot; 1 tidak bisa hadir</p>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="bg-white border border-gray-200 rounded-base p-4 flex gap-3 shadow-xs">
                    <div class="text-primary-600 mt-0.5">
                        <i class="fas fa-info-circle text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-gray-900 mb-1.5">Belum membalas bukan berarti tidak hadir</h5>
                        <p class="text-xs text-gray-500 leading-relaxed">Catat waktu, media dan respons pasien. Jika pasien atau psikolog berhalangan, catat jadwal lama, jadwal baru dan alasan reschedule, lalu beri tahu pasien. Tandai tidak hadir hanya setelah tanggal kunjungan. Pasien yang tidak mengonfirmasi sekaligus tidak hadir tidak mendapat jadwal pengganti otomatis.</p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modals -->
    <?php  require_once __DIR__ . '/components/catat-respon.php';  ?>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>