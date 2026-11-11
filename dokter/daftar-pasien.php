<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('dokter');
require_once __DIR__ . '/../config/database.php';

$page_title = "Daftar Pasien";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$dokterId = (int) ($_SESSION['profil_id'] ?? 0);
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

// Jalankan modal terlebih dahulu agar perubahan status langsung tercermin
ob_start();
require_once __DIR__ . "/components/dashboard/ringkasan-pemeriksaan.php";
$htmlModalRingkasan = ob_get_clean();

ob_start();
require_once __DIR__ . "/components/dashboard/form-pemeriksaan.php";
$htmlModalFormPeriksa = ob_get_clean();

ob_start();
require_once __DIR__ . "/components/dashboard/perubahan-jadwal.php";
$htmlModalPerubahanJadwal = ob_get_clean();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$filterTanggal = trim($_GET['tanggal'] ?? $todayDb);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterTanggal)) {
    $filterTanggal = $todayDb;
}
$search       = trim($_GET['q'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$filterAsal   = trim($_GET['asal'] ?? '');

$poliIdDokter   = (int) ($dokterProfil['poli_id'] ?? 0);
$namaPoliDokter = $dokterProfil['nama_poli'] ?? 'Poli';
$namaDokter     = $dokterProfil['nama'] ?? 'Dokter';

$jadwalSesi     = null;
$cntTotal       = 0;
$cntOnline      = 0;
$cntOffline     = 0;
$cntMenunggu    = 0;
$cntDiperiksa   = 0;
$cntSelesai     = 0;
$pasienRows     = [];
$pasienAktif    = null;
$errorDb        = false;

try {
    // Info jadwal dokter pada tanggal yang dipilih
    $stmtJdw = $pdo->prepare("
        SELECT id, tanggal, jam_mulai, jam_selesai, kuota, status
        FROM jadwal
        WHERE dokter_id = :dok_id AND tanggal = :tgl AND status <> 'tutup'
        ORDER BY jam_mulai ASC
        LIMIT 1
    ");
    $stmtJdw->execute([':dok_id' => $dokterId, ':tgl' => $filterTanggal]);
    $jadwalSesi = $stmtJdw->fetch();

    // Statistik Counter pada tanggal tersebut (tanpa filter pencarian/status)
    $stmtStat = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            COUNT(*) FILTER (WHERE a.reservasi_id IS NOT NULL) AS online_cnt,
            COUNT(*) FILTER (WHERE a.reservasi_id IS NULL) AS offline_cnt,
            COUNT(*) FILTER (WHERE a.status = 'menunggu') AS menunggu_cnt,
            COUNT(*) FILTER (WHERE a.status = 'diperiksa') AS diperiksa_cnt,
            COUNT(*) FILTER (WHERE a.status = 'selesai') AS selesai_cnt
        FROM antrean a
        WHERE a.tanggal = :tgl
          AND a.poli_id = :poli_id
          AND (a.dokter_id = :dok_id OR a.dokter_id IS NULL)
          AND a.status <> 'batal'
    ");
    $stmtStat->execute([
        ':tgl'     => $filterTanggal,
        ':poli_id' => $poliIdDokter,
        ':dok_id'  => $dokterId,
    ]);
    $st = $stmtStat->fetch();

    $cntTotal     = (int) ($st['total'] ?? 0);
    $cntOnline    = (int) ($st['online_cnt'] ?? 0);
    $cntOffline   = (int) ($st['offline_cnt'] ?? 0);
    $cntMenunggu  = (int) ($st['menunggu_cnt'] ?? 0);
    $cntDiperiksa = (int) ($st['diperiksa_cnt'] ?? 0);
    $cntSelesai   = (int) ($st['selesai_cnt'] ?? 0);

    // Daftar pasien sesuai filter
    $where = [
        "a.tanggal = :tgl",
        "a.poli_id = :poli_id",
        "(a.dokter_id = :dok_id OR a.dokter_id IS NULL)",
        "a.status <> 'batal'",
    ];
    $params = [
        ':tgl'     => $filterTanggal,
        ':poli_id' => $poliIdDokter,
        ':dok_id'  => $dokterId,
    ];

    if ($search !== '') {
        $where[]      = "(ps.nama ILIKE :q OR ps.nim_nip ILIKE :q)";
        $params[':q'] = '%' . $search . '%';
    }
    if (in_array($filterStatus, ['menunggu', 'diperiksa', 'selesai'], true)) {
        $where[]           = "a.status = :status";
        $params[':status'] = $filterStatus;
    }
    if ($filterAsal === 'online') {
        $where[] = "a.reservasi_id IS NOT NULL";
    } elseif ($filterAsal === 'offline') {
        $where[] = "a.reservasi_id IS NULL";
    }

    $sqlList = "
        SELECT 
            a.id AS antrean_id,
            a.no_antrean,
            a.status,
            a.reservasi_id,
            a.waktu_daftar,
            ps.id AS pasien_id,
            ps.nama AS nama_pasien,
            ps.nim_nip,
            ps.status_civitas,
            COALESCE(j.jam_mulai, (a.waktu_daftar AT TIME ZONE 'Asia/Jakarta')::time) AS jam_kunjungan,
            COALESCE(pm.keluhan, r.keluhan, 'Pemeriksaan langsung') AS keluhan_awal,
            pm.id AS pemeriksaan_id
        FROM antrean a
        JOIN pasien ps ON ps.id = a.pasien_id
        LEFT JOIN jadwal j       ON j.id = a.jadwal_id
        LEFT JOIN reservasi r    ON r.id = a.reservasi_id
        LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY 
            CASE a.status
                WHEN 'diperiksa' THEN 1
                WHEN 'menunggu'  THEN 2
                WHEN 'selesai'   THEN 3
                ELSE 4
            END ASC,
            a.no_antrean ASC
    ";
    $stmtList = $pdo->prepare($sqlList);
    $stmtList->execute($params);
    $pasienRows = $stmtList->fetchAll();

    foreach ($pasienRows as $pr) {
        if ($pr['status'] === 'diperiksa') {
            $pasienAktif = $pr;
            break;
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[daftar-pasien-dokter] ' . $e->getMessage());
}

$jamSesiFmt = $jadwalSesi
    ? str_replace(':', '.', substr($jadwalSesi['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($jadwalSesi['jam_selesai'], 0, 5)) . ' WIB'
    : 'Sesuai jam operasional';
$kuotaLabel = ($jadwalSesi && $jadwalSesi['kuota'] !== null)
    ? 'Kuota ' . (int) $jadwalSesi['kuota']
    : 'Walk-in / Tanpa Batas';
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

            <!-- Main Content -->
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

                <!-- Banner Informasi Sesi Praktik -->
                <div class="bg-white border border-gray-200 rounded-base p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary-50 border border-primary-100 text-primary-600 rounded-base flex items-center justify-center shrink-0">
                            <i class="fas fa-user-nurse text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Pasien saya</h2>
                            <p class="text-sm text-gray-500 mt-0.5"><?= e($namaPoliDokter) ?> &bull; <?= e($jamSesiFmt) ?> &bull; <?= e($kuotaLabel) ?></p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <?= $filterTanggal === $todayDb ? 'Hari Ini' : e(date('d M Y', strtotime($filterTanggal))) ?>
                        </span>
                    </div>
                </div>

                <!-- Counter Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Terjadwal -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-gray-700">Pasien terdaftar</p>
                            <i class="fas fa-user-friends text-gray-400 text-base"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold text-gray-900 mb-2"><?= e($cntTotal) ?></h3>
                        <p class="text-xs text-gray-500"><?= e($cntOnline) ?> online &bull; <?= e($cntOffline) ?> offline</p>
                    </div>

                    <!-- Menunggu Pemeriksaan -->
                    <div class="bg-yellow-50/70 border border-yellow-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-gray-700">Menunggu pemeriksaan</p>
                            <i class="far fa-clock text-yellow-600 text-base"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold text-yellow-700 mb-2"><?= e($cntMenunggu) ?></h3>
                        <p class="text-xs text-yellow-800">Pasien dalam antrean saya</p>
                    </div>

                    <!-- Sedang Diperiksa -->
                    <div class="bg-primary-50/70 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-primary-900">Sedang diperiksa</p>
                            <i class="fas fa-stethoscope text-primary-600 text-base"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold text-primary-600 mb-2"><?= e($cntDiperiksa) ?></h3>
                        <p class="text-xs text-primary-800">Pemeriksaan belum selesai</p>
                    </div>

                    <!-- Pemeriksaan Selesai -->
                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-sm font-semibold text-emerald-900">Pemeriksaan selesai</p>
                        </div>
                        <h3 class="text-3xl font-extrabold text-emerald-800 mb-2"><?= e($cntSelesai) ?></h3>
                        <p class="text-xs text-emerald-800">Penanganan telah dicatat</p>
                    </div>
                </div>

                <!-- Main Card: Daftar Reservasi & Antrean Pasien -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm mb-6">
                    
                    <!-- Card Header -->
                    <div class="px-6 pt-6 pb-3 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar kunjungan pasien</h3>
                            <p class="text-sm text-gray-500">Pasien yang terdaftar untuk <?= e($namaDokter) ?></p>
                        </div>
                        <span class="text-sm font-medium text-primary-600 px-3 py-1 self-start md:self-auto">
                            <?= count($pasienRows) ?> pasien ditampilkan
                        </span>
                    </div>

                    <!-- Filter Controls (Method GET) -->
                    <form method="GET" action="daftar-pasien.php" class="p-6 pt-3 border-b border-gray-100 bg-gray-50/30">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Input Search -->
                            <div class="relative">
                                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, NIM, atau NIP" class="w-full pl-4 pr-10 py-2.5 bg-white border border-gray-300 rounded-base text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700 placeholder-gray-400">
                                <button type="submit" class=" absolute right-3.5 top-1/2 -translate-y-1/2 text-primary text-sm cursor-pointer">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>

                            <!-- Input Date -->
                            <div class="relative">
                                <input id="filter-tanggal" name="tanggal" type="date" value="<?= e($filterTanggal) ?>" onchange="this.form.submit()" class="block w-full border border-gray-300 bg-white text-heading text-sm rounded-base focus:ring-primary focus:border-primary px-3 py-2.5 shadow-xs" />
                            </div>

                            <!-- Dropdown Status -->
                            <div class="relative">
                                <select name="status" onchange="this.form.submit()" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none pr-8 cursor-pointer">
                                    <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua status</option>
                                    <option value="menunggu" <?= $filterStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                    <option value="diperiksa" <?= $filterStatus === 'diperiksa' ? 'selected' : '' ?>>Diperiksa</option>
                                    <option value="selesai" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <!-- Dropdown Asal -->
                            <div class="relative">
                                <select name="asal" onchange="this.form.submit()" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none pr-8 cursor-pointer">
                                    <option value="" <?= $filterAsal === '' ? 'selected' : '' ?>>Semua asal</option>
                                    <option value="online" <?= $filterAsal === 'online' ? 'selected' : '' ?>>Online (Reservasi)</option>
                                    <option value="offline" <?= $filterAsal === 'offline' ? 'selected' : '' ?>>Offline (Walk-in)</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </form>

                    <!-- Table Container -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/80 border-b border-gray-200 uppercase font-semibold">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">Pasien / NIM atau NIP</th>
                                    <th scope="col" class="px-6 py-3.5">Jam / Antrean</th>
                                    <th scope="col" class="px-6 py-3.5">Keluhan awal</th>
                                    <th scope="col" class="px-6 py-3.5">Asal</th>
                                    <th scope="col" class="px-6 py-3.5">Pemeriksaan</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($pasienRows)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            Tidak ada data kunjungan pasien pada tanggal dan filter ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pasienRows as $row): ?>
                                        <?php
                                        $lblId   = ($row['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                                        $jamFmt  = !empty($row['jam_kunjungan']) ? str_replace(':', '.', substr($row['jam_kunjungan'], 0, 5)) : '-';
                                        $isOnline = !empty($row['reservasi_id']);
                                        $baseQ   = http_build_query([
                                            'tanggal' => $filterTanggal,
                                            'q'       => $search,
                                            'status'  => $filterStatus,
                                            'asal'    => $filterAsal,
                                        ]);
                                        ?>
                                        <tr class="<?= $row['status'] === 'diperiksa' ? 'bg-primary-50/20 hover:bg-primary-50/40' : 'hover:bg-gray-50/60' ?> transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900 text-base"><?= e($row['nama_pasien']) ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?= $lblId ?> <?= e($row['nim_nip']) ?> &bull; <span class="capitalize"><?= e($row['status_civitas']) ?></span></div>
                                            </td>
                                            <td class="px-6 py-4 font-bold text-gray-900">
                                                <div><?= e($jamFmt) ?></div>
                                                <div class="text-xs font-normal text-gray-400">Antrean #<?= (int) $row['no_antrean'] ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600"><?= e($row['keluhan_awal']) ?></td>
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-900"><?= $isOnline ? 'Online' : 'Offline' ?></div>
                                                <div class="text-xs text-gray-400 mt-0.5">Terkonfirmasi</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if ($row['status'] === 'selesai'): ?>
                                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Selesai
                                                    </span>
                                                <?php elseif ($row['status'] === 'diperiksa'): ?>
                                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-primary-700 bg-primary-100/70 border border-primary-200 rounded-base">
                                                        Diperiksa
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-amber-50 text-amber-800 border border-amber-200">
                                                        Menunggu
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <?php if ($row['status'] === 'selesai'): ?>
                                                    <a href="daftar-pasien.php?<?= $baseQ ?>&ringkasan_antrean_id=<?= (int) $row['antrean_id'] ?>" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm inline-block">
                                                        Lihat Riwayat
                                                    </a>
                                                <?php elseif ($row['status'] === 'diperiksa'): ?>
                                                    <div class="flex flex-col items-end gap-1">
                                                        <a href="daftar-pasien.php?<?= $baseQ ?>&periksa_antrean_id=<?= (int) $row['antrean_id'] ?>" class="bg-primary text-white hover:bg-primary-700 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm inline-block">
                                                            Lanjutkan Pemeriksaan
                                                        </a>
                                                        <a href="riwayat-penanganan.php?pasien_id=<?= (int) $row['pasien_id'] ?>" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                                                            Lihat riwayat pasien <i class="fas fa-arrow-right text-2xs"></i>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex flex-col items-end gap-1">
                                                        <a href="daftar-pasien.php?<?= $baseQ ?>&periksa_antrean_id=<?= (int) $row['antrean_id'] ?>" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-4 py-2 text-xs font-semibold transition-colors shadow-sm inline-block">
                                                            Mulai Pemeriksaan
                                                        </a>
                                                        <a href="riwayat-penanganan.php?pasien_id=<?= (int) $row['pasien_id'] ?>" class="text-xs text-primary hover:underline font-medium inline-flex items-center gap-1">
                                                            Lihat riwayat pasien <i class="fas fa-arrow-right text-2xs"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400 font-medium">
                        <span>Menampilkan <?= count($pasienRows) ?> dari <?= e($cntTotal) ?> pasien pada tanggal ini</span>
                        <a href="daftar-pasien.php" class="text-primary hover:underline">Reset filter</a>
                    </div>
                </div>

                <!-- Footer Active Examination Card -->
                <?php if ($pasienAktif): ?>
                    <div class="bg-primary-50/50 border border-primary-200/60 rounded-base p-5 flex items-start gap-4">
                        <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-base flex items-center justify-center shrink-0">
                            <i class="fas fa-stethoscope text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-0.5">
                                Pemeriksaan aktif: <?= e($pasienAktif['nama_pasien']) ?> &bull; Antrean #<?= (int) $pasienAktif['no_antrean'] ?>
                            </h4>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Lanjutkan pencatatan hasil pemeriksaan dan diagnosis melalui tindakan di atas. Reservasi offline dan perubahan jadwal pasien ditangani oleh admin.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- Render Modals -->
    <?= $htmlModalRingkasan ?>
    <?= $htmlModalFormPeriksa ?>
    <?= $htmlModalPerubahanJadwal ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>