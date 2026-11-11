<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Reservasi";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

function formatTglSingkatRsv(string $dateStr): string
{
    $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $ts    = strtotime($dateStr);
    if (!$ts) return $dateStr;
    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

try {
    $todayDb = $pdo->query("SELECT fn_today()::text")->fetchColumn() ?: date('Y-m-d');
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// PROSES POST: CHECK-IN, JADWAL ULANG, DAN PEMBATALAN KUNJUNGAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $redirectTgl = $_POST['redirect_tanggal'] ?? $todayDb;
    $adminId     = isset($_SESSION['profil_id']) ? (int) $_SESSION['profil_id'] : null;

    try {
        $pdo->beginTransaction();

        if (!empty($_SESSION['user_id'])) {
            $stmtCfg = $pdo->prepare("SELECT set_config('app.user_id', :uid, true)");
            $stmtCfg->execute([':uid' => (string) $_SESSION['user_id']]);
        }

        if ($action === 'checkin') {
            $reservasiId = (int) ($_POST['reservasi_id'] ?? 0);

            $stmtIns = $pdo->prepare("
                INSERT INTO antrean (no_antrean, pasien_id, poli_id, jadwal_id, dokter_id, reservasi_id, admin_id)
                SELECT 0, r.pasien_id, j.poli_id, j.id, j.dokter_id, r.id, :admin_id
                FROM reservasi r
                JOIN jadwal j ON j.id = r.jadwal_id
                WHERE r.id = :rsv_id
                RETURNING no_antrean
            ");
            $stmtIns->bindValue(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtIns->bindValue(':rsv_id', $reservasiId, PDO::PARAM_INT);
            $stmtIns->execute();
            $noAntrean = $stmtIns->fetchColumn();

            $stmtHadir = $pdo->prepare("UPDATE reservasi SET status = 'hadir' WHERE id = :id AND status = 'dikonfirmasi'");
            $stmtHadir->execute([':id' => $reservasiId]);

            $_SESSION['flash_success'] = "Kehadiran pasien berhasil dicatat. Pasien masuk ke antrean nomor #" . (int) $noAntrean . ".";

        } elseif ($action === 'reschedule') {
            $reservasiId  = (int) ($_POST['reservasi_id'] ?? 0);
            $jadwalBaruId = (int) ($_POST['jadwal_baru_id'] ?? 0);

            $stmtRes = $pdo->prepare("SELECT fn_reschedule_reservasi(:rsv_id, :jadwal_baru_id)");
            $stmtRes->execute([
                ':rsv_id'         => $reservasiId,
                ':jadwal_baru_id' => $jadwalBaruId,
            ]);

            $_SESSION['flash_success'] = "Jadwal reservasi pasien berhasil diperbarui.";

        } elseif ($action === 'batal_reservasi') {
            $reservasiId = (int) ($_POST['reservasi_id'] ?? 0);
            $stmtBtl     = $pdo->prepare("
                UPDATE reservasi
                   SET status = 'dibatalkan', alasan_batal = 'admin'
                 WHERE id = :id
            ");
            $stmtBtl->execute([':id' => $reservasiId]);

            $stmtBtlAnt = $pdo->prepare("
                UPDATE antrean SET status = 'batal'
                 WHERE reservasi_id = :id AND status IN ('menunggu', 'diperiksa')
            ");
            $stmtBtlAnt->execute([':id' => $reservasiId]);

            $_SESSION['flash_success'] = "Reservasi pasien telah dibatalkan.";

        } elseif ($action === 'batal_walkin') {
            $antreanId = (int) ($_POST['antrean_id'] ?? 0);
            $stmtBtl   = $pdo->prepare("
                UPDATE antrean SET status = 'batal'
                 WHERE id = :id AND status IN ('menunggu', 'diperiksa')
            ");
            $stmtBtl->execute([':id' => $antreanId]);

            $_SESSION['flash_success'] = "Kunjungan langsung telah dibatalkan.";
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $rawMsg = $e->getMessage();
        if (str_contains($rawMsg, 'uq_antrean_reservasi')) {
            $_SESSION['flash_error'] = "Pasien pada reservasi ini sudah tercatat hadir di antrean.";
        } elseif (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $rawMsg, $matches)) {
            $_SESSION['flash_error'] = trim($matches[1]);
        } else {
            $_SESSION['flash_error'] = "Gagal memproses tindakan: " . $rawMsg;
        }
        error_log('[reservasi-admin-post] ' . $rawMsg);
    }

    header("Location: reservasi.php?tanggal=" . urlencode($redirectTgl));
    exit;
}

// PROSES GET: FILTER, STATISTIK COUNTER, DAN DAFTAR KUNJUNGAN GABUNGAN
$filterTanggal = trim($_GET['tanggal'] ?? $todayDb);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterTanggal)) {
    $filterTanggal = $todayDb;
}
$filterPoli   = trim($_GET['poli_id'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$filterAsal   = trim($_GET['asal'] ?? '');
$search       = trim($_GET['q'] ?? '');

$detailSource = $_GET['detail_src'] ?? '';
$detailId     = isset($_GET['detail_id']) && ctype_digit((string) $_GET['detail_id']) ? (int) $_GET['detail_id'] : null;
$rescheduleId = isset($_GET['reschedule_id']) && ctype_digit((string) $_GET['reschedule_id']) ? (int) $_GET['reschedule_id'] : null;

$listPoli          = [];
$kunjunganRows     = [];
$detailData        = null;
$rescheduleData    = null;
$jadwalPengganti   = [];
$errorDb           = false;

$cntOnlineTotal    = 0;
$cntOfflineTotal   = 0;
$cntOnlineSelesai  = 0;
$cntOfflineSelesai = 0;
$cntMenunggu       = 0;
$cntBelumCheckin   = 0;
$cntReschedule     = 0;
$cntDibatalkan     = 0;

try {
    $listPoli = $pdo->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();

    $stmtCnt = $pdo->prepare("
        SELECT
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status <> 'dibatalkan') AS online_aktif,
            (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status <> 'batal') AS offline_aktif,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status = 'selesai') AS online_selesai,
            (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status = 'selesai') AS offline_selesai,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status IN ('menunggu', 'dikonfirmasi'))
              + (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status = 'menunggu') AS total_menunggu,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status = 'dikonfirmasi' AND NOT EXISTS (SELECT 1 FROM antrean a WHERE a.reservasi_id = r.id)) AS belum_checkin,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.reschedule_count > 0) AS sudah_reschedule,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status IN ('dibatalkan', 'tidak_hadir'))
              + (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status = 'batal') AS total_batal
    ");
    $stmtCnt->execute([':tgl' => $filterTanggal]);
    $c = $stmtCnt->fetch();

    $cntOnlineTotal    = (int) ($c['online_aktif'] ?? 0);
    $cntOfflineTotal   = (int) ($c['offline_aktif'] ?? 0);
    $cntOnlineSelesai  = (int) ($c['online_selesai'] ?? 0);
    $cntOfflineSelesai = (int) ($c['offline_selesai'] ?? 0);
    $cntMenunggu       = (int) ($c['total_menunggu'] ?? 0);
    $cntBelumCheckin   = (int) ($c['belum_checkin'] ?? 0);
    $cntReschedule     = (int) ($c['sudah_reschedule'] ?? 0);
    $cntDibatalkan     = (int) ($c['total_batal'] ?? 0);

    $sqlUnion = "
        SELECT * FROM (
            SELECT 
                r.id AS item_id,
                'online' AS asal,
                r.no_reservasi AS kode_tiket,
                p.id AS pasien_id,
                p.nama AS nama_pasien,
                p.nim_nip,
                p.status_civitas,
                j.poli_id,
                po.nama AS nama_poli,
                d.nama AS nama_dokter,
                j.tanggal,
                j.jam_mulai,
                j.jam_selesai,
                r.status,
                r.reschedule_count,
                r.keluhan,
                r.created_at,
                a.id AS antrean_id,
                a.no_antrean
            FROM reservasi r
            JOIN pasien p ON p.id = r.pasien_id
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po  ON po.id = j.poli_id
            LEFT JOIN dokter d  ON d.id = j.dokter_id
            LEFT JOIN antrean a ON a.reservasi_id = r.id

            UNION ALL

            SELECT 
                a.id AS item_id,
                'offline' AS asal,
                concat('ANT-', lpad(a.no_antrean::text, 3, '0')) AS kode_tiket,
                p.id AS pasien_id,
                p.nama AS nama_pasien,
                p.nim_nip,
                p.status_civitas,
                a.poli_id,
                po.nama AS nama_poli,
                COALESCE(d.nama, jd.nama) AS nama_dokter,
                a.tanggal,
                j.jam_mulai,
                j.jam_selesai,
                CASE a.status
                    WHEN 'batal'     THEN 'dibatalkan'
                    WHEN 'diperiksa' THEN 'hadir'
                    ELSE a.status
                END AS status,
                0 AS reschedule_count,
                pm.keluhan,
                a.waktu_daftar AS created_at,
                a.id AS antrean_id,
                a.no_antrean
            FROM antrean a
            JOIN pasien p ON p.id = a.pasien_id
            JOIN poli po  ON po.id = a.poli_id
            LEFT JOIN dokter d       ON d.id = a.dokter_id
            LEFT JOIN jadwal j       ON j.id = a.jadwal_id
            LEFT JOIN dokter jd      ON jd.id = j.dokter_id
            LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
            WHERE a.reservasi_id IS NULL
        ) gabungan
        WHERE tanggal = :tanggal
    ";

    $params = [':tanggal' => $filterTanggal];

    if ($search !== '') {
        $sqlUnion    .= " AND (nama_pasien ILIKE :q OR nim_nip ILIKE :q OR kode_tiket ILIKE :q OR COALESCE(nama_dokter, '') ILIKE :q)";
        $params[':q'] = '%' . $search . '%';
    }
    if ($filterPoli !== '' && ctype_digit($filterPoli)) {
        $sqlUnion          .= " AND poli_id = :poli_id";
        $params[':poli_id'] = (int) $filterPoli;
    }
    if (in_array($filterAsal, ['online', 'offline'], true)) {
        $sqlUnion       .= " AND asal = :asal";
        $params[':asal'] = $filterAsal;
    }
    if ($filterStatus !== '') {
        $sqlUnion         .= " AND status = :status";
        $params[':status'] = $filterStatus;
    }

    $sqlUnion .= " ORDER BY COALESCE(jam_mulai, '08:00:00'::time) ASC, created_at ASC";

    $stmtList = $pdo->prepare($sqlUnion);
    $stmtList->execute($params);
    $kunjunganRows = $stmtList->fetchAll();

    if ($detailId !== null && in_array($detailSource, ['online', 'offline'], true)) {
        if ($detailSource === 'online') {
            $stmtDet = $pdo->prepare("
                SELECT 
                    'online' AS asal,
                    r.id, r.no_reservasi AS kode_tiket, r.status, r.keluhan, r.reschedule_count,
                    r.konfirmasi_diminta_at, r.dikonfirmasi_at, r.dibatalkan_at, r.alasan_batal, r.created_at,
                    p.nama AS nama_pasien, p.nim_nip, p.status_civitas, p.no_hp,
                    po.nama AS nama_poli, po.butuh_reservasi,
                    d.nama AS nama_dokter,
                    j.tanggal, j.jam_mulai, j.jam_selesai,
                    a.id AS antrean_id, a.no_antrean
                FROM reservasi r
                JOIN pasien p ON p.id = r.pasien_id
                JOIN jadwal j ON j.id = r.jadwal_id
                JOIN poli po  ON po.id = j.poli_id
                LEFT JOIN dokter d  ON d.id = j.dokter_id
                LEFT JOIN antrean a ON a.reservasi_id = r.id
                WHERE r.id = :id
            ");
            $stmtDet->execute([':id' => $detailId]);
            $detailData = $stmtDet->fetch();
        } else {
            $stmtDet = $pdo->prepare("
                SELECT 
                    'offline' AS asal,
                    a.id, concat('ANT-', lpad(a.no_antrean::text, 3, '0')) AS kode_tiket,
                    a.status, pm.keluhan, 0 AS reschedule_count,
                    NULL AS konfirmasi_diminta_at, NULL AS dikonfirmasi_at, NULL AS dibatalkan_at, NULL AS alasan_batal,
                    a.waktu_daftar AS created_at,
                    p.nama AS nama_pasien, p.nim_nip, p.status_civitas, p.no_hp,
                    po.nama AS nama_poli, po.butuh_reservasi,
                    COALESCE(d.nama, jd.nama) AS nama_dokter,
                    a.tanggal, j.jam_mulai, j.jam_selesai,
                    a.id AS antrean_id, a.no_antrean
                FROM antrean a
                JOIN pasien p ON p.id = a.pasien_id
                JOIN poli po  ON po.id = a.poli_id
                LEFT JOIN dokter d       ON d.id = a.dokter_id
                LEFT JOIN jadwal j       ON j.id = a.jadwal_id
                LEFT JOIN dokter jd      ON jd.id = j.dokter_id
                LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
                WHERE a.id = :id
            ");
            $stmtDet->execute([':id' => $detailId]);
            $detailData = $stmtDet->fetch();
        }
    }

    if ($rescheduleId !== null) {
        $stmtRsc = $pdo->prepare("
            SELECT r.id, r.no_reservasi, r.jadwal_id, r.status, r.reschedule_count,
                   p.nama AS nama_pasien, p.nim_nip,
                   j.poli_id, po.nama AS nama_poli, j.tanggal, j.jam_mulai, j.jam_selesai
            FROM reservasi r
            JOIN pasien p ON p.id = r.pasien_id
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po  ON po.id = j.poli_id
            WHERE r.id = :id
        ");
        $stmtRsc->execute([':id' => $rescheduleId]);
        $rescheduleData = $stmtRsc->fetch();

        if ($rescheduleData) {
            $stmtJdw = $pdo->prepare("
                SELECT id, dokter, tanggal, jam_mulai, jam_selesai, sisa_kuota
                FROM v_jadwal_tersedia
                WHERE poli_id = :poli_id AND id <> :jadwal_lama AND sisa_kuota > 0
                ORDER BY tanggal ASC, jam_mulai ASC
            ");
            $stmtJdw->execute([
                ':poli_id'     => $rescheduleData['poli_id'],
                ':jadwal_lama' => $rescheduleData['jadwal_id'],
            ]);
            $jadwalPengganti = $stmtJdw->fetchAll();
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[reservasi-admin-read] ' . $e->getMessage());
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
                        Data reservasi belum dapat dimuat saat ini. Silakan muat ulang halaman.
                    </div>
                <?php endif; ?>

                <!-- Counter Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Total kunjungan</p>
                            <i class="fas fa-user-friends text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($cntOnlineTotal + $cntOfflineTotal) ?></h3>
                        <p class="text-xs text-gray-500"><?= e($cntOnlineTotal) ?> reservasi &bull; <?= e($cntOfflineTotal) ?> kunjungan langsung</p>
                    </div>

                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-emerald-900">Kunjungan selesai</p>
                            <i class="far fa-check-circle text-emerald-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-emerald-800 mb-1"><?= e($cntOnlineSelesai + $cntOfflineSelesai) ?></h3>
                        <p class="text-xs text-emerald-800"><?= e($cntOnlineSelesai) ?> reservasi &bull; <?= e($cntOfflineSelesai) ?> kunjungan langsung</p>
                    </div>

                    <div class="bg-primary-50/60 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-primary-900">Menunggu layanan</p>
                            <i class="far fa-clock text-primary-600"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary-700 mb-1"><?= e($cntMenunggu) ?></h3>
                        <p class="text-xs text-primary-800"><?= e($cntBelumCheckin) ?> terkonfirmasi belum hadir</p>
                    </div>

                    <div class="bg-yellow-50/80 border border-yellow-300 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-yellow-900">Jadwal ulang / Batal</p>
                            <i class="fas fa-exchange-alt text-yellow-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-yellow-800 mb-1"><?= e($cntReschedule) ?></h3>
                        <p class="text-xs text-yellow-800"><?= e($cntDibatalkan) ?> kunjungan dibatalkan</p>
                    </div>
                </div>

                <!-- Daftar Reservasi & Kunjungan -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar reservasi &amp; kunjungan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Kelola kehadiran pasien reservasi dan pendaftaran kunjungan langsung</p>
                        </div>
                        <span class="text-xs font-semibold text-primary-700 bg-primary-50 px-3 py-1 rounded-base">
                            <?= count($kunjunganRows) ?> data ditampilkan
                        </span>
                    </div>

                    <!-- Form Filter (Method GET) -->
                    <form method="GET" action="reservasi.php">
                        <div class="my-4 relative">
                            <label for="search-reservation" class="sr-only">Cari</label>
                            <input type="search" id="search-reservation" name="q" value="<?= e($search) ?>" class="block w-full p-3 ps-10 text-sm text-gray-900 border border-gray-300 rounded-base bg-gray-50/50 focus:ring-primary focus:border-primary" placeholder="Cari nama pasien, NIM/NIP, dokter, atau nomor tiket (RSV / ANT)..." />
                            <div class="absolute inset-y-0 inset-s-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                                <i class="fas fa-search text-sm"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
                            <div class="flex flex-col">
                                <label for="filter-tanggal" class="text-xs font-medium text-gray-500 mb-1">Tanggal kunjungan</label>
                                <input id="filter-tanggal" name="tanggal" type="date" value="<?= e($filterTanggal) ?>" onchange="this.form.submit()" class="block w-full border border-gray-300 text-gray-900 text-sm rounded-base focus:ring-primary focus:border-primary px-3 py-2 bg-white" />
                            </div>

                            <div class="flex flex-col">
                                <label for="poli-filter" class="text-xs font-medium text-gray-500 mb-1">Poli</label>
                                <select id="poli-filter" name="poli_id" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-base px-3 py-2 bg-white">
                                    <option value="">Semua Poli</option>
                                    <?php foreach ($listPoli as $p): ?>
                                        <option value="<?= (int) $p['id'] ?>" <?= $filterPoli === (string) $p['id'] ? 'selected' : '' ?>>
                                            <?= e($p['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label for="status-filter" class="text-xs font-medium text-gray-500 mb-1">Status</label>
                                <select id="status-filter" name="status" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-base px-3 py-2 bg-white">
                                    <option value="">Semua Status</option>
                                    <option value="menunggu" <?= $filterStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                    <option value="dikonfirmasi" <?= $filterStatus === 'dikonfirmasi' ? 'selected' : '' ?>>Terkonfirmasi (Siap Hadir)</option>
                                    <option value="hadir" <?= $filterStatus === 'hadir' ? 'selected' : '' ?>>Hadir / Diperiksa</option>
                                    <option value="selesai" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="tidak_hadir" <?= $filterStatus === 'tidak_hadir' ? 'selected' : '' ?>>Tidak Hadir</option>
                                    <option value="dibatalkan" <?= $filterStatus === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label for="asal-filter" class="text-xs font-medium text-gray-500 mb-1">Jenis Pendaftaran</label>
                                <select id="asal-filter" name="asal" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-base px-3 py-2 bg-white">
                                    <option value="">Semua Jenis</option>
                                    <option value="online" <?= $filterAsal === 'online' ? 'selected' : '' ?>>Reservasi Online</option>
                                    <option value="offline" <?= $filterAsal === 'offline' ? 'selected' : '' ?>>Kunjungan Langsung</option>
                                </select>
                            </div>

                            <div class="flex flex-row gap-3 items-end">
                                <a href="reservasi.php?tanggal=<?= urlencode($todayDb) ?>" class="inline-flex items-center justify-center px-3.5 py-2 text-primary-700 text-xs font-semibold rounded-base bg-primary-50 border border-primary-100 hover:bg-primary-100 transition-colors">Hari ini</a>
                                <a href="reservasi.php" class="text-primary text-xs font-semibold py-2 hover:underline">Reset filter</a>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel Data -->
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/60 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Pasien</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Layanan &amp; Jadwal</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Jenis</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Status</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($kunjunganRows)): ?>
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            Belum ada data reservasi atau kunjungan pada tanggal dan filter ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($kunjunganRows as $row): ?>
                                        <?php
                                        $labelId       = ($row['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                                        $jamSesi       = (!empty($row['jam_mulai']) && !empty($row['jam_selesai']))
                                            ? str_replace(':', '.', substr($row['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($row['jam_selesai'], 0, 5)) . ' WIB'
                                            : 'Jam Operasional';
                                        $isToday       = ($row['tanggal'] === $todayDb);
                                        $canCheckin    = ($row['asal'] === 'online' && $row['status'] === 'dikonfirmasi' && empty($row['antrean_id']) && $isToday);
                                        $canReschedule = ($row['asal'] === 'online' && in_array($row['status'], ['menunggu', 'dikonfirmasi'], true) && (int) $row['reschedule_count'] < 1);
                                        $canCancel     = in_array($row['status'], ['menunggu', 'dikonfirmasi'], true);

                                        $baseQ = http_build_query([
                                            'tanggal' => $filterTanggal,
                                            'poli_id' => $filterPoli,
                                            'status'  => $filterStatus,
                                            'asal'    => $filterAsal,
                                            'q'       => $search,
                                        ]);
                                        ?>
                                        <tr class="hover:bg-gray-50/60 transition-colors">
                                            <th scope="row" class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                <div class="text-base font-bold"><?= e($row['nama_pasien']) ?></div>
                                                <div class="text-xs font-normal text-gray-500 mt-0.5"><?= $labelId ?> &middot; <?= e($row['nim_nip']) ?></div>
                                                <div class="text-xs font-normal text-gray-400"><?= e($row['kode_tiket']) ?></div>
                                            </th>
                                            <td class="px-4 py-4">
                                                <div class="font-bold text-gray-900"><?= e($row['nama_poli']) ?> &middot; <?= e($jamSesi) ?></div>
                                                <div class="text-xs text-gray-600 mt-0.5"><?= e($row['nama_dokter'] ?? 'Tenaga layanan bertugas') ?></div>
                                                <div class="text-xs text-gray-400"><?= e(formatTglSingkatRsv($row['tanggal'])) ?></div>
                                            </td>
                                            <td class="px-4 py-4 align-top">
                                                <?php if ($row['asal'] === 'online'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 text-primary-700 text-xs font-semibold rounded-base bg-primary-50">Reservasi Online</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 text-gray-700 text-xs font-semibold rounded-base bg-gray-100">Kunjungan Langsung</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 align-top">
                                                <?php if ($row['status'] === 'selesai'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-emerald-700 text-xs font-semibold rounded-base bg-emerald-50 border border-emerald-200">Selesai</span>
                                                    <div class="text-xs text-gray-500">Pemeriksaan selesai</div>
                                                <?php elseif ($row['status'] === 'hadir'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-primary-700 text-xs font-semibold rounded-base bg-primary-100">Hadir &middot; Antrean #<?= (int) $row['no_antrean'] ?></span>
                                                    <div class="text-xs text-gray-500">Dalam antrean pemeriksaan</div>
                                                <?php elseif ($row['status'] === 'dikonfirmasi'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-primary text-xs font-semibold rounded-base bg-brand-softer">Terkonfirmasi</span>
                                                    <div class="text-xs text-gray-500"><?= $row['antrean_id'] ? 'Antrean #' . (int) $row['no_antrean'] : 'Siap catat kehadiran' ?></div>
                                                <?php elseif ($row['status'] === 'menunggu'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-yellow-900 text-xs font-semibold rounded-base bg-yellow-100">Menunggu</span>
                                                    <div class="text-xs text-gray-500"><?= $row['asal'] === 'online' ? 'Menunggu konfirmasi H-1' : 'Menunggu panggilan dokter' ?></div>
                                                <?php elseif ($row['status'] === 'tidak_hadir'): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-red-800 text-xs font-semibold rounded-base bg-red-100">Tidak Hadir</span>
                                                    <div class="text-xs text-gray-500">Melewati jadwal sesi</div>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 mb-1 text-gray-700 text-xs font-semibold rounded-base bg-gray-200">Dibatalkan</span>
                                                    <div class="text-xs text-gray-500">Kunjungan dibatalkan</div>
                                                <?php endif; ?>

                                                <?php if ((int) $row['reschedule_count'] > 0): ?>
                                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded bg-amber-50 text-amber-700">
                                                        Dijadwalkan Ulang 1x
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 align-top text-right">
                                                <div class="flex flex-wrap justify-end items-center gap-2 pt-1">
                                                    <a href="reservasi.php?<?= $baseQ ?>&detail_src=<?= e($row['asal']) ?>&detail_id=<?= (int) $row['item_id'] ?>" class="text-xs font-semibold hover:underline text-primary">Lihat</a>

                                                    <?php if ($canCheckin): ?>
                                                        <form method="POST" action="reservasi.php" class="inline">
                                                            <input type="hidden" name="action" value="checkin">
                                                            <input type="hidden" name="reservasi_id" value="<?= (int) $row['item_id'] ?>">
                                                            <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">
                                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-primary text-xs font-semibold rounded-base bg-brand-softer hover:bg-primary hover:text-white transition-all cursor-pointer">
                                                                Catat Hadir
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($canReschedule): ?>
                                                        <a href="reservasi.php?<?= $baseQ ?>&reschedule_id=<?= (int) $row['item_id'] ?>" class="inline-flex items-center px-3 py-1.5 text-amber-700 text-xs font-semibold rounded-base bg-amber-50 hover:bg-amber-600 hover:text-white transition-all">
                                                            Ubah Jadwal
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($canCancel): ?>
                                                        <form method="POST" action="reservasi.php" class="inline" onsubmit="return confirm('Batalkan kunjungan/reservasi ini?')">
                                                            <input type="hidden" name="action" value="<?= $row['asal'] === 'online' ? 'batal_reservasi' : 'batal_walkin' ?>">
                                                            <input type="hidden" name="<?= $row['asal'] === 'online' ? 'reservasi_id' : 'antrean_id' ?>" value="<?= (int) $row['item_id'] ?>">
                                                            <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-red-700 text-xs font-semibold rounded-base bg-red-50 hover:bg-red-600 hover:text-white transition-all cursor-pointer">
                                                                Batalkan
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Detail Reservasi -->
    <?php require_once __DIR__ . '/components/reservasi/detail-reservasi.php'; ?>

    <!-- Modal Reschedule Reservasi -->
    <?php if (!empty($rescheduleData)): ?>
        <?php require_once __DIR__ . '/components/reservasi/reschedule-reservasi.php' ?>
    <?php endif; ?>

    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>