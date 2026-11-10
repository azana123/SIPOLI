<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Dashboard Admin";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$totalReservasiHariIni = 0;
$kuotaTotal            = 0;
$kuotaTersedia         = 0;
$totalKonfirmasiH1     = 0;
$totalResepMenunggu    = 0;
$kuotaPerPoli          = [];
$listReservasi         = [];
$errorDb               = false;

try {
    $stats = $pdo->query("
        SELECT
            (
                SELECT COUNT(*)
                FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id
                WHERE j.tanggal = fn_today() AND r.status <> 'dibatalkan'
            ) AS reservasi_hari_ini,
            (
                SELECT COUNT(*)
                FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id
                WHERE j.tanggal = fn_today() + 1 AND r.status = 'menunggu'
            ) AS konfirmasi_h1,
            (
                SELECT COUNT(*)
                FROM permintaan_obat 
                WHERE status = 'menunggu'
            ) AS resep_menunggu
    ")->fetch();

    $totalReservasiHariIni = (int) $stats['reservasi_hari_ini'];
    $totalKonfirmasiH1     = (int) $stats['konfirmasi_h1'];
    $totalResepMenunggu    = (int) $stats['resep_menunggu'];

    $kuotaPerPoli = $pdo->query("
        SELECT po.nama,
               COALESCE(SUM(x.kuota), 0)    AS kuota,
               COALESCE(SUM(x.terpakai), 0) AS terpakai
          FROM poli po
          LEFT JOIN (
                SELECT j.poli_id, j.kuota,
                       (SELECT COUNT(*) FROM reservasi r
                         WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS terpakai
                  FROM jadwal j
                 WHERE j.tanggal = fn_today() AND j.status <> 'tutup'
          ) x ON x.poli_id = po.id
         WHERE po.butuh_reservasi
         GROUP BY po.id, po.nama
         ORDER BY po.nama
    ")->fetchAll();

    foreach ($kuotaPerPoli as $k) {
        $kuotaTotal    += (int) $k['kuota'];
        $kuotaTersedia += max((int) $k['kuota'] - (int) $k['terpakai'], 0);
    }

    $listReservasi = $pdo->query("
        SELECT r.id, r.no_reservasi, r.status, r.reschedule_count,
               p.nama   AS nama_pasien,
               pol.nama AS nama_poli,
               j.jam_mulai, j.jam_selesai
          FROM reservasi r
          JOIN pasien p   ON p.id   = r.pasien_id
          JOIN jadwal j   ON j.id   = r.jadwal_id
          JOIN poli   pol ON pol.id = j.poli_id
         WHERE j.tanggal = fn_today() AND r.status <> 'dibatalkan'
         ORDER BY j.jam_mulai, r.created_at
         LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $errorDb = true;
    error_log('[dashboard-admin] ' . $e->getMessage());
}

// sesuaikan nanti warnanya
$statusBadge = [
    'menunggu'     => ['Menunggu',     'bg-danger-soft text-fg-danger-strong'],
    'dikonfirmasi' => ['Dikonfirmasi', 'bg-success-soft text-fg-success-strong'],
    'hadir'        => ['Hadir',        'bg-brand-softer text-fg-brand-strong'],
    'selesai'      => ['Selesai',      'bg-gray-200 text-heading'],
    'tidak_hadir'  => ['Tidak hadir',  'bg-gray-200 text-heading'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | <?= e($app_name) ?></title>
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

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">

                <?php if ($errorDb): ?>
                    <div class="mb-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        Data dashboard gagal dimuat. Angka di bawah belum tentu benar. Periksa log server.
                    </div>
                <?php endif; ?>

                <!-- Counter -->
                <div class="counter grid grid-cols-2 md:grid-cols-4 gap-3.5">
                    <div class="bg-neutral-primary-soft flex flex-col p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Reservasi Hari ini</p>
                        <h4 class="text-4xl font-bold"><?= e($totalReservasiHariIni) ?></h4>
                    </div>
                    <!-- Kuota Tersedia -->
                    <div class="bg-neutral-primary-soft block p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-default rounded-base shadow-xs">
                        <p class="text-gray-500 font-medium">Kuota Tersedia</p>
                        <h4 class="text-4xl font-bold"><?= e($kuotaTersedia) ?><span class="text-2xl text-gray-400">/<?= e($kuotaTotal) ?></span></h4>
                    </div>
                    <!-- Konfirmasi H-1 -->
                    <div class="bg-yellow-100 block p-4 md:p-6 border whitespace-normal text-center md:text-left border-yellow-400 rounded-base shadow-xs text-yellow-900">
                        <p class="font-medium">Konfirmasi H-1 Menunggu</p>
                        <h4 class="text-4xl font-bold"><?= e($totalKonfirmasiH1) ?></h4>
                    </div>
                    <!-- Resep Menunggu -->
                    <div class="bg-blue-200 block p-4 md:p-6 border whitespace-nowrap text-center md:text-left border-blue-400 rounded-base shadow-xs text-blue-900">
                        <p class="font-medium">Resep Menunggu</p>
                        <h4 class="text-4xl font-bold"><?= e($totalResepMenunggu) ?></h4>
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
                                    <th scope="row" class="py-4 font-medium text-primary-600 whitespace-nowrap">
                                        <?= e($totalKonfirmasiH1) ?> Pasien konseling/reservasi belum dikonfirmasi
                                    </th>
                                    <td class="px-6 text-end">
                                        <a href="./konfirmasi-h1.php">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
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
                                    <?php if (empty($listReservasi)): ?>
                                        <tr class="bg-neutral-primary border-b border-primary-400-05">
                                            <td colspan="4" class="py-4 text-center text-gray-500">Belum ada data reservasi untuk hari ini.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listReservasi as $row): ?>
                                            <?php
                                            [$label, $badgeClass] = $statusBadge[$row['status']]
                                                ?? [ucfirst((string) $row['status']), 'bg-gray-200 text-heading'];
                                            ?>
                                            <tr class="bg-neutral-primary border-b border-primary-400-05">
                                                <th scope="row" class="py-4 font-medium text-heading whitespace-nowrap">
                                                    <?= e($row['nama_pasien']) ?>
                                                </th>
                                                <td class="px-6 py-4">
                                                    <?= e($row['nama_poli']) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?= e(substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5)) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded <?= e($badgeClass) ?>">
                                                        <?= e($label) ?>
                                                    </span>
                                                    <?php if ((int) $row['reschedule_count'] > 0): ?>
                                                        <span class="ml-1 inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-brand-softer text-fg-brand-strong">
                                                            Reschedule
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kuota -->
                <div class="my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
                    <h4 class="text-lg font-bold mb-4">Kuota Hari ini</h4>
                    <div class="chart-bar">
                        <?php if (empty($kuotaPerPoli)): ?>
                            <p class="text-sm text-gray-500">Tidak ada jadwal reservasi hari ini.</p>
                        <?php else: ?>
                            <?php foreach ($kuotaPerPoli as $k): ?>
                                <?php
                                $kuota    = (int) $k['kuota'];
                                $terpakai = (int) $k['terpakai'];
                                $persen   = $kuota > 0 ? min(($terpakai / $kuota) * 100, 100) : 0;
                                ?>
                                <div class="mb-3">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-base font-medium text-gray-700"><?= e($k['nama']) ?></span>
                                        <span class="text-sm font-medium text-gray-700"><?= e($terpakai) ?>/<?= e($kuota) ?></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-warning h-2.5 rounded-full" style="width: <?= e(round($persen)) ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>

        </div>
    </div>

    <?php require_once __DIR__ . '/components/form-pasien-offline.php'; ?>

    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>