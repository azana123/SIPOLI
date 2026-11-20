<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/_load.php';

$page_title = "Jadwal & Kuota";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

function formatTanggalIndo(string $dateStr, bool $withDay = false): string
{
    $hari = [
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
        5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
    ];
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $ts = strtotime($dateStr);
    if (!$ts) return $dateStr;
    $tgl = (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    return $withDay ? $hari[(int) date('N', $ts)] . ', ' . $tgl : $tgl;
}

function formatTanggalSingkat(string $dateStr): string
{
    $bulanSingkat = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $ts = strtotime($dateStr);
    if (!$ts) return $dateStr;
    return date('d', $ts) . ' ' . $bulanSingkat[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

try {
    $todayDb = QueryAdminJadwal::todayDb() ?: date('Y-m-d');
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}


$filterTanggal = trim($_GET['tanggal'] ?? $todayDb);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterTanggal)) {
    $filterTanggal = $todayDb;
}
$filterPoli   = trim($_GET['poli_id'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$detailId     = isset($_GET['detail_id']) && ctype_digit((string) $_GET['detail_id']) ? (int) $_GET['detail_id'] : null;
$editId       = isset($_GET['edit_id']) && ctype_digit((string) $_GET['edit_id']) ? (int) $_GET['edit_id'] : null;

$listPoli             = [];
$listDokter           = [];
$jadwalRows           = [];
$detailJadwal         = null;
$detailPasienList     = [];
$editJadwal           = null;
$errorDb              = false;

$statTotalJadwal      = 0;
$statDipublikasi      = 0;
$statKuotaTerisi      = 0;
$statTotalKuota       = 0;
$statKuotaSisa        = 0;
$statJadwalPenuh      = 0;
$infoJadwalPenuhLabel = 'Belum ada jadwal penuh';

try {
    $listPoli = QueryAdminJadwal::listPoli();

    $listDokter = QueryAdminJadwal::listDokter();

    $stmtStats = QueryAdminJadwal::stats([':tanggal' => $filterTanggal]);
    $allDaySchedules = $stmtStats->fetchAll();

    $statTotalJadwal = count($allDaySchedules);
    $penuhDetails    = [];

    foreach ($allDaySchedules as $s) {
        if ($s['dipublikasi']) {
            $statDipublikasi++;
        }
        if ($s['kuota'] !== null) {
            $kuotaMax = (int) $s['kuota'];
            $terisi   = (int) $s['rsv_aktif'];
            $statTotalKuota  += $kuotaMax;
            $statKuotaTerisi += $terisi;
            $statKuotaSisa   += max(0, $kuotaMax - $terisi);
        }
        if ($s['status'] === 'penuh') {
            $statJadwalPenuh++;
            $penuhDetails[] = $s['nama_poli'] . ' · ' . (int) $s['rsv_aktif'] . ' dari ' . (int) $s['kuota'] . ' pasien';
        }
    }
    if (!empty($penuhDetails)) {
        $infoJadwalPenuhLabel = implode(', ', array_slice($penuhDetails, 0, 2));
    }

    $jadwalRows = QueryDaftar::jadwalAdmin([
        'tanggal' => $filterTanggal, 'poli_id' => $filterPoli, 'status' => $filterStatus,
    ]);

    if ($detailId !== null) {
        $stmtDetail = QueryAdminJadwal::detail([':id' => $detailId]);
        $detailJadwal = $stmtDetail->fetch();

        if ($detailJadwal) {
            if ($detailJadwal['butuh_reservasi']) {
                $stmtPasien = QueryAdminJadwal::pasien([':jadwal_id' => $detailId]);
                $detailPasienList = $stmtPasien->fetchAll();
            } else {
                $stmtPasien = QueryAdminJadwal::pasien2([
                    ':poli_id' => $detailJadwal['poli_id'],
                    ':tanggal' => $detailJadwal['tanggal'],
                ]);
                $detailPasienList = $stmtPasien->fetchAll();
            }
        }
    }

    if ($editId !== null) {
        $stmtEdit = QueryAdminJadwal::edit([':id' => $editId]);
        $editJadwal = $stmtEdit->fetch();
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[jadwal-admin-read] ' . $e->getMessage());
}

$poliUnikTabel  = count(array_unique(array_column($jadwalRows, 'poli_id')));
$tabelTerisiSum = 0;
$tabelKuotaSum  = 0;
foreach ($jadwalRows as $r) {
    if ($r['kuota'] !== null && $r['status'] !== 'tutup') {
        $tabelTerisiSum += (int) $r['terisi_reservasi'];
        $tabelKuotaSum  += (int) $r['kuota'];
    }
}
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
                        Data jadwal belum dapat dimuat saat ini. Silakan muat ulang halaman.
                    </div>
                <?php endif; ?>

                <!-- Page Header & Action -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ringkasan jadwal praktik &amp; kuota layanan &middot; <?= e(formatTanggalIndo($filterTanggal, true)) ?></p>
                    </div>
                    <button type="button" data-modal-target="modal-tambah-jadwal" data-modal-toggle="modal-tambah-jadwal" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-base hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 cursor-pointer transition-colors">
                        <i class="fas fa-plus mr-2"></i> Tambah Jadwal
                    </button>
                </div>

                <!-- Stats Counter -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Jadwal aktif</p>
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($statTotalJadwal) ?></h3>
                        <p class="text-xs text-gray-500"><?= e($statDipublikasi) ?> jadwal dipublikasikan</p>
                    </div>
                    <div class="bg-primary-50/50 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Kuota terisi</p>
                            <i class="fas fa-user-friends text-primary-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary mb-1"><?= e($statKuotaTerisi) ?><span class="text-xl text-gray-400 font-normal">/<?= e($statTotalKuota) ?></span></h3>
                        <p class="text-xs text-gray-500">Total gabungan poli reservasi</p>
                    </div>
                    <div class="bg-emerald-50/60 border border-emerald-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-emerald-900">Kuota tersedia</p>
                            <i class="fas fa-check-circle text-emerald-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-emerald-800 mb-1"><?= e($statKuotaSisa) ?></h3>
                        <p class="text-xs text-emerald-800">Masih dapat direservasi</p>
                    </div>
                    <div class="<?= $statJadwalPenuh > 0 ? 'bg-yellow-50/80 border-yellow-300' : 'bg-white border-gray-200' ?> border rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm <?= $statJadwalPenuh > 0 ? 'text-yellow-900' : 'text-gray-600' ?> font-semibold">Jadwal penuh</p>
                            <i class="fas fa-exclamation-circle <?= $statJadwalPenuh > 0 ? 'text-yellow-700' : 'text-gray-400' ?>"></i>
                        </div>
                        <h3 class="text-3xl font-bold <?= $statJadwalPenuh > 0 ? 'text-yellow-800' : 'text-gray-900' ?> mb-1"><?= e($statJadwalPenuh) ?></h3>
                        <p class="text-xs <?= $statJadwalPenuh > 0 ? 'text-yellow-800' : 'text-gray-500' ?>"><?= e($infoJadwalPenuhLabel) ?></p>
                    </div>
                </div>

                <!-- Main Data Card -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6 mb-6">

                    <!-- Card Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar jadwal praktik</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Pengaturan sesi pelayanan dokter dan psikolog</p>
                        </div>
                        <span class="text-xs font-semibold text-primary-700 bg-primary-50 px-3 py-1 rounded-base">
                            <?= count($jadwalRows) ?> jadwal &middot; <?= $poliUnikTabel ?> poli
                        </span>
                    </div>

                    <!-- Filters (Method GET) -->
                    <form method="GET" action="jadwal.php" class="grid grid-cols-1 md:grid-cols-4 gap-3 my-4 items-end">
                        <div>
                            <label for="filter-tanggal" class="block mb-1 text-xs font-medium text-gray-500">Tanggal</label>
                            <input type="date" id="filter-tanggal" name="tanggal" value="<?= e($filterTanggal) ?>" onchange="this.form.submit()" class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-base focus:ring-primary-500 focus:border-primary-500 bg-white" />
                        </div>
                        <div>
                            <label for="filter-poli" class="block mb-1 text-xs font-medium text-gray-500">Poli</label>
                            <select id="filter-poli" name="poli_id" onchange="this.form.submit()" class="block w-full p-2.5 border border-gray-300 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option value="">Semua poli</option>
                                <?php foreach ($listPoli as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" <?= $filterPoli === (string) $p['id'] ? 'selected' : '' ?>>
                                        <?= e($p['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="filter-status" class="block mb-1 text-xs font-medium text-gray-500">Status</label>
                            <select id="filter-status" name="status" onchange="this.form.submit()" class="block w-full p-2.5 border border-gray-300 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua jadwal aktif</option>
                                <option value="buka" <?= $filterStatus === 'buka' ? 'selected' : '' ?>>Tersedia (Buka)</option>
                                <option value="penuh" <?= $filterStatus === 'penuh' ? 'selected' : '' ?>>Penuh</option>
                                <option value="tutup" <?= $filterStatus === 'tutup' ? 'selected' : '' ?>>Ditutup</option>
                                <option value="semua" <?= $filterStatus === 'semua' ? 'selected' : '' ?>>Semua (Termasuk Tutup)</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="jadwal.php?tanggal=<?= urlencode($todayDb) ?>" class="px-3.5 py-2.5 text-xs font-semibold text-primary-700 bg-primary-50 rounded-base border border-primary-100 hover:bg-primary-100">
                                Hari ini
                            </a>
                            <a href="jadwal.php" class="text-xs font-semibold text-primary-600 hover:underline py-2">
                                Reset filter
                            </a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="relative overflow-x-auto border-t border-gray-100 pt-2">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/60 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Dokter / Psikolog</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Poli</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Tanggal &amp; Waktu</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Kuota</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Status</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($jadwalRows)): ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada jadwal praktik pada tanggal dan filter yang dipilih.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($jadwalRows as $row): ?>
                                        <?php
                                        $jamMulaiFmt   = substr($row['jam_mulai'], 0, 5);
                                        $jamSelesaiFmt = substr($row['jam_selesai'], 0, 5);
                                        $kuotaMax      = $row['kuota'] !== null ? (int) $row['kuota'] : null;
                                        $terisiRsv     = (int) $row['terisi_reservasi'];
                                        $terisiAntrean = (int) $row['terisi_antrean'];
                                        $persen        = ($kuotaMax && $kuotaMax > 0) ? min(100, round(($terisiRsv / $kuotaMax) * 100)) : 0;
                                        $sisaKuota     = ($kuotaMax !== null) ? max(0, $kuotaMax - $terisiRsv) : null;
                                        $isTutup       = ($row['status'] === 'tutup');

                                        $baseQuery = http_build_query([
                                            'tanggal' => $filterTanggal,
                                            'poli_id' => $filterPoli,
                                            'status'  => $filterStatus,
                                        ]);
                                        ?>
                                        <tr class="<?= $isTutup ? 'bg-gray-50 opacity-60' : 'bg-white hover:bg-gray-50/60' ?> transition-colors">
                                            <td class="px-4 py-4">
                                                <?php if (!empty($row['nama_dokter'])): ?>
                                                    <div class="font-bold text-gray-900"><?= e($row['nama_dokter']) ?></div>
                                                    <div class="text-xs text-gray-500 capitalize"><?= e($row['profesi']) ?></div>
                                                <?php else: ?>
                                                    <div class="font-medium text-amber-700">Seluruh Tenaga Layanan</div>
                                                    <div class="text-xs text-gray-400">Sesuai giliran jaga</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 font-semibold text-gray-900"><?= e($row['nama_poli']) ?></td>
                                            <td class="px-4 py-4">
                                                <div class="font-medium text-gray-900"><?= e(formatTanggalSingkat($row['tanggal'])) ?></div>
                                                <div class="text-xs text-gray-500"><?= e(str_replace(':', '.', $jamMulaiFmt)) ?>–<?= e(str_replace(':', '.', $jamSelesaiFmt)) ?> WIB</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <?php if ($kuotaMax !== null): ?>
                                                    <div class="font-bold <?= $row['status'] === 'penuh' ? 'text-red-600' : 'text-gray-900' ?> text-sm mb-1">
                                                        <?= $terisiRsv ?>/<?= $kuotaMax ?> terisi
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1 max-w-[120px]">
                                                        <div class="<?= $row['status'] === 'penuh' ? 'bg-red-500' : 'bg-primary-500' ?> h-1.5 rounded-full" style="width: <?= $persen ?>%"></div>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= $isTutup ? 'Jadwal ditutup' : ($sisaKuota > 0 ? $sisaKuota . ' kuota tersedia' : 'Kuota penuh') ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="font-bold text-gray-900 text-sm mb-1">Tanpa batas</div>
                                                    <div class="text-xs text-gray-500">Kunjungan langsung &middot; <?= $terisiAntrean ?> pasien</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4">
                                                <?php if ($row['status'] === 'buka'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-emerald-700 text-xs font-semibold rounded-base bg-emerald-50 border border-emerald-200">Tersedia</span>
                                                <?php elseif ($row['status'] === 'penuh'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-red-800 text-xs font-semibold rounded-base bg-red-100">Penuh</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-gray-700 text-xs font-semibold rounded-base bg-gray-200">Tutup</span>
                                                <?php endif; ?>
                                                <div class="text-xs text-gray-500">
                                                    <?= $row['dipublikasi'] ? 'Dipublikasikan' : 'Belum dipublikasikan' ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap text-right">
                                                <div class="flex justify-end items-center gap-3">
                                                    <a href="jadwal.php?<?= $baseQuery ?>&detail_id=<?= (int) $row['id'] ?>" class="text-xs font-semibold hover:underline cursor-pointer">Lihat</a>
                                                    <a href="jadwal.php?<?= $baseQuery ?>&edit_id=<?= (int) $row['id'] ?>" class="w-8 h-8 rounded-base bg-primary-50 text-primary-600 hover:bg-primary hover:text-white flex justify-center items-center transition-colors" title="Ubah Jadwal">
                                                        <i class="fas fa-pen text-xs"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer -->
                    <div class="flex flex-col md:flex-row justify-between items-center text-xs text-gray-500 mt-4 pt-4 border-t border-gray-100 gap-4">
                        <span>Menampilkan <?= count($jadwalRows) ?> jadwal</span>
                        <span class="font-semibold text-primary-700">Total kuota reservasi aktif: <?= $tabelTerisiSum ?> terisi dari <?= $tabelKuotaSum ?> pasien</span>
                    </div>

                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-base p-5 border border-gray-200 flex items-start gap-3.5 shadow-sm">
                        <div class="w-9 h-9 rounded-base bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Aturan kuota poli</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Poli Gigi dan Konseling menggunakan sistem reservasi dengan batas kuota harian. Poli Umum melayani kunjungan langsung tanpa batas kuota.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-base p-5 border border-gray-200 flex items-start gap-3.5 shadow-sm">
                        <div class="w-9 h-9 rounded-base bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Penutupan jadwal</h5>
                            <p class="text-xs text-gray-500 leading-relaxed">Menutup jadwal akan menariknya dari daftar jadwal publik, membatalkan reservasi aktif secara otomatis, dan mengirimkan pemberitahuan kepada pasien.</p>
                        </div>
                    </div>
                </div>

            </main>

        </div>
    </div>

    <?php require_once __DIR__ . '/components/jadwal/detail-jadwal.php'; ?>
    <?php require_once __DIR__ . '/components/jadwal/form-tambah-jadwal.php'; ?>
    <?php require_once __DIR__ . '/components/jadwal/form-edit-jadwal.php'; ?>

    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../assets/js/flowbite-init.js"></script>
</body>

</html>