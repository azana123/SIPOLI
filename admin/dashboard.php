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
        <?php require_once __DIR__ . "/components/sidebar.php" ?>


        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

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