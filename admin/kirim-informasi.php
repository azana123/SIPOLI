<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Kirim Informasi";
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
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar (Asumsi judul halaman "Kirim Informasi" dirender dari sini) -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">

                <!-- Header Content -->
                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                    <p class="text-sm text-gray-600">Sampaikan informasi layanan dan perubahan jadwal kepada pasien.</p>
                    <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Draf • belum dikirim</span>
                </div>

                <!-- Grid Form & Preview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    
                    <!-- Left: Tulis Informasi -->
                    <div class="lg:col-span-2 bg-white p-6 border border-gray-200 rounded-base shadow-sm flex flex-col">
                        <h4 class="text-xl font-bold text-gray-900 mb-6">Tulis informasi</h4>
                        
                        <form action="" method="POST" class="flex flex-col gap-5 flex-1">
                            <!-- Input Penerima -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-900">Penerima</label>
                                    <a href="#" class="text-sm text-primary-600 hover:underline font-medium">Pilih pasien / reservasi</a>
                                </div>
                                <div class="relative">
                                    <input type="text" class="bg-white border border-primary-400-05 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 pr-10" value="Rizal Pratama · Reservasi RSV-0261" readonly>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="far fa-user text-primary-500"></i>
                                    </div>
                                </div>
                                <!-- Info Sub-text -->
                                <div class="mt-2 bg-page text-gray-600 text-xs p-2 rounded-base flex items-center gap-2">
                                    <i class="far fa-calendar-alt text-primary-500"></i>
                                    <span>25 Sep 2026 · Perubahan waktu kunjungan 09.00 → 13.00 WIB</span>
                                </div>
                            </div>

                            <!-- Grid Jenis & Media -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">Jenis informasi</label>
                                    <select class="bg-white border border-primary-400-05 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                        <option>Perubahan jadwal</option>
                                        <option>Informasi umum</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">Media pengiriman</label>
                                    <select class="bg-white border border-primary-400-05 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                        <option>Notifikasi aplikasi</option>
                                        <option>WhatsApp (Manual)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Input Judul -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Judul</label>
                                <div class="relative">
                                    <input type="text" class="bg-white border border-primary-400-05 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 pr-10" value="Perubahan jadwal kunjungan · 25 September">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-pen text-primary"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Textarea Pesan -->
                            <div class="flex-1">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Pesan</label>
                                <textarea rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-base border border-primary-400-05 focus:ring-primary-500 focus:border-primary-500 resize-none h-32">Jadwal kunjungan Anda pada Jumat, 25 September 2026 berubah dari pukul 09.00 menjadi 13.00 WIB. Mohon konfirmasi ketersediaan melalui menu Reservasi di SIPOLI. Terima kasih.</textarea>
                            </div>

                            <!-- Footer Form -->
                            <div class="flex flex-col-reverse md:flex-row gap-3 justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500 text-center">1 penerima · Periksa pesan sebelum mengirim</span>
                                <button type="button" class="inline-flex items-center text-white bg-primary-500 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-base text-sm px-5 py-2.5">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim informasi
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Pratinjau Pesan -->
                    <div class="bg-white p-6 border border-gray-200 rounded-base shadow-sm flex flex-col">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-xl font-bold text-gray-900">Pratinjau pesan</h4>
                            <i class="far fa-bell text-primary-600 text-lg"></i>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Tampilan notifikasi yang akan diterima pasien di SIPOLI.</p>

                        <!-- Mockup Notifikasi -->
                        <div class="bg-page border border-gray-200 rounded-base p-5 mb-6 flex-1">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm font-bold text-primary-700">Sipoli</span>
                                <span class="text-xs text-gray-400">Pratinjau draf</span>
                            </div>
                            <h5 class="text-base font-bold text-gray-900 mb-2 leading-tight">Perubahan jadwal kunjungan · 25 September</h5>
                            <p class="text-sm text-gray-700 mb-4 leading-relaxed">Jadwal kunjungan Anda pada Jumat, 25 September 2026 berubah dari pukul 09.00 menjadi 13.00 WIB. Mohon konfirmasi ketersediaan melalui menu Reservasi di SIPOLI. Terima kasih.</p>
                            <a href="#" class="text-sm font-bold text-primary-600 hover:underline">Buka Reservasi →</a>
                        </div>

                        <!-- Info Penerima & Alert -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm text-gray-600">Rizal Pratama</span>
                                <span class="inline-flex items-center px-2 py-1 text-fg-brand-strong text-xs font-medium rounded bg-brand-softer">1 Penerima</span>
                            </div>
                            <div class="bg-primary-50 p-4 rounded-base border border-primary-100">
                                <p class="text-sm font-bold text-primary-700 mb-1">Jika menggunakan WhatsApp</p>
                                <p class="text-xs text-primary-600/80">Hubungi pasien secara manual, lalu catat status kontak. Tidak ada pengiriman otomatis atau status baca WhatsApp.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Card Riwayat -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                        <h4 class="text-lg font-bold text-gray-900">Riwayat pengiriman terakhir</h4>
                        <span class="text-sm text-gray-500">3 catatan terakhir · 23-24 Sep 2026</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3 rounded-tl-base">Waktu kirim / kontak</th>
                                    <th scope="col" class="px-4 py-3">Penerima / judul</th>
                                    <th scope="col" class="px-4 py-3">Jenis</th>
                                    <th scope="col" class="px-4 py-3">Media</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3 rounded-tr-base">Waktu dibaca</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">24 Sep · 08.55</td>
                                    <td class="px-4 py-4">
                                        <p class="font-bold text-gray-900">Dewi Anggriani</p>
                                        <p class="text-xs text-gray-400">Konfirmasi kunjungan besok</p>
                                    </td>
                                    <td class="px-4 py-4">Konfirmasi H-1</td>
                                    <td class="px-4 py-4">WhatsApp (manual)</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 text-heading text-xs font-medium rounded bg-gray-200">Kontak dicatat</span>
                                    </td>
                                    <td class="px-4 py-4">Tidak tersedia</td>
                                </tr>
                                <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">24 Sep · 08.40</td>
                                    <td class="px-4 py-4">
                                        <p class="font-bold text-gray-900">Nadia Putri</p>
                                        <p class="text-xs text-gray-400">Informasi layanan poliklinik</p>
                                    </td>
                                    <td class="px-4 py-4">Informasi umum</td>
                                    <td class="px-4 py-4">Notifikasi aplikasi</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Terkirim</span>
                                    </td>
                                    <td class="px-4 py-4">24 Sep · 08.45</td>
                                </tr>
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">23 Sep · 15.10</td>
                                    <td class="px-4 py-4">
                                        <p class="font-bold text-gray-900">Bima Setiawan</p>
                                        <p class="text-xs text-gray-400">Perubahan jadwal kunjungan</p>
                                    </td>
                                    <td class="px-4 py-4">Perubahan jadwal</td>
                                    <td class="px-4 py-4">Notifikasi aplikasi</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Terkirim</span>
                                    </td>
                                    <td class="px-4 py-4">Belum dibaca</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Status dibaca hanya untuk notifikasi aplikasi. Kontak WhatsApp dicatat oleh admin, bukan bukti pesan terkirim atau dibaca.</p>
                </div>

            </main>
        </div>
    </div>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>