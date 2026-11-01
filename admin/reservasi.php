<?php
$page_title = "Reservasi";
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
        <!-- Perubahan: Mengganti 'hidden md:block' menjadi 'fixed md:relative' agar dapat difungsikan sebagai drawer oleh flowbite di mode mobile -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>


        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">
                <!-- Counter -->
                <div class="counter grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div class="bg-neutral-primary-soft flex flex-col md:block p-4 md:px-4.5 md:py-3.5 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Total Kunjungan</p>
                        <h4 class="text-2xl font-bold my-1.5 flex-1">45</h4>
                        <p class="text-xs text-gray-500 text-wrap">4 online &bull; 2 offline</p>
                    </div>
                    <div class="bg-neutral-primary-soft flex flex-col md:block p-4 md:px-4.5 md:py-3.5 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Selesai</p>
                        <h4 class="text-2xl font-bold my-1.5 flex-1">45</h4>
                        <p class="text-xs text-gray-500 text-wrap">1 online &bull; 2 ditangani perawat</p>
                    </div>
                    <div class="bg-neutral-primary-soft flex flex-col md:block p-4 md:px-4.5 md:py-3.5 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Menunggu</p>
                        <h4 class="text-2xl font-bold my-1.5 flex-1">1</h4>
                        <p class="text-xs text-gray-500 text-wrap">1 reservasi belum check-in</p>
                    </div>
                    <div class="bg-yellow-100 flex flex-col md:block p-4 md:px-4.5 md:py-3.5 border whitespace-nowrap text-center md:text-left border-yellow-400 rounded-base shadow-xs text-yellow-900">
                        <p class="font-medium">Perlu reschedule</p>
                        <h4 class="text-2xl font-bold my-1.5 flex-1">45</h4>
                        <p class="text-xs">1 lainnya dibatalkan</p>
                    </div>
                </div>
                <!-- End of Counter -->

                <!-- Tindakan -->
                <div class="my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
                    <h4 class="text-lg font-bold">Daftar Reservasi</h4>

                    <div class="my-4 relative">
                        <label for="search-reservation" class="mb-2 text-sm font-medium text-gray-900 sr-only">Cari</label>
                        <input type="search" id="search-reservation" class="block w- p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="Cari data reservasi..." required />
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Filter -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="flex flex-col">
                            <label for="datepicker-autohide" class="text-sm text-primary-600">Tanggal kunjungan</label>
                            <div class="relative max-w-sm mt-1">
                                <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-primary-700" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z" />
                                    </svg>
                                </div>
                                <input id="datepicker-autohide" datepicker datepicker-autohide type="text" class="block w-full ps-3 pe-3  border border-primary-400-05 text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Pilih Tanggal">
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <label for="poli-filter" class="text-sm text-primary-600">Poli</label>
                            <select id="poli-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1">
                                <option selected>Semua Poli</option>
                                <option value="laptop">Laptop</option>
                                <option value="desktop">Desktop</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="status-filter" class="text-sm text-primary-600">Status</label>
                            <select id="status-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1">
                                <option selected>Semua Status</option>
                                <option value="laptop">Laptop</option>
                                <option value="desktop">Desktop</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="asal-filter" class="text-sm text-primary-600">Asal</label>
                            <select id="asal-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1">
                                <option selected>Semua Asal</option>
                                <option value="laptop">Laptop</option>
                                <option value="desktop">Desktop</option>
                            </select>
                        </div>
                        <div class="flex flex-row gap-2">
                            <button type="button" class="inline-flex items-center justify-center px-3 py-2.5 text-primary text-sm self-end font-medium rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">Hari ini</button>
                            <a href="" class="text-primary text-sm font-medium py-2.5 self-end hover:underline">Reset Filter</a>
                        </div>
                    </div>
                    <div class="relative overflow-x-auto overflow-y-auto h-1/2">
                        <table class="w-full text-sm text-left rtl:text-right text-body">
                            <thead class="text-xs text-heading uppercase bg-neutral-primary border-b border-primary-400-05">
                                <tr>
                                    <th scope="col" class="py-3">Pasien</th>
                                    <th scope="col" class="py-3">Layanan & jadwal</th>
                                    <th scope="col" class="py-3">Asal</th>
                                    <th scope="col" class="py-3">Status</th>
                                    <th scope="col" class="py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-primary-600">
                                <tr class="bg-neutral-primary border-b border-primary-400-05">
                                    <th scope="row" class="py-4 font-medium text-heading whitespace-nowrap">
                                        <div class="text-base">Alya Prameswari</div>
                                        <div class="text-xs font-normal">NIM · 2341720101</div>
                                    </th>
                                    <td class="py-4">
                                        <div class="font-medium text-heading">Poli Umum · 09.00–12.00</div>
                                        <div class="">dr. Andini Putri</div>
                                        <div class="text-xs ">24 Sep 2026 · WIB</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <span class="inline-flex items-center px-2 py-1 text-primary text-xs font-medium rounded bg-brand-softer">Online</span>
                                    </td>
                                    <td class="py-4 align-top">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                                        <div class="text-xs">Kunjungan selesai</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <button class="font-medium hover:underline text-primary cursor-pointer" data-modal-target="modal-detail-reservasi" data-modal-toggle="modal-detail-reservasi">Lihat</button>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary border-b border-primary-400-05">
                                    <th scope="row" class="py-4 font-medium text-heading whitespace-nowrap">
                                        <div class="text-base">Raka Aditya</div>
                                        <div class="text-xs font-normal">NIM · 2341720102</div>
                                    </th>
                                    <td class="py-4">
                                        <div class="font-medium text-heading">Poli Gigi · 09.00–12.00</div>
                                        <div class="">drg. Bima Pratama</div>
                                        <div class="text-xs">24 Sep 2026 · WIB</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <span class="inline-flex items-center px-2 py-1 text-primary text-xs font-medium rounded bg-brand-softer">Online</span>
                                    </td>
                                    <td class="py-4 align-top">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Perlu Reschedule</span>
                                        <div class="text-xs">Pasien Berhalangan</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <div class="flex items-center gap-3 pt-1">
                                            <a href="#" class="font-medium hover:underline text-primary">Lihat</a>
                                            <button type="button" class="inline-flex items-center px-3 py-1.5 text-primary text-sm font-medium rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">Reschedule</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary border-b border-primary-400-05">
                                    <th scope="row" class="py-4 font-medium text-heading whitespace-nowrap">
                                        <div class="text-base">Raka Aditya</div>
                                        <div class="text-xs font-normal">NIM · 2341720102</div>
                                    </th>
                                    <td class="py-4">
                                        <div class="font-medium text-heading">Poli Gigi · 09.00–12.00</div>
                                        <div class="">drg. Bima Pratama</div>
                                        <div class="text-xs">24 Sep 2026 · WIB</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <span class="inline-flex items-center px-2 py-1 text-primary text-xs font-medium rounded bg-brand-softer">Online</span>
                                    </td>
                                    <td class="py-4 align-top">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-primary text-xs font-medium rounded bg-brand-softer">Menunggu</span>
                                        <div class="text-xs">Belum check-in</div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <div class="flex items-center gap-3 pt-1">
                                            <a href="#" class="font-medium hover:underline text-primary">Lihat</a>
                                            <button type="button" class="inline-flex items-center px-3 py-1.5 text-primary text-sm font-medium rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">Check-in</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/detail-reservasi.php'; ?>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>