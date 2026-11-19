<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/_load.php';
require_once __DIR__ . '/../middleware/check-role.php';
check_role('pasien');

$page_title = "Notifikasi";
$userId = (int) ($_SESSION['user_id'] ?? 0);
$notifikasi = QueryPasienNotifikasi::daftar($userId);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | SIPOLI</title>
    <!-- Pastikan file output CSS Tailwind v4 diload di sini -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama Mobile First -->
    <div class="w-full md:max-w-md mx-auto min-h-screen bg-page flex flex-col shadow-xl relative">

        <!-- App Bar / Header -->
        <header class="bg-primary-500 text-white px-5 py-4 flex items-center sticky top-0 z-20 shadow-sm">
            <a href="./../index.php" class="mr-4 hover:bg-primary-600 p-1 rounded transition-colors">
                <i class="fas fa-chevron-left text-lg"></i>
            </a>
            <h1 class="text-lg font-semibold tracking-wide"><?= $page_title ?></h1>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-5 py-6 overflow-y-auto">

            <?php if (!$notifikasi): ?>
                <div class="bg-white border border-gray-200 rounded p-6 text-center text-sm text-gray-500">
                    Belum ada notifikasi.
                </div>
            <?php else: ?>
                <?php foreach ($notifikasi as $n): ?>
                    <div class="bg-primary-50 border border-primary-200 rounded p-4 flex gap-3 relative mb-3">
                        <!-- Icon Kiri -->
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-700 shrink-0 flex justify-center items-center mt-0.5">
                            <i class="fas fa-history text-lg"></i>
                        </div>

                        <!-- Konten Tengah & Kanan -->
                        <div class="flex-1 flex flex-col">
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="font-bold text-gray-900 text-[15px] leading-snug">
                                    <?= e($n['judul']) ?>
                                </h3>
                                <?php if ($n['belum_dibaca']): ?>
                                    <span class="w-2.5 h-2.5 bg-primary-600 rounded-full mt-1.5 shrink-0"></span>
                                <?php endif; ?>
                            </div>

                            <p class="text-[13px] text-gray-600 mt-1 leading-relaxed">
                                <?= e($n['pesan']) ?>
                            </p>

                            <div class="flex justify-between items-center mt-3 text-xs">
                                <span class="text-gray-500"><?= e($n['waktu']) ?></span>

                                <?php if ($n['butuh_aksi'] && $n['aksi_selesai_at'] === null): ?>
                                    <a href="<?= $n['ref_tipe'] === 'reservasi' ? e('./reservasi/detail-reservasi.php?id=' . (int) $n['ref_id']) : '#' ?>" class="font-semibold text-primary-600 hover:underline flex items-center gap-1">
                                        Lihat detail
                                        <i class="fas fa-chevron-right text-2xs"></i>
                                    </a>
                                <?php elseif ($n['belum_dibaca']): ?>
                                    <button type="button" data-api-click data-api-endpoint="/api/pasien/notifikasi.php" data-api-payload='{"notifikasi_id":<?= (int) $n['id'] ?>}' class="font-semibold text-primary-600 hover:underline">
                                        Tandai dibaca
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </main>
    </div>

    <?php require __DIR__ . '/components/api-script.php'; ?>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>