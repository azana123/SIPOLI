<?php
$page_title = "Dashboard Admin";
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
        <aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-full h-screen transition-transform -translate-x-full md:translate-x-0 md:relative md:h-auto" aria-label="Sidebar">
            <div class="h-full px-3 py-4 bg-primary-500 border-e border-default">
                <a href="#" class="flex items-center ps-2.5 mb-5">
                    <h3 class="text-white font-bold text-3xl">SIPOLI</h3>
                </a>
                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 btn-active">
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Jadwal & Kuota</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Reservasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Data Master</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Konfirmasi H-1</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Obat & Stok</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Resep</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Kirim Informasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                            <span class="ms-3">Notifikasi</span>
                        </a>
                    </li>
                    <li class="text-center md:hidden">
                        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" type="button" class="bg-white border border-transparent hover focus:ring-4 focus:ring-primary-400-05 shadow-xs font-medium leading-5 rounded-full text-sm px-1 py-1 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#1f1f1f">
                                <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <nav class="bg-neutral-primary sticky top-0 z-30 border-b border-default">
                <div class="max-w-7xl flex flex-wrap items-center justify-between mx-auto px-4 py-2.5">
                    <!-- Tambahan: Wrapper flex untuk Hamburger dan Title -->
                    <div class="flex items-center justify-start">
                        <!-- Hamburger Menu Toggle -->
                        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 mr-3 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                            <span class="sr-only">Buka sidebar</span>
                            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                            </svg>
                        </button>

                        <div class="page-title">
                            <h5 class="font-bold text-sm">Beranda</h5>
                            <span class="date text-gray-500 text-sm">Kamis, 24 September 2026</span>
                        </div>
                    </div>

                    <div class="right-nav flex items-center">
                        <button id="add-pasien-offline" data-modal-target="modal-tambah-pasien" data-modal-toggle="modal-tambah-pasien" class="hidden md:inline-flex items-center  text-white bg-primary hover:bg-primary-700 box-border border border-transparent focus:ring-4 focus:ring-primary-400 shadow-xs font-medium leading-5 rounded text-xs px-2.5 py-1 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#fff">
                                <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                            </svg>
                            Tambahkan Pasien Offline
                        </button>

                        <!-- Greeting User -->
                        <div class="flex items-center">
                            <div class="flex items-center ms-3">
                                <div>
                                    <button type="button" class="flex font-medium text-sm text-gray-500 focus:ring-gray-300 cursor-pointer" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                                        <span class="sr-only">Open user menu</span>
                                        <p>Hallo, Admin</p>
                                    </button>
                                </div>
                                <div class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44" id="dropdown-user">
                                    <div class="px-4 py-3 border-b border-default-medium" role="none">
                                        <p class="text-sm font-medium text-heading" role="none">
                                            Neil Sims
                                        </p>
                                        <p class="text-sm text-body truncate" role="none">
                                            neil.sims@flowbite.com
                                        </p>
                                    </div>
                                    <ul class="p-2 text-sm text-body font-medium" role="none">
                                        <li>
                                            <a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded" role="menuitem">Dashboard</a>
                                        </li>
                                        <li>
                                            <a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded" role="menuitem">Settings</a>
                                        </li>
                                        <li>
                                            <a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded" role="menuitem">Earnings</a>
                                        </li>
                                        <li>
                                            <a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded" role="menuitem">Sign out</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">
                <!-- Counter -->
                <div class="counter grid grid-cols-2 md:grid-cols-4 gap-3.5">
                    <div class="bg-neutral-primary-soft block p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Reservasi Hari ini</p>
                        <h4 class="text-2xl font-bold">45</h4>
                    </div>
                    <div class="bg-neutral-primary-soft block p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Reservasi Hari ini</p>
                        <h4 class="text-2xl font-bold">45<span class="text-sm text-gray-400">/100</span></h4>
                    </div>
                    <div class="bg-yellow-100 block p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-yellow-400 rounded-base shadow-xs text-yellow-900">
                        <p class="font-medium">Reservasi Hari ini</p>
                        <h4 class="text-2xl font-bold">45</h4>
                    </div>
                    <div class="bg-blue-200 block p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-blue-400 rounded-base shadow-xs text-blue-900">
                        <p class="font-medium">Reservasi Hari ini</p>
                        <h4 class="text-2xl font-bold">45</h4>
                    </div>
                </div>
                <!-- End of Counter -->

                <!-- Tindakan -->
                <div class="my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
                    <h4 class="text-lg font-bold">Perlu Tindakan</h4>
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right text-body">
                            <tbody class="text-primary-600">
                                <tr class="bg-neutral-primary border-b border-primary-400-05">
                                    <th scope="row" class="py-4 font-medium text-heading  whitespace-nowrap">
                                        Apple MacBook Pro 17"
                                    </th>
                                    <td class="px-6 py">
                                        Silver
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary border-b border-primary-400-05">
                                    <th scope="row" class="py-4 font-medium text-heading  whitespace-nowrap">
                                        Apple MacBook Pro 17"
                                    </th>
                                    <td class="px-6 py-4">
                                        Silver
                                    </td>
                                    <td class="px-6 py-4">
                                        Laptop
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Selesai</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daftar Reservasi -->
                <div class="my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
                    <div class="title-card flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold">Reservasi Hari ini</h4>
                        <a href="./users/jadwal-pemeriksaan.php" class="action-link text-primary-400 hover:text-primary-700">
                            <span class="font-medium text-sm">Lihat Semua</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="reservation-table" id="reservation-table">
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-body">
                                <thead class="text-sm text-primary-600 border-b border-primary-400-05">
                                    <tr>
                                        <th scope="col" class="py-3 font-semibold">Pasien</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Poli</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Jam Praktik</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-neutral-primary border-b border-primary-400-05">

                                        <th scope="row" class="py-4 font-medium text-heading  whitespace-nowrap">

                                            Apple MacBook Pro 17"

                                        </th>

                                        <td class="px-6 py-4">

                                            Silver

                                        </td>

                                        <td class="px-6 py-4">

                                            Laptop

                                        </td>

                                        <td class="px-6 py-4">

                                            <span class="inline-flex items-center px-2 py-1 text-fg-brand-strong text-xs font-medium rounded bg-brand-softer">Reschedule</span>

                                        </td>

                                    </tr>
                                    <tr class="bg-neutral-primary border-b border-primary-400-05">
                                        <th scope="row" class="py-4 font-medium text-heading  whitespace-nowrap">
                                            Apple MacBook Pro 17"
                                        </th>
                                        <td class="px-6 py-4">
                                            Silver
                                        </td>
                                        <td class="px-6 py-4">
                                            Laptop
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Selesai</span>
                                        </td>
                                    </tr>
                                    <tr class="bg-neutral-primary border-b border-primary-400-05">
                                        <th scope="row" class="py-4 font-medium text-heading  whitespace-nowrap">
                                            Apple MacBook Pro 17"
                                        </th>
                                        <td class="px-6 py-4">
                                            Silver
                                        </td>
                                        <td class="px-6 py-4">
                                            Laptop
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2 py-1 text-fg-danger-strong text-xs font-medium rounded bg-danger-soft">Menunggu</span>
                                        </td>
                                    </tr>
                                    <tr class="bg-neutral-primary border-b border-primary-400-05">
                                        <th scope="row" class="py-4 font-medium text-heading  whitespace-nowrap">
                                            Apple MacBook Pro 17"
                                        </th>
                                        <td class="px-6 py-4">
                                            Silver
                                        </td>
                                        <td class="px-6 py-4">
                                            Laptop
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Terkonfirmasi</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kuota -->
                <div class="my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
                    <h4 class="text-lg font-bold mb-4">Kuota Hari ini</h4>
                    <div class="chart-bar">
                        <div class="mb-3">
                            <div class="flex justify-between mb-1">
                                <span class="text-base font-medium text-gray-700">Poli Gigi</span>
                                <span class="text-sm font-medium text-gray-700">5/5</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-warning h-2.5 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

        </div>
    </div>

    <?php require_once __DIR__ . '/components/form-pasien-offline.php'; ?>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>