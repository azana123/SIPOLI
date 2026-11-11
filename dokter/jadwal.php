<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('dokter');
require_once __DIR__ . '/../config/database.php';

$page_title = "Jadwal Saya";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$dokterId     = (int) ($_SESSION['profil_id'] ?? 0);
$dokterProfil = null;

try {
    $todayDb = $pdo->query("SELECT fn_today()::text")->fetchColumn() ?: date('Y-m-d');
    if ($dokterId > 0) {
        $stmtDok = $pdo->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id,
                   p.nama AS nama_poli, p.butuh_reservasi, p.kuota_harian
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmtDok->execute([':id' => $dokterId]);
        $dokterProfil = $stmtDok->fetch();
    }
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}

// Jalankan modal pengajuan perubahan jadwal terlebih dahulu
ob_start();
require_once __DIR__ . "/components/dashboard/perubahan-jadwal.php";
$htmlModalPerubahanJadwal = ob_get_clean();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$namaDokter     = $dokterProfil['nama'] ?? 'Dokter';
$namaPoliDokter = $dokterProfil['nama_poli'] ?? 'Poli';
$butuhReservasi = !empty($dokterProfil['butuh_reservasi']);


$weekOffset   = isset($_GET['week']) && is_numeric($_GET['week']) ? (int) $_GET['week'] : 0;
$filterStatus = trim($_GET['status'] ?? '');
$tsToday   = strtotime($todayDb);
$hariKe    = (int) date('N', $tsToday);
$seninBase = strtotime("-" . ($hariKe - 1) . " days", $tsToday);
$tsStart   = strtotime(($weekOffset >= 0 ? "+{$weekOffset}" : "{$weekOffset}") . " weeks", $seninBase);
$tsEnd     = strtotime("+6 days", $tsStart);

$tglMulaiPeriode   = date('Y-m-d', $tsStart);
$tglSelesaiPeriode = date('Y-m-d', $tsEnd);

$hariMap  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
$bulanMap = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
$bulanFullMap = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$labelPeriode = date('j', $tsStart) . '–' . date('j', $tsEnd) . ' ' . $bulanFullMap[(int) date('n', $tsEnd)] . ' ' . date('Y', $tsEnd);

$jadwalHariIni    = null;
$jadwalBerikutnya = null;
$jadwalMingguIni  = [];
$statSesi         = 0;
$statTotalPasien  = 0;
$statTotalKuota   = 0;
$statTotalJam     = 0;
$errorDb          = false;

