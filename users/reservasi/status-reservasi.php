<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/_load.php';
require_once __DIR__ . '/../../middleware/check-role.php';
check_role('pasien');

$reservasiId = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$pasienId    = (int) ($_SESSION['profil_id'] ?? 0);
$detail = null;
try {
    $detail = $reservasiId > 0 ? QueryPasienReservasi::detail($reservasiId, $pasienId) : null;
} catch (ApiException $e) {
    $detail = null;
}

$poliName     = $detail['poli'] ?? 'Poli Gigi';
$doctorName   = $detail['dokter'] ?? 'Dokter akan ditentukan';
$selectedDate = $detail['tanggal_panjang'] ?? '-';
$selectedTime = $detail['jam'] ?? '-';
$statusLabel  = $detail['status_label'] ?? 'Menunggu';

$page_title       = $page_title ?? "Status Reservasi";
$page_description = "Rincian status pengajuan reservasi pemeriksaan Anda di poliklinik.";
$back_url         = $back_url ?? './detail-reservasi.php?id=' . $reservasiId;
$breadcrumbs      = $breadcrumbs ?? [
    ['label' => 'Reservasi Saya', 'url' => './reservasi.php'],
    ['label' => $page_title]
];

$apakahLogin = isset($_SESSION['user_id'], $_SESSION['role']);
$namaPasien  = null;

if ($apakahLogin) {
    require_once __DIR__ . '/../../middleware/check-role.php';
    check_role(['admin', 'dokter', 'pasien']);

    $peran = (string) ($_SESSION['role'] ?? '');
    if ($peran !== 'pasien') {
        redirect_by_role($peran);
    }

    try {
        $stmtNama = $pdo->prepare('SELECT nama FROM pasien WHERE user_id = :uid AND is_active = TRUE LIMIT 1');
        $stmtNama->execute([':uid' => (int) $_SESSION['user_id']]);
        $namaPasien = (string) ($stmtNama->fetchColumn() ?: ($_SESSION['username'] ?? 'Pasien'));
    } catch (PDOException $e) {
        $namaPasien = (string) ($_SESSION['username'] ?? 'Pasien');
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | SIPOLI</title>
    <!-- Asumsi style.css berisi layer base Tailwind v4 dan Flowbite -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama: Diperlebar untuk desktop agar header baru terlihat proporsional -->
    <div class="w-full mx-auto min-h-screen bg-page flex flex-col shadow-xl">

        <!-- Komponen Header Desktop & Mobile -->
        <?php require_once __DIR__ . "./../components/header.php" ?>

        <!-- Main Content -->
        <main class="flex-1 px-5 py-6 md:py-10 overflow-y-auto flex flex-col justify-center">
            <!-- Wrapper Konten terpusat agar tetap proporsional di layar desktop -->
            <div class="w-full max-w-lg mx-auto">

                <!-- Ikon Sukses -->
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                        <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center text-white text-3xl shadow-md">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Teks Konfirmasi -->
                <div class="text-center mb-8">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Reservasi berhasil</h1>
                    <p class="text-sm text-gray-500 px-4">Reservasimu sudah tercatat dan menunggu konfirmasi dari poliklinik.</p>
                </div>

                <!-- Card Detail Reservasi -->
                <div class="bg-white rounded p-5 md:p-6 shadow-sm border border-gray-100">
                    <!-- Row: Poli -->
                    <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-500">Poli</span>
                        <span class="text-sm font-bold text-gray-900"><?= e($poliName) ?></span>
                    </div>

                    <!-- Row: Dokter -->
                    <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-500">Dokter</span>
                        <span class="text-sm font-bold text-gray-900"><?= e($doctorName) ?></span>
                    </div>

                    <!-- Row: Tanggal -->
                    <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-500">Tanggal</span>
                        <span class="text-sm font-bold text-gray-900"><?= e($selectedDate) ?></span>
                    </div>

                    <!-- Row: Waktu -->
                    <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-500">Waktu</span>
                        <span class="text-sm font-bold text-gray-900"><?= e($selectedTime) ?></span>
                    </div>

                    <!-- Row: Status -->
                    <div class="flex justify-between items-center pt-3.5 pb-1">
                        <span class="text-sm font-semibold text-gray-500">Status</span>
                        <span class="text-xs font-bold text-[#b45309] bg-[#fef3c7] px-3 py-1 rounded"><?= e($statusLabel) ?></span>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mt-6 pb-4 flex flex-col gap-3">
                    <!-- Primary Button -->
                    <a href="./detail-reservasi.php?id=<?= $reservasiId ?>" class="w-full py-2.5 bg-primary hover:bg-primary-700 text-white text-center font-semibold rounded-full shadow-sm transition-colors focus:ring-4 focus:ring-primary-300 block">
                        Lihat reservasi saya
                    </a>

                    <!-- Secondary/Outline Button -->
                    <a href="./../../index.php" class="w-full py-2.5 bg-transparent border-2 border-primary-600 text-primary-600 hover:bg-primary-50 text-center font-semibold rounded-full transition-colors focus:ring-4 focus:ring-primary-100 block">
                        Kembali ke beranda
                    </a>
                </div>

            </div>
        </main>
    </div>

    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../../assets/js/flowbite-init.js"></script>
</body>
</html>