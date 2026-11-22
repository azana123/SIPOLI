<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/_load.php';
require_once __DIR__ . '/../../middleware/check-role.php';
check_role('pasien');

$page_title = "Reservasi Saya";
$page_description = "Pantau jadwal periksa mendatang dan riwayat reservasi Anda dengan mudah.";
$back_url = $back_url ?? './../../index.php';
$breadcrumbs = $breadcrumbs ?? [
    ['label' => $page_title]
];

$pasienId = (int) ($_SESSION['profil_id'] ?? 0);
$dataReservasi = QueryPasienReservasi::daftar($pasienId);
$akan_datang = $dataReservasi['akan_datang'];
$riwayat = $dataReservasi['riwayat'];

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
    <title><?= $page_title ?> | SIPOLI</title>
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama: Diperlebar untuk desktop agar header baru terlihat proporsional -->
    <div class="w-full mx-auto min-h-screen bg-page flex flex-col shadow-xl">

        <?php require_once __DIR__ . "/../components/header.php" ?>

        <!-- Main Content -->
        <main class="flex-1 px-5 md:px-20 py-6 overflow-y-auto">

            <!-- Section: Akan Datang -->
            <section class="mb-8">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Akan Datang</h2>
                <div class="flex flex-col gap-5 md:grid md:grid-cols-2 lg:grid-cols-2">
                    <?php if (empty($akan_datang)): ?>
                        <div class="bg-neutral-primary-soft w-full p-6 rounded-base text-sm text-gray-500 text-center md:col-span-2">
                            Belum ada jadwal reservasi yang akan datang.
                        </div>
                    <?php else: ?>
                        <?php foreach ($akan_datang as $item): ?>
                            <!-- Card Item -->
                            <a href="./detail-reservasi.php?id=<?= (int) $item['id'] ?>" class="group bg-white rounded p-4 border-primary shadow-md/50 shadow-primary-400 flex gap-4 relative transition-colors duration-300 hover:bg-primary">
                                <!-- Icon Kiri -->
                                <div class="w-14 h-14 rounded-full bg-primary-50 shrink-0 flex justify-center items-center self-start mt-1 transition-colors duration-300 group-hover:bg-white/20">
                                    <i class="fas fa-calendar-alt text-primary-500 text-2xl transition-colors duration-300 group-hover:text-white"></i>
                                </div>

                                <!-- Konten Tengah & Kanan -->
                                <div class="flex-1 flex flex-col justify-center">
                                    <div class="flex justify-between items-start gap-2">
                                        <h3 class="font-bold text-gray-900 text-[15px] leading-snug transition-colors duration-300 group-hover:text-white"><?= $item['poli'] ?></h3>
                                        <!-- Badge Status -->
                                        <span class="<?= $item['badge_color'] ?> text-2xs font-bold px-2 py-0.5 rounded whitespace-nowrap transition-colors duration-300">
                                            <?= $item['status'] ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1 transition-colors duration-300 group-hover:text-white"><?= $item['dokter'] ?></p>
                                    <p class="text-sm text-gray-500 transition-colors duration-300 group-hover:text-white"><?= $item['jadwal'] ?></p>

                                    <!-- Optional Note (Reschedule) -->
                                    <?php if (isset($item['note'])): ?>
                                        <p class="text-xs text-primary-600 mt-0.5 transition-colors duration-300 group-hover:text-white"><?= $item['note'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Section: Riwayat -->
            <section class="mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Riwayat</h2>
                <div class="flex flex-col gap-3 md:grid md:grid-cols-2 lg:grid-cols-2">
                    <?php if (empty($riwayat)): ?>
                        <div class="bg-neutral-primary-soft w-full p-6 rounded-base text-sm text-gray-500 text-center md:col-span-2">
                            Belum ada riwayat reservasi.
                        </div>
                    <?php else: ?>
                        <?php foreach ($riwayat as $item): ?>
                            <!-- Card Item -->
                            <a href="./detail-reservasi.php?id=<?= (int) $item['id'] ?>" class="group bg-white rounded p-4 border-primary shadow-md/50 shadow-primary-400 flex gap-4 relative transition-colors duration-300 hover:bg-primary">
                                <!-- Icon Kiri -->
                                <div class="w-14 h-14 rounded-full bg-primary-50 shrink-0 flex justify-center items-center self-start mt-1 transition-colors duration-300 group-hover:bg-white/20">
                                    <i class="fas fa-calendar-alt text-primary-500 text-2xl transition-colors duration-300 group-hover:text-white"></i>
                                </div>

                                <!-- Konten Tengah & Kanan -->
                                <div class="flex-1 flex flex-col justify-center">
                                    <div class="flex justify-between items-start gap-2">
                                        <h3 class="font-bold text-gray-900 text-[15px] leading-snug transition-colors duration-300 group-hover:text-white"><?= $item['poli'] ?></h3>
                                        <!-- Badge Status -->
                                        <span class="<?= $item['badge_color'] ?> text-2xs font-bold px-2 py-0.5 rounded whitespace-nowrap transition-colors duration-300">
                                            <?= $item['status'] ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1 transition-colors duration-300 group-hover:text-white"><?= $item['dokter'] ?></p>
                                    <p class="text-sm text-gray-500 transition-colors duration-300 group-hover:text-white"><?= $item['jadwal'] ?></p>

                                    <!-- Optional Note (Reschedule) -->
                                    <?php if (isset($item['note'])): ?>
                                        <p class="text-xs text-primary-600 mt-0.5 transition-colors duration-300 group-hover:text-white"><?= $item['note'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Typo pada tag script bawaan diperbaiki dari <scripts> menjadi <script> -->
    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../../assets/js/flowbite-init.js"></script>
</body>

</html>