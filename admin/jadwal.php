<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

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
    $todayDb = $pdo->query("SELECT fn_today()::text")->fetchColumn() ?: date('Y-m-d');
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action       = $_POST['action'] ?? '';
    $redirectDate = $_POST['redirect_tanggal'] ?? $todayDb;

    try {
        if ($action === 'create') {
            $poliId      = (int) ($_POST['poli_id'] ?? 0);
            $dokterId    = !empty($_POST['dokter_id']) ? (int) $_POST['dokter_id'] : null;
            $tanggal     = trim($_POST['tanggal'] ?? '');
            $jamMulai    = trim($_POST['jam_mulai'] ?? '');
            $jamSelesai  = trim($_POST['jam_selesai'] ?? '');
            $tanpaBatas  = isset($_POST['tanpa_batas']);
            $kuota       = (!$tanpaBatas && isset($_POST['kuota']) && $_POST['kuota'] !== '')
                           ? (int) $_POST['kuota']
                           : null;
            $dipublikasi = isset($_POST['dipublikasi']);

            $stmt = $pdo->prepare("
                INSERT INTO jadwal (poli_id, dokter_id, tanggal, jam_mulai, jam_selesai, kuota, dipublikasi)
                VALUES (:poli_id, :dokter_id, :tanggal, :jam_mulai, :jam_selesai, :kuota, :dipublikasi)
            ");
            $stmt->bindValue(':poli_id', $poliId, PDO::PARAM_INT);
            $stmt->bindValue(':dokter_id', $dokterId, $dokterId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':tanggal', $tanggal, PDO::PARAM_STR);
            $stmt->bindValue(':jam_mulai', $jamMulai, PDO::PARAM_STR);
            $stmt->bindValue(':jam_selesai', $jamSelesai, PDO::PARAM_STR);
            $stmt->bindValue(':kuota', $kuota, $kuota === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':dipublikasi', $dipublikasi, PDO::PARAM_BOOL);
            $stmt->execute();

            $_SESSION['flash_success'] = "Jadwal praktik berhasil ditambahkan.";
            $redirectDate = $tanggal ?: $redirectDate;

        } elseif ($action === 'update') {
            $id           = (int) ($_POST['id'] ?? 0);
            $poliId       = (int) ($_POST['poli_id'] ?? 0);
            $dokterId     = !empty($_POST['dokter_id']) ? (int) $_POST['dokter_id'] : null;
            $tanggal      = trim($_POST['tanggal'] ?? '');
            $jamMulai     = trim($_POST['jam_mulai'] ?? '');
            $jamSelesai   = trim($_POST['jam_selesai'] ?? '');
            $tanpaBatas   = isset($_POST['tanpa_batas']);
            $kuota        = (!$tanpaBatas && isset($_POST['kuota']) && $_POST['kuota'] !== '')
                            ? (int) $_POST['kuota']
                            : null;
            $statusJadwal = $_POST['status'] ?? 'buka';
            if (!in_array($statusJadwal, ['buka', 'penuh', 'tutup'], true)) {
                $statusJadwal = 'buka';
            }
            $dipublikasi  = ($statusJadwal === 'tutup') ? false : isset($_POST['dipublikasi']);

            $stmt = $pdo->prepare("
                UPDATE jadwal
                   SET poli_id     = :poli_id,
                       dokter_id   = :dokter_id,
                       tanggal     = :tanggal,
                       jam_mulai   = :jam_mulai,
                       jam_selesai = :jam_selesai,
                       kuota       = :kuota,
                       status      = :status,
                       dipublikasi = :dipublikasi
                 WHERE id = :id
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':poli_id', $poliId, PDO::PARAM_INT);
            $stmt->bindValue(':dokter_id', $dokterId, $dokterId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':tanggal', $tanggal, PDO::PARAM_STR);
            $stmt->bindValue(':jam_mulai', $jamMulai, PDO::PARAM_STR);
            $stmt->bindValue(':jam_selesai', $jamSelesai, PDO::PARAM_STR);
            $stmt->bindValue(':kuota', $kuota, $kuota === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':status', $statusJadwal, PDO::PARAM_STR);
            $stmt->bindValue(':dipublikasi', $dipublikasi, PDO::PARAM_BOOL);
            $stmt->execute();

            if ($statusJadwal !== 'tutup') {
                $stmtSync = $pdo->prepare("SELECT fn_hitung_status_jadwal(:id)");
                $stmtSync->execute([':id' => $id]);
            }

            $_SESSION['flash_success'] = "Jadwal praktik berhasil diperbarui.";
            $redirectDate = $tanggal ?: $redirectDate;

        } elseif ($action === 'soft_delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmtTutup = $pdo->prepare("
                UPDATE jadwal
                   SET status      = 'tutup',
                       dipublikasi = FALSE
                 WHERE id = :id
            ");
            $stmtTutup->execute([':id' => $id]);

            $_SESSION['flash_success'] = "Jadwal berhasil ditutup. Seluruh reservasi aktif pada jadwal ini otomatis dibatalkan dan pasien telah diberi pemberitahuan.";
        }
    } catch (PDOException $e) {
        $rawMsg = $e->getMessage();
        if (str_contains($rawMsg, 'ex_jadwal_dokter_bentrok')) {
            $_SESSION['flash_error'] = "Gagal menyimpan: Dokter tersebut sudah memiliki jadwal aktif lain yang waktunya beririsan pada tanggal yang sama.";
        } elseif (str_contains($rawMsg, 'uq_jadwal')) {
            $_SESSION['flash_error'] = "Gagal menyimpan: Jadwal untuk poli, tanggal, dan jam mulai tersebut sudah ada.";
        } elseif (str_contains($rawMsg, 'chk_jadwal_jam')) {
            $_SESSION['flash_error'] = "Gagal menyimpan: Jam selesai harus lebih besar dari jam mulai.";
        } else {
            if (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $rawMsg, $matches)) {
                $_SESSION['flash_error'] = trim($matches[1]);
            } else {
                $_SESSION['flash_error'] = "Terjadi kendala saat memproses jadwal.";
            }
        }
        error_log('[jadwal-admin-crud] ' . $rawMsg);
    }

    header("Location: jadwal.php?tanggal=" . urlencode($redirectDate));
    exit;
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
    $listPoli = $pdo->query("
        SELECT id, nama, butuh_reservasi, kuota_harian, durasi_menit
        FROM poli
        ORDER BY id ASC
    ")->fetchAll();

    $listDokter = $pdo->query("
        SELECT d.id, d.poli_id, d.nama, d.profesi, p.nama AS nama_poli
        FROM dokter d
        JOIN users u ON d.user_id = u.id
        JOIN poli p  ON d.poli_id = p.id
        WHERE d.is_active = TRUE AND u.is_active = TRUE
        ORDER BY d.poli_id ASC, d.nama ASC
    ")->fetchAll();

    $stmtStats = $pdo->prepare("
        SELECT 
            j.id, j.kuota, j.status, j.dipublikasi, p.nama AS nama_poli, p.butuh_reservasi,
            (SELECT COUNT(*) FROM reservasi r WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS rsv_aktif
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        WHERE j.tanggal = :tanggal AND j.status <> 'tutup'
    ");
    $stmtStats->execute([':tanggal' => $filterTanggal]);
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

    $where  = ["j.tanggal = :tanggal"];
    $params = [':tanggal' => $filterTanggal];

    if ($filterPoli !== '' && ctype_digit($filterPoli)) {
        $where[]            = "j.poli_id = :poli_id";
        $params[':poli_id'] = (int) $filterPoli;
    }

    if (in_array($filterStatus, ['buka', 'penuh', 'tutup'], true)) {
        $where[]           = "j.status = :status";
        $params[':status'] = $filterStatus;
    } elseif ($filterStatus === '') {
        $where[] = "j.status <> 'tutup'";
    }

    $sqlList = "
        SELECT 
            j.id, j.poli_id, p.nama AS nama_poli, p.butuh_reservasi, p.kuota_harian,
            j.dokter_id, d.nama AS nama_dokter, d.profesi,
            j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, j.status, j.dipublikasi,
            (
                SELECT COUNT(*) 
                FROM reservasi r 
                WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan'
            ) AS terisi_reservasi,
            (
                SELECT COUNT(*) 
                FROM antrean a 
                WHERE (a.jadwal_id = j.id OR (a.jadwal_id IS NULL AND a.poli_id = j.poli_id AND a.tanggal = j.tanggal))
                  AND a.status <> 'batal'
            ) AS terisi_antrean
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        LEFT JOIN dokter d ON d.id = j.dokter_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY (j.status = 'tutup') ASC, j.jam_mulai ASC, j.poli_id ASC
    ";
    $stmtList = $pdo->prepare($sqlList);
    $stmtList->execute($params);
    $jadwalRows = $stmtList->fetchAll();

    if ($detailId !== null) {
        $stmtDetail = $pdo->prepare("
            SELECT 
                j.id, j.poli_id, p.nama AS nama_poli, p.butuh_reservasi, p.kuota_harian, p.durasi_menit,
                j.dokter_id, d.nama AS nama_dokter, d.profesi,
                j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, j.status, j.dipublikasi,
                (
                    SELECT COUNT(*) FROM reservasi r
                    WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan'
                ) AS terisi_online,
                (
                    SELECT COUNT(*) FROM antrean a
                    WHERE a.jadwal_id = j.id AND a.reservasi_id IS NULL AND a.status <> 'batal'
                ) AS terisi_offline_jadwal,
                (
                    SELECT COUNT(*) FROM antrean a
                    WHERE a.poli_id = j.poli_id AND a.tanggal = j.tanggal AND a.reservasi_id IS NULL AND a.status <> 'batal'
                ) AS terisi_walkin_poli
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE j.id = :id
        ");
        $stmtDetail->execute([':id' => $detailId]);
        $detailJadwal = $stmtDetail->fetch();

        if ($detailJadwal) {
            if ($detailJadwal['butuh_reservasi']) {
                $stmtPasien = $pdo->prepare("
                    SELECT 
                        ps.nama AS nama_pasien,
                        ps.nim_nip,
                        r.no_reservasi AS kode_tiket,
                        'Reservasi Online' AS sumber,
                        r.status
                    FROM reservasi r
                    JOIN pasien ps ON ps.id = r.pasien_id
                    WHERE r.jadwal_id = :jadwal_id
                    ORDER BY r.created_at ASC
                ");
                $stmtPasien->execute([':jadwal_id' => $detailId]);
                $detailPasienList = $stmtPasien->fetchAll();
            } else {
                $stmtPasien = $pdo->prepare("
                    SELECT 
                        ps.nama AS nama_pasien,
                        ps.nim_nip,
                        concat('ANT-', lpad(a.no_antrean::text, 3, '0')) AS kode_tiket,
                        CASE WHEN a.reservasi_id IS NOT NULL THEN 'Reservasi Online' ELSE 'Kunjungan Langsung' END AS sumber,
                        a.status
                    FROM antrean a
                    JOIN pasien ps ON ps.id = a.pasien_id
                    WHERE a.poli_id = :poli_id AND a.tanggal = :tanggal
                    ORDER BY a.no_antrean ASC
                ");
                $stmtPasien->execute([
                    ':poli_id' => $detailJadwal['poli_id'],
                    ':tanggal' => $detailJadwal['tanggal'],
                ]);
                $detailPasienList = $stmtPasien->fetchAll();
            }
        }
    }

    if ($editId !== null) {
        $stmtEdit = $pdo->prepare("
            SELECT j.*, p.butuh_reservasi, p.kuota_harian
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            WHERE j.id = :id
        ");
        $stmtEdit->execute([':id' => $editId]);
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
                            <i class="far fa-calendar-alt text-gray-400"></i>
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
                            <i class="far fa-check-circle text-emerald-700"></i>
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
                            <i class="far fa-calendar-times"></i>
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
</body>

</html>