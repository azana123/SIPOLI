<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Resep";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

function formatTanggalIndoResep(string $dateStr): string
{
    $hari  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $ts    = strtotime($dateStr);
    if (!$ts) return $dateStr;
    return $hari[(int) date('N', $ts)] . ', ' . (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

try {
    $todayDb = $pdo->query("SELECT fn_today()::text")->fetchColumn() ?: date('Y-m-d');
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status_resep') {
    $permintaanId = (int) ($_POST['permintaan_id'] ?? 0);
    $statusBaru   = $_POST['status_baru'] ?? '';
    $catatanAdmin = trim($_POST['catatan'] ?? '');
    $redirectTgl  = $_POST['redirect_tanggal'] ?? $todayDb;
    $adminId      = isset($_SESSION['profil_id']) ? (int) $_SESSION['profil_id'] : null;

    if (!in_array($statusBaru, ['disiapkan', 'diserahkan', 'dibatalkan'], true)) {
        $_SESSION['flash_error'] = "Status pemrosesan resep tidak valid.";
        header("Location: resep.php?tanggal=" . urlencode($redirectTgl) . "&id=" . $permintaanId);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if (!empty($_SESSION['user_id'])) {
            $stmtCfg = $pdo->prepare("SELECT set_config('app.user_id', :uid, true)");
            $stmtCfg->execute([':uid' => (string) $_SESSION['user_id']]);
        }

        $stmtCek = $pdo->prepare("SELECT id, pemeriksaan_id, status FROM permintaan_obat WHERE id = :id FOR UPDATE");
        $stmtCek->execute([':id' => $permintaanId]);
        $req = $stmtCek->fetch();

        if (!$req) {
            throw new Exception("Data permintaan resep tidak ditemukan.");
        }
        if (in_array($req['status'], ['diserahkan', 'dibatalkan'], true)) {
            throw new Exception("Resep yang sudah selesai atau dibatalkan tidak dapat diubah lagi.");
        }

        $stmtUpd = $pdo->prepare("
            UPDATE permintaan_obat
               SET status           = :status,
                   diproses_oleh    = COALESCE(:admin_id, diproses_oleh),
                   waktu_diserahkan = CASE WHEN :status = 'diserahkan' THEN NOW() ELSE waktu_diserahkan END,
                   catatan          = COALESCE(NULLIF(:catatan, ''), catatan)
             WHERE id = :id
        ");
        $stmtUpd->bindValue(':status', $statusBaru, PDO::PARAM_STR);
        $stmtUpd->bindValue(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmtUpd->bindValue(':catatan', $catatanAdmin, PDO::PARAM_STR);
        $stmtUpd->bindValue(':id', $permintaanId, PDO::PARAM_INT);
        $stmtUpd->execute();

        $stmtInbox = $pdo->prepare("
            UPDATE inbox
               SET dibaca_at       = COALESCE(dibaca_at, NOW()),
                   aksi_selesai_at = CASE WHEN :status IN ('diserahkan', 'dibatalkan') THEN NOW() ELSE aksi_selesai_at END
             WHERE tipe = 'permintaan_obat' AND ref_tipe = 'permintaan_obat' AND ref_id = :id
        ");
        $stmtInbox->execute([':status' => $statusBaru, ':id' => $permintaanId]);

        $pdo->commit();

        $labelSukses = match ($statusBaru) {
            'disiapkan'  => 'Resep mulai disiapkan oleh petugas.',
            'diserahkan' => 'Obat telah diserahkan kepada pasien.',
            'dibatalkan' => 'Permintaan obat telah dibatalkan.',
        };
        $_SESSION['flash_success'] = $labelSukses;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash_error'] = $e->getMessage();
        error_log('[resep-admin-post] ' . $e->getMessage());
    }

    header("Location: resep.php?tanggal=" . urlencode($redirectTgl) . "&id=" . $permintaanId);
    exit;
}

$filterTanggal = trim($_GET['tanggal'] ?? $todayDb);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterTanggal)) {
    $filterTanggal = $todayDb;
}
$filterStatus = trim($_GET['status'] ?? '');
$search       = trim($_GET['q'] ?? '');
$selectedId   = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : null;

$statHariIni  = 0;
$statMenunggu = 0;
$statDiproses = 0;
$statSelesai  = 0;
$listAntrean  = [];
$detailResep  = null;
$detailItems  = [];
$errorDb      = false;

try {
    $stmtStat = $pdo->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE po.status <> 'dibatalkan') AS total_hari_ini,
            COUNT(*) FILTER (WHERE po.status = 'menunggu')    AS total_menunggu,
            COUNT(*) FILTER (WHERE po.status = 'disiapkan')   AS total_diproses,
            COUNT(*) FILTER (WHERE po.status = 'diserahkan')  AS total_selesai
        FROM permintaan_obat po
        JOIN pemeriksaan pm ON pm.id = po.pemeriksaan_id
        WHERE pm.tanggal = :tanggal
    ");
    $stmtStat->execute([':tanggal' => $filterTanggal]);
    $st = $stmtStat->fetch();

    $statHariIni  = (int) ($st['total_hari_ini'] ?? 0);
    $statMenunggu = (int) ($st['total_menunggu'] ?? 0);
    $statDiproses = (int) ($st['total_diproses'] ?? 0);
    $statSelesai  = (int) ($st['total_selesai'] ?? 0);

    $where  = ["pm.tanggal = :tanggal"];
    $params = [':tanggal' => $filterTanggal];

    if ($search !== '') {
        $where[]      = "(ps.nama ILIKE :q OR ps.nim_nip ILIKE :q OR d.nama ILIKE :q OR concat('RSP-', lpad(po.id::text, 3, '0')) ILIKE :q)";
        $params[':q'] = '%' . $search . '%';
    }

    if (in_array($filterStatus, ['menunggu', 'disiapkan', 'diserahkan', 'dibatalkan'], true)) {
        $where[]           = "po.status = :status";
        $params[':status'] = $filterStatus;
    } else {
        $where[] = "po.status <> 'dibatalkan'";
    }

    $sqlList = "
        SELECT 
            po.id AS permintaan_id,
            concat('RSP-', lpad(po.id::text, 3, '0')) AS kode_resep,
            po.status,
            po.created_at,
            po.waktu_diserahkan,
            pm.id AS pemeriksaan_id,
            ps.nama AS nama_pasien,
            ps.nim_nip,
            d.nama AS nama_dokter,
            (SELECT COUNT(*) FROM resep_obat ro WHERE ro.pemeriksaan_id = pm.id) AS jumlah_obat
        FROM permintaan_obat po
        JOIN pemeriksaan pm ON pm.id = po.pemeriksaan_id
        JOIN pasien ps      ON ps.id = pm.pasien_id
        JOIN dokter d       ON d.id  = pm.dokter_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY 
            CASE po.status 
                WHEN 'menunggu'   THEN 1 
                WHEN 'disiapkan'  THEN 2 
                WHEN 'diserahkan' THEN 3 
                ELSE 4 
            END ASC,
            po.created_at DESC
    ";
    $stmtList = $pdo->prepare($sqlList);
    $stmtList->execute($params);
    $listAntrean = $stmtList->fetchAll();

    if ($selectedId === null && !empty($listAntrean)) {
        $selectedId = (int) $listAntrean[0]['permintaan_id'];
    }

    if ($selectedId !== null) {
        $stmtDet = $pdo->prepare("
            SELECT 
                po.id AS permintaan_id,
                concat('RSP-', lpad(po.id::text, 3, '0')) AS kode_resep,
                po.status,
                po.catatan AS catatan_apotek,
                po.created_at,
                po.waktu_diserahkan,
                adm.nama AS nama_admin,
                pm.id AS pemeriksaan_id,
                pm.tanggal,
                pm.diagnosa,
                pm.catatan AS catatan_dokter,
                ps.id AS pasien_id,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.riwayat_alergi,
                d.nama AS nama_dokter,
                pol.nama AS nama_poli
            FROM permintaan_obat po
            JOIN pemeriksaan pm ON pm.id  = po.pemeriksaan_id
            JOIN pasien ps      ON ps.id  = pm.pasien_id
            JOIN dokter d       ON d.id   = pm.dokter_id
            JOIN poli pol       ON pol.id = pm.poli_id
            LEFT JOIN admin adm ON adm.id = po.diproses_oleh
            WHERE po.id = :id
        ");
        $stmtDet->execute([':id' => $selectedId]);
        $detailResep = $stmtDet->fetch();

        if ($detailResep) {
            $stmtItems = $pdo->prepare("
                SELECT 
                    ro.id AS resep_obat_id,
                    ro.jumlah,
                    ro.aturan_pakai,
                    o.id AS obat_id,
                    o.kode_obat,
                    o.nama_obat,
                    o.bentuk,
                    o.satuan,
                    o.stok AS stok_saat_ini,
                    lso.stok_sebelum,
                    lso.stok_sesudah
                FROM resep_obat ro
                JOIN obat o ON o.id = ro.obat_id
                LEFT JOIN LATERAL (
                    SELECT stok_sebelum, stok_sesudah
                    FROM log_stok_obat l
                    WHERE l.resep_obat_id = ro.id
                    ORDER BY l.id DESC
                    LIMIT 1
                ) lso ON TRUE
                WHERE ro.pemeriksaan_id = :pem_id
                ORDER BY ro.id ASC
            ");
            $stmtItems->execute([':pem_id' => $detailResep['pemeriksaan_id']]);
            $detailItems = $stmtItems->fetchAll();
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[resep-admin-read] ' . $e->getMessage());
}

$hasDetail = !empty($detailResep);
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
                        Data resep belum dapat dimuat saat ini. Silakan muat ulang halaman.
                    </div>
                <?php endif; ?>

                <!-- Page Info & Action -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <p class="text-sm text-gray-600 font-medium">Tinjau resep dokter, periksa ketersediaan stok, lalu proses penyiapan dan penyerahan obat.</p>
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-primary-700 bg-primary-50 border border-primary-100 rounded-base">
                        Pelayanan Resep Obat
                    </span>
                </div>

                <!-- Stats Counter -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Resep tanggal ini</p>
                            <i class="fas fa-file-medical text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($statHariIni) ?></h3>
                        <p class="text-xs text-gray-500"><?= e(formatTanggalIndoResep($filterTanggal)) ?></p>
                    </div>
                    <div class="bg-yellow-50/80 border border-yellow-300 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-yellow-900">Menunggu</p>
                            <i class="far fa-clock text-yellow-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-yellow-800 mb-1"><?= e($statMenunggu) ?></h3>
                        <p class="text-xs text-yellow-800">Perlu penyiapan obat</p>
                    </div>
                    <div class="bg-primary-50/60 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-primary-900">Sedang disiapkan</p>
                            <i class="fas fa-mortar-pestle text-primary-600"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary-700 mb-1"><?= e($statDiproses) ?></h3>
                        <p class="text-xs text-primary-800">Dalam proses pengemasan</p>
                    </div>
                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-emerald-900">Selesai diserahkan</p>
                            <i class="far fa-check-circle text-emerald-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-emerald-800 mb-1"><?= e($statSelesai) ?></h3>
                        <p class="text-xs text-emerald-800">Obat telah diterima pasien</p>
                    </div>
                </div>

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Left Column: Antrean Resep -->
                    <div id="left-card-resep" class="<?= $hasDetail ? 'lg:col-span-7' : 'lg:col-span-12' ?> p-6 bg-white border border-gray-200 rounded-base shadow-sm flex flex-col h-full">
                        <div class="border-b border-gray-100 pb-3 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900">Antrean resep</h3>
                            <span class="text-sm text-gray-500"><?= count($listAntrean) ?> resep</span>
                        </div>

                        <!-- Filters (Method GET) -->
                        <form method="GET" action="resep.php" class="flex flex-col gap-3 my-4">
                            <div class="relative w-full">
                                <input type="search" name="q" value="<?= e($search) ?>" class="block w-full p-2.5 pe-10 text-sm text-gray-900 border border-gray-300 rounded-base focus:ring-primary-500 focus:border-primary-500" placeholder="Cari pasien, NIM/NIP, dokter, atau nomor resep..." />
                                <button type="submit" class="absolute inset-y-0 inset-e-0 flex items-center pe-3 text-primary cursor-pointer">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                                <div class="relative">
                                    <input type="date" name="tanggal" value="<?= e($filterTanggal) ?>" onchange="this.form.submit()" class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-base focus:ring-primary-500 focus:border-primary-500" />
                                </div>
                                <select name="status" onchange="this.form.submit()" class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-base focus:ring-primary-500 focus:border-primary-500 bg-white">
                                    <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua status aktif</option>
                                    <option value="menunggu" <?= $filterStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                    <option value="disiapkan" <?= $filterStatus === 'disiapkan' ? 'selected' : '' ?>>Sedang Disiapkan</option>
                                    <option value="diserahkan" <?= $filterStatus === 'diserahkan' ? 'selected' : '' ?>>Telah Diserahkan</option>
                                    <option value="dibatalkan" <?= $filterStatus === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                                <div class="flex items-center justify-end gap-3">
                                    <a href="resep.php?tanggal=<?= urlencode($todayDb) ?>" class="text-xs font-semibold text-primary-700 bg-primary-50 px-3 py-2 rounded-base border border-primary-100 hover:bg-primary-100">Hari ini</a>
                                    <a href="resep.php" class="text-xs font-medium text-primary-600 hover:underline">Reset</a>
                                </div>
                            </div>
                        </form>

                        <!-- List Antrean -->
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-xs text-gray-500 bg-gray-50/60 border-b border-gray-200 uppercase">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 font-semibold w-1/2">Pasien / Resep</th>
                                        <th scope="col" class="px-4 py-3 font-semibold">Dokter</th>
                                        <th scope="col" class="px-4 py-3 font-semibold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if (empty($listAntrean)): ?>
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                                Belum ada antrean resep pada tanggal dan filter ini.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listAntrean as $row): ?>
                                            <?php
                                            $isSelected = ((int) $row['permintaan_id'] === $selectedId);
                                            $jamDibuat  = date('H.i', strtotime($row['created_at']));
                                            $linkDetail = 'resep.php?' . http_build_query([
                                                'tanggal' => $filterTanggal,
                                                'status'  => $filterStatus,
                                                'q'       => $search,
                                                'id'      => (int) $row['permintaan_id'],
                                            ]);
                                            ?>
                                            <tr onclick="window.location.href='<?= e($linkDetail) ?>'"
                                                class="<?= $isSelected ? 'bg-primary-50/60 border-s-4 border-s-primary' : 'bg-white hover:bg-gray-50' ?> cursor-pointer transition-colors">
                                                <td class="px-4 py-3.5">
                                                    <div class="font-bold text-gray-900 text-base"><?= e($row['nama_pasien']) ?></div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        <?= e($row['kode_resep']) ?> &middot; <?= e($jamDibuat) ?> WIB &middot; <?= (int) $row['jumlah_obat'] ?> jenis obat
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5 text-gray-600"><?= e($row['nama_dokter']) ?></td>
                                                <td class="px-4 py-3.5 text-center">
                                                    <?php if ($row['status'] === 'menunggu'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base bg-orange-100 text-orange-800">Menunggu</span>
                                                    <?php elseif ($row['status'] === 'disiapkan'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base bg-brand-softer text-fg-brand-strong">Disiapkan</span>
                                                    <?php elseif ($row['status'] === 'diserahkan'): ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">Selesai</span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 text-red-700 text-xs font-semibold rounded-base bg-red-100">Dibatalkan</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-2 mt-auto">
                            <p class="text-xs text-gray-400">Menampilkan <?= count($listAntrean) ?> resep &middot; Pilih baris untuk melihat rincian obat</p>
                        </div>
                    </div>

                    <!-- Right Column: Detail Resep -->
                    <?php if ($hasDetail): ?>
                        <div id="right-card-resep" class="flex lg:col-span-5 bg-white border border-gray-200 rounded-base shadow-sm p-6 flex-col h-full">
                            <!-- Detail Header -->
                            <div class="flex justify-between items-start mb-6">
                                <h3 class="text-lg font-bold text-gray-900">Rincian <?= e($detailResep['kode_resep']) ?></h3>
                                <?php if ($detailResep['status'] === 'menunggu'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base bg-orange-100 text-orange-800">Menunggu</span>
                                <?php elseif ($detailResep['status'] === 'disiapkan'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base bg-brand-softer text-fg-brand-strong">Disiapkan</span>
                                <?php elseif ($detailResep['status'] === 'diserahkan'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">Selesai</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 text-red-700 text-xs font-semibold rounded-base bg-red-100">Dibatalkan</span>
                                <?php endif; ?>
                            </div>

                            <!-- Patient Info -->
                            <div class="mb-6">
                                <h4 class="text-base font-bold text-gray-900 mb-1"><?= e($detailResep['nama_pasien']) ?></h4>
                                <p class="text-xs text-gray-500 mb-1">
                                    NIM/NIP <?= e($detailResep['nim_nip']) ?> &middot; <?= e(date('d M Y, H.i', strtotime($detailResep['created_at']))) ?> WIB
                                </p>
                                <p class="text-xs text-gray-600">Dokter pemeriksa: <span class="font-semibold"><?= e($detailResep['nama_dokter']) ?></span> (<?= e($detailResep['nama_poli']) ?>)</p>
                                <?php if (!empty($detailResep['riwayat_alergi'])): ?>
                                    <p class="text-xs font-semibold text-red-700 bg-red-50 px-2.5 py-1 rounded mt-2 inline-block">
                                        Riwayat Alergi: <?= e($detailResep['riwayat_alergi']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Doctor Note -->
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-base mb-6">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Diagnosis &amp; Catatan Dokter</p>
                                <p class="text-sm font-bold text-gray-900 mb-1"><?= e($detailResep['diagnosa']) ?></p>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    <?= !empty($detailResep['catatan_dokter']) ? e($detailResep['catatan_dokter']) : 'Siapkan obat sesuai jumlah dan aturan pakai yang tertulis.' ?>
                                </p>
                            </div>

                            <!-- Medicine List -->
                            <div class="mb-4 flex-1">
                                <h5 class="text-sm font-bold text-gray-900 mb-3"><?= count($detailItems) ?> jenis obat yang diresepkan</h5>

                                <?php foreach ($detailItems as $item): ?>
                                    <?php
                                    $stokSebelum = $item['stok_sebelum'] !== null ? (int) $item['stok_sebelum'] : ((int) $item['stok_saat_ini'] + (int) $item['jumlah']);
                                    ?>
                                    <div class="bg-page border border-primary-50 p-4 rounded-base mb-3">
                                        <div class="flex justify-between items-start mb-1">
                                            <h6 class="font-bold text-gray-900 text-sm"><?= e($item['nama_obat']) ?></h6>
                                            <span class="font-bold text-primary-600 text-xs"><?= (int) $item['jumlah'] ?> <?= e($item['satuan']) ?></span>
                                        </div>
                                        <p class="text-xs text-gray-500 mb-2">
                                            <?= e($item['kode_obat']) ?> &middot; Stok awal <?= $stokSebelum ?> <?= e($item['satuan']) ?> &middot; Sisa stok saat ini <?= (int) $item['stok_saat_ini'] ?> <?= e($item['satuan']) ?>
                                        </p>
                                        <p class="text-xs text-gray-800">
                                            Aturan pakai: <span class="font-semibold"><?= e($item['aturan_pakai'] ?: 'Sesuai anjuran dokter') ?></span>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Action Area -->
                            <div class="border-t border-gray-100 pt-4">
                                <div class="bg-emerald-50 border border-emerald-100 p-3 rounded-base mb-4 flex items-center">
                                    <i class="far fa-check-circle text-emerald-600 mr-2"></i>
                                    <span class="text-xs font-semibold text-emerald-800">Stok obat telah dikurangi secara otomatis saat resep diterbitkan</span>
                                </div>

                                <?php if (in_array($detailResep['status'], ['menunggu', 'disiapkan'], true)): ?>
                                    <form method="POST" action="resep.php" class="space-y-3 mb-4">
                                        <input type="hidden" name="action" value="update_status_resep">
                                        <input type="hidden" name="permintaan_id" value="<?= (int) $detailResep['permintaan_id'] ?>">
                                        <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Catatan penyerahan obat (opsional)</label>
                                            <input type="text" name="catatan" value="<?= e($detailResep['catatan_apotek'] ?? '') ?>" placeholder="Contoh: Obat sudah lengkap dan telah dijelaskan aturan pakainya" class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-base bg-white" />
                                        </div>

                                        <div class="flex flex-wrap justify-between items-center gap-2 pt-2">
                                            <button type="submit" name="status_baru" value="dibatalkan" onclick="return confirm('Batalkan permintaan obat ini?')" class="px-3 py-2 text-xs font-bold text-red-700 bg-red-50 rounded-base hover:bg-red-100 cursor-pointer">
                                                Batalkan
                                            </button>

                                            <div class="flex items-center gap-2">
                                                <?php if ($detailResep['status'] === 'menunggu'): ?>
                                                    <button type="submit" name="status_baru" value="disiapkan" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-primary-700 bg-primary-50 border border-primary-200 rounded-base hover:bg-primary-100 cursor-pointer">
                                                        Siapkan Obat
                                                    </button>
                                                <?php endif; ?>

                                                <button type="submit" name="status_baru" value="diserahkan" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-primary rounded-base hover:bg-primary-700 transition-colors cursor-pointer">
                                                    <i class="fas fa-check mr-1.5"></i> Serahkan Obat
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-base mb-4 text-xs text-gray-600">
                                        <?php if ($detailResep['status'] === 'diserahkan'): ?>
                                            Obat telah diserahkan pada <b><?= e($detailResep['waktu_diserahkan'] ? date('d M Y, H.i', strtotime($detailResep['waktu_diserahkan'])) . ' WIB' : '-') ?></b>
                                            oleh <b><?= e($detailResep['nama_admin'] ?? 'Admin') ?></b>.
                                        <?php else: ?>
                                            Permintaan resep ini telah <b>dibatalkan</b>.
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <p class="text-xs text-gray-400 leading-relaxed">
                                    Petugas hanya memproses penyiapan dan penyerahan obat sesuai resep dokter.
                                </p>
                            </div>

                        </div>
                    <?php endif; ?>

                </div>

            </main>
        </div>
    </div>

    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../assets/js/app.js"></script>
</body>

</html>