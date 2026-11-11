<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Konfirmasi H-1";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

try {
    $todayDb = $pdo->query("SELECT fn_today()::text")->fetchColumn() ?: date('Y-m-d');
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}

$besokDb = date('Y-m-d', strtotime($todayDb . ' +1 day'));

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'catat_respons') {
    $reservasiId  = (int) ($_POST['reservasi_id'] ?? 0);
    $hasilKonfirm = $_POST['hasil_konfirmasi'] ?? 'hadir';
    $catatanAdmin = trim($_POST['catatan_respons'] ?? '');

    try {
        $pdo->beginTransaction();

        if (!empty($_SESSION['user_id'])) {
            $stmtCfg = $pdo->prepare("SELECT set_config('app.user_id', :uid, true)");
            $stmtCfg->execute([':uid' => (string) $_SESSION['user_id']]);
        }

        $hadirBool = ($hasilKonfirm === 'hadir');

        $stmtFn = $pdo->prepare("SELECT fn_konfirmasi_reservasi(:id, :hadir)");
        $stmtFn->bindValue(':id', $reservasiId, PDO::PARAM_INT);
        $stmtFn->bindValue(':hadir', $hadirBool, PDO::PARAM_BOOL);
        $stmtFn->execute();

        if ($catatanAdmin !== '') {
            $stmtUpd = $pdo->prepare("
                UPDATE reservasi
                   SET keluhan = COALESCE(keluhan, '') || ' [Catatan Konfirmasi: ' || :cat || ']'
                 WHERE id = :id
            ");
            $stmtUpd->execute([':cat' => $catatanAdmin, ':id' => $reservasiId]);
        }

        $pdo->commit();
        $_SESSION['flash_success'] = "Respons konfirmasi pasien berhasil dicatat.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $rawMsg = $e->getMessage();
        if (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $rawMsg, $matches)) {
            $_SESSION['flash_error'] = trim($matches[1]);
        } else {
            $_SESSION['flash_error'] = "Gagal mencatat respons: " . $rawMsg;
        }
        error_log('[konfirmasi-admin-post] ' . $rawMsg);
    }

    header("Location: konfirmasi.php");
    exit;
}