try {
    // Jadwal hari ini
    $stmtToday = $pdo->prepare("
        SELECT id, tanggal, jam_mulai, jam_selesai, kuota, status
        FROM jadwal
        WHERE dokter_id = :dok_id AND tanggal = :today AND status <> 'tutup'
        ORDER BY jam_mulai ASC
        LIMIT 1
    ");
    $stmtToday->execute([':dok_id' => $dokterId, ':today' => $todayDb]);
    $jadwalHariIni = $stmtToday->fetch();

    // Jadwal praktik berikutnya (setelah hari ini)
    $stmtNext = $pdo->prepare("
        SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, p.nama AS nama_poli,
               (SELECT COUNT(*) FROM reservasi r WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS terisi_rsv,
               (SELECT COUNT(*) FROM antrean a WHERE a.jadwal_id = j.id AND a.status <> 'batal') AS terisi_ant
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        WHERE j.dokter_id = :dok_id AND j.tanggal > :today AND j.status <> 'tutup'
        ORDER BY j.tanggal ASC, j.jam_mulai ASC
        LIMIT 1
    ");
    $stmtNext->execute([':dok_id' => $dokterId, ':today' => $todayDb]);
    $jadwalBerikutnya = $stmtNext->fetch();

    // Daftar jadwal pada periode minggu yang dipilih
    $stmtList = $pdo->prepare("
        SELECT 
            j.id, j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, j.status,
            p.nama AS nama_poli, p.butuh_reservasi,
            EXTRACT(EPOCH FROM (j.jam_selesai - j.jam_mulai)) / 3600.0 AS durasi_jam,
            (SELECT COUNT(*) FROM reservasi r WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS terisi_rsv,
            (SELECT COUNT(*) FROM antrean a WHERE (a.jadwal_id = j.id OR (a.jadwal_id IS NULL AND a.poli_id = j.poli_id AND a.tanggal = j.tanggal)) AND a.status <> 'batal') AS terisi_ant
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        WHERE j.dokter_id = :dok_id
          AND j.tanggal BETWEEN :tgl_mulai AND :tgl_selesai
          AND j.status <> 'tutup'
        ORDER BY j.tanggal ASC, j.jam_mulai ASC
    ");
    $stmtList->execute([
        ':dok_id'      => $dokterId,
        ':tgl_mulai'   => $tglMulaiPeriode,
        ':tgl_selesai' => $tglSelesaiPeriode,
    ]);
    $allWeekRows = $stmtList->fetchAll();

    foreach ($allWeekRows as $r) {
        $statSesi++;
        $terisi = $r['butuh_reservasi'] ? (int) $r['terisi_rsv'] : (int) $r['terisi_ant'];
        $statTotalPasien += $terisi;
        if ($r['kuota'] !== null) {
            $statTotalKuota += (int) $r['kuota'];
        }
        $statTotalJam += (float) $r['durasi_jam'];

        $statusWaktu = 'terjadwal';
        if ($r['tanggal'] < $todayDb) {
            $statusWaktu = 'selesai';
        } elseif ($r['tanggal'] === $todayDb) {
            $statusWaktu = 'berlangsung';
        }

        if ($filterStatus === '' or $filterStatus === $statusWaktu) {
            $r['status_waktu'] = $statusWaktu;
            $r['terisi_total'] = $terisi;
            $jadwalMingguIni[] = $r;
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[jadwal-dokter] ' . $e->getMessage());
}

$jamBannerHariIni = $jadwalHariIni
    ? str_replace(':', '.', substr($jadwalHariIni['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($jadwalHariIni['jam_selesai'], 0, 5)) . ' WIB'
    : 'Tidak ada jadwal hari ini';
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

<body class="bg-page">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 p-6 md:p-8">

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

                <!-- Banner Praktik Saya Hari Ini -->
                <div class="bg-white border border-gray-200 rounded-base p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 md:w-12 md:h-12 bg-primary-50 border border-primary-100 text-primary rounded-base flex items-center justify-center shrink-0">
                            <i class="far fa-calendar-check text-base md:text-xl"></i>
                        </div>
                        <div>
                            <div class="flex flex-col-reverse md:flex-row md:items-center md:gap-3 mb-1">
                                <h2 class="text-lg font-bold text-gray-900">Praktik saya hari ini</h2>
                                <?php if ($jadwalHariIni): ?>
                                    <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Sedang berlangsung</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 text-gray-600 text-xs font-medium rounded bg-gray-100">Tidak ada sesi hari ini</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500"><?= e($namaPoliDokter) ?> &bull; <?= e($jamBannerHariIni) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-col md:items-end">
                        <button type="button" data-modal-target="modal-informasi-perubahan" data-modal-toggle="modal-informasi-perubahan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 mb-2 transition-colors cursor-pointer">
                            <i class="far fa-comment-alt"></i> Informasikan perubahan jadwal
                        </button>
                        <p class="text-xs text-gray-500 text-center md:text-left">Perubahan jadwal dikonfirmasi oleh admin</p>
                    </div>
                </div>

                <!-- Metrics Summary (3 Columns) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Sesi Praktik -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Sesi praktik</p>
                            <i class="far fa-calendar text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($statSesi) ?></h3>
                        <p class="text-xs text-gray-500"><?= e($labelPeriode) ?></p>
                    </div>
                    <!-- Total Reservasi / Kunjungan -->
                    <div class="bg-primary-50/40 border border-primary-100 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Total kunjungan / reservasi</p>
                            <i class="fas fa-user-friends text-primary-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary mb-1"><?= e($statTotalPasien) ?></h3>
                        <p class="text-xs text-gray-500">
                            <?= $statTotalKuota > 0 ? 'Dari ' . e($statTotalKuota) . ' kuota pada periode ini' : 'Layanan walk-in pada periode ini' ?>
                        </p>
                    </div>
                    <!-- Durasi Praktik -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Durasi praktik</p>
                            <i class="far fa-clock text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e(round($statTotalJam, 1)) ?> jam</h3>
                        <p class="text-xs text-gray-500"><?= e($statSesi) ?> sesi terjadwal</p>
                    </div>
                </div>

                <!-- Tabel Jadwal Praktik Saya -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm mb-6">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Jadwal praktik saya</h3>
                            <p class="text-sm text-gray-500"><?= e($namaDokter) ?> &bull; <?= e($namaPoliDokter) ?></p>
                        </div>
                        <span class="text-sm font-semibold text-primary"><?= e($labelPeriode) ?> &bull; <?= count($jadwalMingguIni) ?> sesi</span>
                    </div>

                    <!-- Filters & Pagination Bar -->
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <form method="GET" action="jadwal.php" class="flex flex-wrap items-center gap-2">
                            <input type="hidden" name="week" value="<?= $weekOffset ?>">

                            <a href="jadwal.php" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-base px-3 py-2 text-sm font-medium flex items-center gap-2 transition-colors">
                                <span><?= e($labelPeriode) ?></span>
                                <i class="far fa-calendar text-primary"></i>
                            </a>

                            <select name="status" onchange="this.form.submit()" class="border border-gray-300 text-gray-700 bg-white rounded-base px-3 py-2 text-sm font-medium focus:ring-primary focus:border-primary cursor-pointer">
                                <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua status</option>
                                <option value="selesai" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="berlangsung" <?= $filterStatus === 'berlangsung' ? 'selected' : '' ?>>Berlangsung</option>
                                <option value="terjadwal" <?= $filterStatus === 'terjadwal' ? 'selected' : '' ?>>Terjadwal</option>
                            </select>
                        </form>

                        <!-- Prev / Next Navigation -->
                        <div class="flex items-center gap-2">
                            <a href="jadwal.php?week=<?= $weekOffset - 1 ?>&status=<?= urlencode($filterStatus) ?>" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-base px-3 py-2 text-sm font-medium transition-colors">
                                Sebelumnya
                            </a>
                            <?php if ($weekOffset !== 0): ?>
                                <a href="jadwal.php?week=0&status=<?= urlencode($filterStatus) ?>" class="border border-primary-200 text-primary bg-primary-50 hover:bg-primary-100 rounded-base px-3 py-2 text-sm font-medium transition-colors">
                                    Minggu Ini
                                </a>
                            <?php endif; ?>
                            <a href="jadwal.php?week=<?= $weekOffset + 1 ?>&status=<?= urlencode($filterStatus) ?>" class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-base px-3 py-2 text-sm font-medium transition-colors">
                                Berikutnya
                            </a>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/50 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Tanggal</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Poli</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Waktu praktik</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Kuota</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Kunjungan / Reservasi</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Status</th>
                                    <th scope="col" class="px-6 py-3.5 font-semibold">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($jadwalMingguIni)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            Tidak ada jadwal praktik pada periode minggu ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($jadwalMingguIni as $row): ?>
                                        <?php
                                        $tsRow     = strtotime($row['tanggal']);
                                        $tglRowFmt = $hariMap[(int) date('N', $tsRow)] . ', ' . date('d', $tsRow) . ' ' . $bulanMap[(int) date('n', $tsRow)] . ' ' . date('Y', $tsRow);
                                        $jamRowFmt = str_replace(':', '.', substr($row['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($row['jam_selesai'], 0, 5)) . ' WIB';
                                        $kuotaMax  = $row['kuota'] !== null ? (int) $row['kuota'] : null;
                                        $terisi    = (int) $row['terisi_total'];
                                        $persen    = ($kuotaMax !== null && $kuotaMax > 0) ? min(100, round(($terisi / $kuotaMax) * 100)) : 0;
                                        $isToday   = ($row['status_waktu'] === 'berlangsung');
                                        ?>
                                        <tr class="<?= $isToday ? 'bg-primary-50/20 hover:bg-primary-50/30' : 'hover:bg-gray-50/50' ?>">
                                            <td class="px-6 py-4 font-bold text-gray-900"><?= e($tglRowFmt) ?></td>
                                            <td class="px-6 py-4 text-gray-500"><?= e($row['nama_poli']) ?></td>
                                            <td class="px-6 py-4 text-gray-900 font-medium"><?= e($jamRowFmt) ?></td>
                                            <td class="px-6 py-4 text-gray-900 font-medium"><?= $kuotaMax !== null ? e($kuotaMax) : 'Walk-in' ?></td>
                                            <td class="px-6 py-4">
                                                <?php if ($kuotaMax !== null): ?>
                                                    <div class="flex flex-col gap-1.5 w-28">
                                                        <span class="text-xs font-semibold text-gray-900"><?= $terisi ?> dari <?= $kuotaMax ?></span>
                                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                            <div class="bg-primary-500 h-1.5 rounded-full" style="width: <?= $persen ?>%"></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-xs font-semibold text-gray-900"><?= $terisi ?> pasien</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if ($row['status_waktu'] === 'selesai'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 text-fg-success-strong text-xs font-semibold rounded-base bg-success-soft">Selesai</span>
                                                <?php elseif ($row['status_waktu'] === 'berlangsung'): ?>
                                                    <span class="inline-flex items-center px-2 py-1 text-primary-600 text-xs font-semibold rounded bg-primary-50">Berlangsung</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2 py-1 text-gray-700 text-xs font-medium rounded bg-gray-200">Terjadwal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-gray-500">
                                                <?= $row['status_waktu'] === 'selesai' ? 'Pelayanan selesai' : ($row['status_waktu'] === 'berlangsung' ? 'Sesi hari ini' : 'Sesuai jadwal praktik') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Table -->
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                        <span>Menampilkan <?= count($jadwalMingguIni) ?> sesi pada periode ini</span>
                        <span>Terhubung ke Database</span>
                    </div>
                </div>

                <!-- Bottom Cards Area -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Perlu perubahan jadwal praktik? (Span 2 Cols) -->
                    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-base shadow-sm p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Perlu perubahan jadwal praktik?</h3>
                            <p class="text-sm text-gray-500 mb-6 max-w-xl">
                                Sampaikan tanggal, alasan, dan usulan waktu kepada admin. Jadwal dan kuota tetap dikelola oleh admin.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <button type="button" data-modal-target="modal-informasi-perubahan" data-modal-toggle="modal-informasi-perubahan" class="bg-primary-500 text-white hover:bg-primary-600 rounded-base px-5 py-2.5 text-sm font-medium flex items-center justify-center gap-2 transition-colors cursor-pointer w-fit">
                                <i class="far fa-comment-alt"></i> Kirim informasi ke admin
                            </button>
                            <span class="text-xs text-gray-400">Tidak mengubah jadwal secara langsung</span>
                        </div>
                    </div>

                    <!-- Praktik Berikutnya (Span 1 Col) -->
                    <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Praktik berikutnya</h3>
                            <?php if ($jadwalBerikutnya): ?>
                                <?php
                                $tsNext     = strtotime($jadwalBerikutnya['tanggal']);
                                $tglNextFmt = $hariMap[(int) date('N', $tsNext)] . ', ' . (int) date('j', $tsNext) . ' ' . $bulanFullMap[(int) date('n', $tsNext)] . ' ' . date('Y', $tsNext);
                                $jamNextFmt = str_replace(':', '.', substr($jadwalBerikutnya['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($jadwalBerikutnya['jam_selesai'], 0, 5)) . ' WIB';
                                ?>
                                <h4 class="text-base font-bold text-gray-900 mb-1"><?= e($tglNextFmt) ?></h4>
                                <p class="text-sm text-gray-500 mb-3"><?= e($jadwalBerikutnya['nama_poli']) ?> &bull; <?= e($jamNextFmt) ?></p>
                            <?php else: ?>
                                <p class="text-sm text-gray-500 mb-3">Belum ada jadwal praktik berikutnya yang terdaftar.</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($jadwalBerikutnya): ?>
                            <p class="text-sm font-bold text-primary">
                                <?php if ($jadwalBerikutnya['kuota'] !== null): ?>
                                    <?= (int) $jadwalBerikutnya['terisi_rsv'] ?> reservasi terdaftar dari <?= (int) $jadwalBerikutnya['kuota'] ?> kuota
                                <?php else: ?>
                                    Layanan Walk-in (Tanpa batas kuota)
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- Render Modal Perubahan Jadwal -->
    <?= $htmlModalPerubahanJadwal ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>