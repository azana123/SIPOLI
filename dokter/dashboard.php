<?php
require_once __DIR__ . "/../config/config.php";

$page_title = "Dashboard Dokter";
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
    <title><?= e($page_title ?? 'Beranda Dokter') ?> | <?= e($app_name ?? 'SIPOLI') ?></title>
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

            <!-- Navbar (Menyesuaikan desain header pada gambar) -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 p-8">

                <!-- Banner Praktik -->
                <div class="bg-white border border-gray-200 rounded-base p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 md:w-12 md:h-12 bg-primary-50 border border-primary-100 text-primary rounded-base flex items-center justify-center shrink-0">
                            <i class="far fa-calendar-check text-base md:text-xl"></i>
                        </div>
                        <div>
                            <div class="flex flex-col-reverse md:flex-row md:items-center md:gap-3 mb-1">
                                <h2 class="text-lg font-bold text-gray-900">Praktik saya hari ini</h2>
                                <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Sedang Berlangsung</span>
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

                <!-- Counters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Terjadwal -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Pasien terjadwal</p>
                            <i class="fas fa-user-friends text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1">6</h3>
                        <p class="text-xs text-gray-500">Reservasi sesi hari ini</p>
                    </div>
                    <!-- Menunggu -->
                    <div class="bg-yellow-100 border border-yellow-300 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Menunggu pemeriksaan</p>
                            <i class="far fa-clock text-yellow-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-yellow-700 mb-1">3</h3>
                        <p class="text-xs text-gray-500">Pasien dalam antrean saya</p>
                    </div>
                    <!-- Diperiksa -->
                    <div class="bg-primary-50/50 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Sedang diperiksa</p>
                            <i class="fas fa-stethoscope text-primary-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary mb-1">1</h3>
                        <p class="text-xs text-gray-500">Pemeriksaan belum selesai</p>
                    </div>
                    <!-- Selesai -->
                    <div class="bg-green-50/50 border border-green-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Pemeriksaan selesai</p>
                            <i class="far fa-check-circle text-green-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-green-700 mb-1">2</h3>
                        <p class="text-xs text-gray-500">Penanganan telah dicatat</p>
                    </div>
                </div>

                <!-- Tabel Pasien -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm mb-6">
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Pasien saya hari ini</h3>
                            <p class="text-sm text-gray-500">Reservasi Poli Umum &bull; Sesi 09.00–12.00 WIB</p>
                        </div>
                        <a href="#" class="text-primary hover:text-primary-700 text-sm font-medium mt-3 md:mt-0 flex items-center gap-1">
                            Buka daftar pasien <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/50 border-b border-gray-200 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Pasien / NIM atau NIP</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Jam</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Keluhan awal</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <!-- Row 1 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">Sinta Maharani</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2341720061 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">09.00</td>
                                    <td class="px-6 py-4 text-gray-500">Batuk dan pilek</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button data-modal-target="modal-ringkasan-pemeriksaan" data-modal-toggle="modal-ringkasan-pemeriksaan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors cursor-pointer">Lihat Riwayat</button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">Rizal Pratama</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2341720084 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">09.20</td>
                                    <td class="px-6 py-4 text-gray-500">Nyeri ulu hati</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors cursor-pointer">Lihat Riwayat</button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="bg-primary-50/20 hover:bg-primary-50/40">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">Dewi Anggraini</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2441720032 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">09.40</td>
                                    <td class="px-6 py-4 text-gray-500">Demam sejak kemarin</td>
                                    <td class="px-6 py-4">
                                        <span class="text-primary-600 font-semibold text-sm">Diperiksa</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="bg-primary-500 text-white hover:bg-primary-600 rounded-base px-3 py-1.5 text-sm font-medium inline-flex items-center gap-1 transition-colors">
                                            <i class="fas fa-arrow-right text-xs"></i> Lanjutkan
                                        </button>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">Ahmad Fauzi</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2441720057 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">10.00</td>
                                    <td class="px-6 py-4 text-gray-500">Sakit kepala</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors cursor-pointer">Mulai Pemeriksaan</button>
                                    </td>
                                </tr>
                                <!-- Row 5 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">Budi Santoso</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIP 198705122019031006 &bull; Pegawai</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">10.20</td>
                                    <td class="px-6 py-4 text-gray-500">Nyeri punggung</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors cursor-pointer">Mulai Pemeriksaan</button>
                                    </td>
                                </tr>
                                <!-- Row 6 -->
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">Nadia Putri</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM 2341720105 &bull; Mahasiswa</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">10.40</td>
                                    <td class="px-6 py-4 text-gray-500">Sakit tenggorokan</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button data-modal-target="modal-pemeriksaan" data-modal-toggle="modal-pemeriksaan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors cursor-pointer">Mulai Pemeriksaan</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                        <span>Menampilkan seluruh 6 pasien terjadwal hari ini</span>
                        <span>Data pasien ilustratif</span>
                    </div>
                </div>

                <!-- Bottom Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Lanjutkan Pemeriksaan -->
                    <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Lanjutkan pemeriksaan</h3>
                            <span class="text-sm font-medium text-primary">Dewi Anggraini &bull; 09.40</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Lengkapi hasil pemeriksaan dan diagnosis, lalu buat resep bila diperlukan.</p>
                        <div class="flex flex-wrap gap-3">
                            <button class="bg-primary text-white hover:bg-primary-700 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-colors">
                                <i class="fas fa-notes-medical"></i> Catat Pemeriksaan & Diagnosis
                            </button>
                            <button class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-colors">
                                <i class="fas fa-prescription-bottle-alt"></i> Buat Resep
                            </button>
                        </div>
                    </div>

                    <!-- Resep Hari Ini -->
                    <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Resep hari ini</h3>
                            <a href="#" class="text-primary hover:text-primary-700 text-sm font-medium flex items-center gap-1">
                                Lihat resep <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                        <ul class="space-y-4 mb-6">
                            <li class="flex items-center gap-3 text-sm text-gray-600">
                                <i class="far fa-paper-plane text-primary-500"></i>
                                <span>1 resep menunggu diproses admin</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-gray-600">
                                <i class="far fa-check-circle text-green-700"></i>
                                <span>1 resep telah diproses admin</span>
                            </li>
                        </ul>
                        <p class="text-xs text-gray-400">Resep digital diteruskan ke admin untuk pelayanan obat.</p>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- modal -->
     <?php require_once __DIR__ . "/components/dashboard/ringkasan-pemeriksaan.php" ?>
     <?php require_once __DIR__ . "/components/dashboard/form-pemeriksaan.php" ?>
     <?php require_once __DIR__ . "/components/dashboard/perubahan-jadwal.php" ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>