$filterTglKunjungan = trim($_GET['tanggal_kunjungan'] ?? $besokDb);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterTglKunjungan)) {
    $filterTglKunjungan = $besokDb;
}
$search       = trim($_GET['q'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$catatId      = isset($_GET['catat_id']) && ctype_digit((string) $_GET['catat_id']) ? (int) $_GET['catat_id'] : null;

$statTotalKonseling = 0;
$statMenunggu       = 0;
$statHadir          = 0;
$statBatal          = 0;
$listKonfirmasi     = [];
$modalReservasi     = null;
$errorDb            = false;

try {
    $stmtSt = $pdo->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE r.status <> 'dibatalkan') AS total,
            COUNT(*) FILTER (WHERE r.status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE r.status = 'dikonfirmasi') AS hadir,
            COUNT(*) FILTER (WHERE r.status IN ('dibatalkan', 'tidak_hadir')) AS batal
        FROM reservasi r
        JOIN jadwal j ON j.id = r.jadwal_id
        JOIN poli po  ON po.id = j.poli_id
        WHERE j.tanggal = :besok AND po.nama ILIKE '%konseling%'
    ");
    $stmtSt->execute([':besok' => $filterTglKunjungan]);
    $stData = $stmtSt->fetch();

    $statTotalKonseling = (int) ($stData['total'] ?? 0);
    $statMenunggu       = (int) ($stData['menunggu'] ?? 0);
    $statHadir          = (int) ($stData['hadir'] ?? 0);
    $statBatal          = (int) ($stData['batal'] ?? 0);

    $where  = ["j.tanggal = :tgl", "po.nama ILIKE '%konseling%'"];
    $params = [':tgl' => $filterTglKunjungan];

    if ($search !== '') {
        $where[] = "(p.nama ILIKE :q OR p.nim_nip ILIKE :q OR r.no_reservasi ILIKE :q)";
        $params[':q'] = '%' . $search . '%';
    }
    if (in_array($filterStatus, ['menunggu', 'dikonfirmasi', 'dibatalkan'], true)) {
        $where[] = "r.status = :status";
        $params[':status'] = $filterStatus;
    } else {
        $where[] = "r.status <> 'dibatalkan'";
    }

    $sqlList = "
        SELECT 
            r.id AS reservasi_id,
            r.no_reservasi,
            r.status,
            r.konfirmasi_diminta_at,
            r.dikonfirmasi_at,
            p.nama AS nama_pasien,
            p.nim_nip,
            p.no_hp,
            j.tanggal AS tanggal_kunjungan,
            j.jam_mulai,
            j.jam_selesai,
            d.nama AS nama_dokter
        FROM reservasi r
        JOIN pasien p ON p.id = r.pasien_id
        JOIN jadwal j ON j.id = r.jadwal_id
        JOIN poli po  ON po.id = j.poli_id
        LEFT JOIN dokter d ON d.id = j.dokter_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY j.jam_mulai ASC, r.created_at ASC
    ";
    $stmtList = $pdo->prepare($sqlList);
    $stmtList->execute($params);
    $listKonfirmasi = $stmtList->fetchAll();

    if ($catatId !== null) {
        $stmtM = $pdo->prepare("
            SELECT r.id AS reservasi_id, r.no_reservasi, p.nama AS nama_pasien, p.nim_nip, p.status_civitas,
                   po.nama AS nama_poli, j.tanggal, j.jam_mulai, j.jam_selesai
            FROM reservasi r
            JOIN pasien p ON p.id = r.pasien_id
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po  ON po.id = j.poli_id
            WHERE r.id = :id
        ");
        $stmtM->execute([':id' => $catatId]);
        $modalReservasi = $stmtM->fetch();
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[konfirmasi-admin] ' . $e->getMessage());
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

<body>
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <div class="flex min-w-0 flex-col w-full">
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <main class="flex-1 min-w-0 bg-page p-6">
                <?php if ($flashSuccess): ?>
                    <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 border border-emerald-300 rounded-base" role="alert"><?= e($flashSuccess) ?></div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert"><?= e($flashError) ?></div>
                <?php endif; ?>
                <?php if ($errorDb): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">Data konfirmasi gagal dimuat. Periksa log server.</div>
                <?php endif; ?>

                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Konfirmasi H-1</h1>
                    <p class="text-gray-500 mt-1">Hari ini: <?= e(date('d F Y', strtotime($todayDb))) ?></p>
                </div>

                <!-- Banner Informasi Tanggal -->
                <div class="bg-white border border-gray-200 rounded-base p-4 flex flex-col md:flex-row items-center md:justify-between shadow-xs mb-6 gap-4">
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <div class="text-primary-600 text-xl px-2"><i class="far fa-calendar-alt"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Hari konfirmasi</p>
                            <p class="font-bold text-gray-900 text-sm"><?= e(date('d M Y', strtotime($todayDb))) ?></p>
                        </div>
                        <div class="text-gray-400 px-2 md:px-4"><i class="fas fa-arrow-right"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal kunjungan</p>
                            <p class="font-bold text-gray-900 text-sm"><?= e(date('d M Y', strtotime($filterTglKunjungan))) ?></p>
                        </div>
                    </div>
                    <div class="w-full md:w-auto flex justify-start md:justify-end">
                        <span class="bg-primary-50 text-primary-700 text-xs font-bold px-3 py-1.5 rounded-base">Poli Konseling &middot; <?= e($statTotalKonseling) ?> pasien</span>
                    </div>
                </div>

                <!-- Ringkasan Cards -->
                <div class="mb-6">
                    <p class="text-xs text-gray-500 mb-3 font-medium">Ringkasan Konfirmasi Kunjungan Konseling</p>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white p-5 border border-gray-200 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-gray-700 font-semibold">Total konseling tanggal ini</p>
                            <h4 class="text-4xl font-bold my-3 text-gray-900 flex-1"><?= e($statTotalKonseling) ?></h4>
                            <p class="text-xs text-gray-500">Pasien terdaftar aktif</p>
                        </div>
                        <div class="bg-yellow-100 p-5 border border-yellow-400 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-yellow-900 font-semibold">Menunggu respons</p>
                            <h4 class="text-4xl font-bold my-3 text-yellow-900 flex-1"><?= e($statMenunggu) ?></h4>
                            <p class="text-xs text-yellow-900">Belum mengonfirmasi kehadiran</p>
                        </div>
                        <div class="bg-white p-5 border border-gray-200 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-gray-700 font-semibold">Konfirmasi hadir</p>
                            <h4 class="text-4xl font-bold my-3 text-gray-900 flex-1"><?= e($statHadir) ?></h4>
                            <p class="text-xs text-gray-500">Siap hadir sesuai jadwal</p>
                        </div>
                        <div class="bg-white p-5 border border-gray-200 rounded-base shadow-xs flex flex-col">
                            <p class="text-sm text-gray-700 font-semibold">Batal / Tidak hadir</p>
                            <h4 class="text-4xl font-bold my-3 text-gray-900 flex-1"><?= e($statBatal) ?></h4>
                            <p class="text-xs text-gray-500">Berhalangan hadir</p>
                        </div>
                    </div>
                </div>

                <!-- Main Data Table Section -->
                <div class="bg-white p-6 border border-gray-200 rounded-base shadow-xs mb-6">
                    <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-2">
                        <h4 class="text-lg font-bold text-gray-900">Daftar konfirmasi konseling</h4>
                        <span class="text-xs text-gray-500">Hubungi via WhatsApp, lalu catat respons pasien</span>
                    </div>

                    <!-- Filter GET -->
                    <form method="GET" action="konfirmasi.php" class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 items-end">
                        <div class="flex flex-col md:col-span-5">
                            <label class="text-xs text-gray-600 mb-1.5">Pencarian pasien</label>
                            <input type="text" name="q" value="<?= e($search) ?>" class="block w-full px-3 py-2 text-sm text-gray-900 border border-primary-400-05 rounded-base bg-white" placeholder="Cari nama, NIM/NIP, atau nomor reservasi">
                        </div>
                        <div class="flex flex-col md:col-span-3">
                            <label class="text-sm text-gray-600 mb-1.5">Tanggal kunjungan</label>
                            <input type="date" name="tanggal_kunjungan" value="<?= e($filterTglKunjungan) ?>" onchange="this.form.submit()" class="block w-full border border-primary-400-05 text-heading text-sm rounded-base bg-white px-3 py-2">
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label class="text-xs text-gray-600 mb-1.5">Status Konfirmasi</label>
                            <select name="status" onchange="this.form.submit()" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 bg-white p-2">
                                <option value="">Semua status aktif</option>
                                <option value="menunggu" <?= $filterStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu Respons</option>
                                <option value="dikonfirmasi" <?= $filterStatus === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                <option value="dibatalkan" <?= $filterStatus === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="flex flex-col md:col-span-2 items-end">
                            <a href="konfirmasi.php" class="text-xs text-primary-600 font-medium hover:underline py-2">Reset filter</a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="py-3 font-semibold">Pasien / Kontak</th>
                                    <th scope="col" class="py-3 font-semibold">Jadwal / Psikolog</th>
                                    <th scope="col" class="py-3 font-semibold">Konfirmasi</th>
                                    <th scope="col" class="py-3 font-semibold">Waktu Diminta</th>
                                    <th scope="col" class="py-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($listKonfirmasi)): ?>
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-500">Tidak ada data konfirmasi konseling untuk tanggal ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($listKonfirmasi as $row): ?>
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="py-4 pe-3 align-top">
                                                <div class="font-bold text-gray-900 text-sm mb-0.5"><?= e($row['nama_pasien']) ?></div>
                                                <div class="text-xs text-gray-500 mb-0.5">NIM/NIP &middot; <?= e($row['nim_nip']) ?></div>
                                                <div class="text-xs text-gray-500"><?= e($row['no_hp'] ?? 'No HP belum diisi') ?></div>
                                            </td>
                                            <td class="py-4 pe-3 align-top">
                                                <div class="font-medium text-gray-900 text-xs mb-1"><?= e(date('d M Y', strtotime($row['tanggal_kunjungan']))) ?> &middot; <?= e(substr($row['jam_mulai'], 0, 5)) ?>–<?= e(substr($row['jam_selesai'], 0, 5)) ?> WIB</div>
                                                <div class="text-xs text-gray-500"><?= e($row['nama_dokter'] ?? 'Psikolog bertugas') ?></div>
                                            </td>
                                            <td class="py-4 pe-3 align-top">
                                                <?php if ($row['status'] === 'menunggu'): ?>
                                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu Respons</span>
                                                <?php elseif ($row['status'] === 'dikonfirmasi'): ?>
                                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Konfirmasi Hadir</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-red-700 text-xs font-medium rounded bg-red-100">Dibatalkan</span>
                                                <?php endif; ?>
                                                <div class="text-xs text-gray-400">Tiket: <?= e($row['no_reservasi']) ?></div>
                                            </td>
                                            <td class="py-4 pe-3 align-top text-xs text-gray-500">
                                                <?= !empty($row['konfirmasi_diminta_at']) ? e(date('d M Y, H:i', strtotime($row['konfirmasi_diminta_at']))) : 'Belum dikirim' ?>
                                            </td>
                                            <td class="py-4 align-top text-right">
                                                <a href="konfirmasi.php?<?= http_build_query(['tanggal_kunjungan' => $filterTglKunjungan, 'catat_id' => (int) $row['reservasi_id']]) ?>" class="inline-flex items-center px-3 py-1.5 text-primary-700 bg-primary-50 hover:bg-primary-100 text-xs font-semibold rounded-base transition-colors">
                                                    Catat Respons
                                                </a>
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

    <?php require_once __DIR__ . '/components/catat-respon.php'; ?>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>