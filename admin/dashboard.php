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

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

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

    $totalReservasiHariIni = (int) ($stats['reservasi_hari_ini'] ?? 0);
    $totalKonfirmasiH1     = (int) ($stats['konfirmasi_h1'] ?? 0);
    $totalResepMenunggu    = (int) ($stats['resep_menunggu'] ?? 0);

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

$statusBadge = [
    'menunggu'     => ['Menunggu',     'bg-yellow-100 text-yellow-900'],
    'dikonfirmasi' => ['Dikonfirmasi', 'bg-success-soft text-fg-success-strong'],
    'hadir'        => ['Hadir',        'bg-brand-softer text-fg-brand-strong'],
    'selesai'      => ['Selesai',      'bg-emerald-50 text-emerald-700 border border-emerald-200'],
    'tidak_hadir'  => ['Tidak hadir',  'bg-red-100 text-red-800'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | <?= e($app_name) ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-page text-gray-800 antialiased">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6 md:p-8">

                <?php if ($flashSuccess): ?>
                    <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 border border-emerald-300 rounded-base" role="alert">
                        <?= e($flashSuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        <?= e($flashError) ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorDb): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        Data beranda belum dapat dimuat saat ini. Silakan muat ulang halaman beberapa saat lagi.
                    </div>
                <?php endif; ?>

                <!-- Counter Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Reservasi Hari Ini -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Reservasi hari ini</p>
                            <i class="far fa-calendar-check text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalReservasiHariIni) ?></h3>
                        <p class="text-xs text-gray-500">Pasien terjadwal hari ini</p>
                    </div>

                    <!-- Kuota Tersedia -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Kuota tersedia</p>
                            <i class="fas fa-user-friends text-primary-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1">
                            <?= e($kuotaTersedia) ?><span class="text-xl text-gray-400 font-normal">/<?= e($kuotaTotal) ?></span>
                        </h3>
                        <p class="text-xs text-gray-500">Sisa kuota poli reservasi</p>
                    </div>

                    <!-- Konfirmasi H-1 -->
                    <div class="bg-yellow-50/80 border border-yellow-300 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-yellow-900">Konfirmasi H-1 menunggu</p>
                            <i class="far fa-clock text-yellow-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-yellow-800 mb-1"><?= e($totalKonfirmasiH1) ?></h3>
                        <p class="text-xs text-yellow-800">Perlu dihubungi untuk esok hari</p>
                    </div>

                    <!-- Resep Menunggu -->
                    <div class="bg-primary-50/60 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-primary-900">Resep menunggu</p>
                            <i class="fas fa-prescription-bottle-alt text-primary-600"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary-700 mb-1"><?= e($totalResepMenunggu) ?></h3>
                        <p class="text-xs text-primary-800">Menunggu penyiapan obat</p>
                    </div>
                </div>

                <!-- Perlu Tindakan -->
                <div class="mb-6 bg-white border border-gray-200 rounded-base p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Perlu tindakan segera</h3>
                    <div class="divide-y divide-gray-100 text-sm">
                        <a href="./konfirmasi.php" class="py-3.5 flex items-center justify-between hover:bg-gray-50 px-2 rounded transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-base bg-yellow-100 text-yellow-800 flex items-center justify-center shrink-0">
                                    <i class="far fa-bell text-xs"></i>
                                </span>
                                <span class="font-medium text-gray-800">
                                    <?= e($totalKonfirmasiH1) ?> pasien reservasi esok hari belum dikonfirmasi kehadirannya
                                </span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                        <a href="./resep.php" class="py-3.5 flex items-center justify-between hover:bg-gray-50 px-2 rounded transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-base bg-primary-50 text-primary-700 flex items-center justify-center shrink-0">
                                    <i class="fas fa-pills text-xs"></i>
                                </span>
                                <span class="font-medium text-gray-800">
                                    <?= e($totalResepMenunggu) ?> resep obat dari dokter menunggu disiapkan
                                </span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Daftar Reservasi Hari Ini -->
                <div class="mb-6 bg-white border border-gray-200 rounded-base shadow-sm">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Reservasi hari ini</h3>
                            <p class="text-xs text-gray-500 mt-0.5">5 jadwal kunjungan terdekat hari ini</p>
                        </div>
                        <a href="./reservasi.php" class="text-primary hover:text-primary-700 text-sm font-medium flex items-center gap-1">
                            Lihat semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/60 border-b border-gray-200 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Pasien</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Poli</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Jam Praktik</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($listReservasi)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data reservasi untuk hari ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($listReservasi as $row): ?>
                                        <?php
                                        [$label, $badgeClass] = $statusBadge[$row['status']]
                                            ?? [ucfirst((string) $row['status']), 'bg-gray-200 text-heading'];
                                        ?>
                                        <tr class="hover:bg-gray-50/60">
                                            <th scope="row" class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                                <?= e($row['nama_pasien']) ?>
                                                <div class="text-xs font-normal text-gray-400 mt-0.5"><?= e($row['no_reservasi']) ?></div>
                                            </th>
                                            <td class="px-6 py-4"><?= e($row['nama_poli']) ?></td>
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                <?= e(str_replace(':', '.', substr($row['jam_mulai'], 0, 5)) . ' – ' . str_replace(':', '.', substr($row['jam_selesai'], 0, 5))) ?> WIB
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base <?= e($badgeClass) ?>">
                                                    <?= e($label) ?>
                                                </span>
                                                <?php if ((int) $row['reschedule_count'] > 0): ?>
                                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-amber-50 text-amber-700">
                                                        Jadwal Ulang
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

                <!-- Kuota Hari Ini -->
                <div class="bg-white border border-gray-200 rounded-base p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Keterisian kuota hari ini</h3>
                    <div class="space-y-4">
                        <?php if (empty($kuotaPerPoli)): ?>
                            <p class="text-sm text-gray-500">Tidak ada jadwal reservasi yang dibuka hari ini.</p>
                        <?php else: ?>
                            <?php foreach ($kuotaPerPoli as $k): ?>
                                <?php
                                $kuota    = (int) $k['kuota'];
                                $terpakai = (int) $k['terpakai'];
                                $persen   = $kuota > 0 ? min(($terpakai / $kuota) * 100, 100) : 0;
                                ?>
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <span class="text-sm font-semibold text-gray-800"><?= e($k['nama']) ?></span>
                                        <span class="text-xs font-semibold text-gray-600"><?= e($terpakai) ?> dari <?= e($kuota) ?> kuota terisi</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: <?= e(round($persen)) ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </main>

        </div>
    </div>

    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>