<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('dokter');
require_once __DIR__ . '/../config/database.php';

$page_title = "Riwayat Penanganan";
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
                   p.nama AS nama_poli, p.butuh_reservasi
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

// Jalankan modal pemeriksaan terlebih dahulu
ob_start();
require_once __DIR__ . "/components/dashboard/ringkasan-pemeriksaan.php";
$htmlModalRingkasan = ob_get_clean();

ob_start();
require_once __DIR__ . "/components/dashboard/form-pemeriksaan.php";
$htmlModalFormPeriksa = ob_get_clean();

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$poliIdDokter = (int) ($dokterProfil['poli_id'] ?? 0);
$search       = trim($_GET['q'] ?? '');
$filterPoli   = trim($_GET['poli_id'] ?? '');
$filterPasien = isset($_GET['pasien_id']) && ctype_digit((string) $_GET['pasien_id']) ? (int) $_GET['pasien_id'] : 0;
$selectedId   = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : null;

$pasienAktif       = null;
$listPoli          = [];
$listPasienPernah  = [];
$riwayatRows       = [];
$detailPenanganan  = null;
$cntHariIni        = 0;
$cntSebelumnya     = 0;
$errorDb           = false;

$bulanFullMap = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$bulanSingkat = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

try {
    // Cek apakah ada pemeriksaan yang sedang aktif ('diperiksa') hari ini
    $stmtAktif = $pdo->prepare("
        SELECT a.id AS antrean_id, a.no_antrean, ps.nama AS nama_pasien,
               COALESCE(j.jam_mulai, (a.waktu_daftar AT TIME ZONE 'Asia/Jakarta')::time) AS jam_kunjungan,
               COALESCE(r.keluhan, 'Pemeriksaan langsung') AS keluhan_awal
        FROM antrean a
        JOIN pasien ps ON ps.id = a.pasien_id
        LEFT JOIN jadwal j    ON j.id = a.jadwal_id
        LEFT JOIN reservasi r ON r.id = a.reservasi_id
        WHERE a.tanggal = :today
          AND a.poli_id = :poli_id
          AND (a.dokter_id = :dok_id OR a.dokter_id IS NULL)
          AND a.status = 'diperiksa'
        ORDER BY a.no_antrean ASC
        LIMIT 1
    ");
    $stmtAktif->execute([
        ':today'   => $todayDb,
        ':poli_id' => $poliIdDokter,
        ':dok_id'  => $dokterId,
    ]);
    $pasienAktif = $stmtAktif->fetch();

    // Opsi filter poli & daftar pasien yang pernah diperiksa
    $listPoli = $pdo->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();
    $stmtPsList = $pdo->prepare("
        SELECT DISTINCT ps.id, ps.nama, ps.nim_nip
        FROM pemeriksaan pm
        JOIN pasien ps ON ps.id = pm.pasien_id
        WHERE pm.dokter_id = :dok_id OR pm.poli_id = :poli_id
        ORDER BY ps.nama ASC
    ");
    $stmtPsList->execute([':dok_id' => $dokterId, ':poli_id' => $poliIdDokter]);
    $listPasienPernah = $stmtPsList->fetchAll();

    // Query daftar riwayat pemeriksaan
    $where  = ["(pm.dokter_id = :dok_id OR pm.poli_id = :poli_default)"];
    $params = [
        ':dok_id'       => $dokterId,
        ':poli_default' => $poliIdDokter,
    ];

    if ($search !== '') {
        $where[]      = "(ps.nama ILIKE :q OR ps.nim_nip ILIKE :q OR COALESCE(pm.diagnosa, '') ILIKE :q)";
        $params[':q'] = '%' . $search . '%';
    }
    if ($filterPoli !== '' && ctype_digit($filterPoli)) {
        $where[]            = "pm.poli_id = :f_poli";
        $params[':f_poli']  = (int) $filterPoli;
    }
    if ($filterPasien > 0) {
        $where[]             = "pm.pasien_id = :f_pasien";
        $params[':f_pasien'] = $filterPasien;
    }

    $sqlRiwayat = "
        SELECT 
            pm.id,
            pm.antrean_id,
            pm.pasien_id,
            pm.tanggal,
            pm.keluhan,
            pm.diagnosa,
            pm.created_at,
            ps.nama AS nama_pasien,
            ps.nim_nip,
            ps.status_civitas,
            pol.nama AS nama_poli,
            d.nama AS nama_dokter
        FROM pemeriksaan pm
        JOIN pasien ps ON ps.id = pm.pasien_id
        JOIN poli pol  ON pol.id = pm.poli_id
        JOIN dokter d  ON d.id  = pm.dokter_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY pm.tanggal DESC, pm.created_at DESC
        LIMIT 50
    ";
    $stmtRw = $pdo->prepare($sqlRiwayat);
    $stmtRw->execute($params);
    $riwayatRows = $stmtRw->fetchAll();

    foreach ($riwayatRows as $rw) {
        if ($rw['tanggal'] === $todayDb) {
            $cntHariIni++;
        } else {
            $cntSebelumnya++;
        }
    }

    if ($selectedId === null && !empty($riwayatRows)) {
        $selectedId = (int) $riwayatRows[0]['id'];
    }

    // Ambil detail lengkap untuk pemeriksaan yang dipilih di kolom kanan
    if ($selectedId !== null) {
        $stmtDet = $pdo->prepare("
            SELECT 
                pm.*,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                pol.nama AS nama_poli,
                d.nama AS nama_dokter,
                a.reservasi_id,
                po.id AS permintaan_obat_id,
                po.status AS status_resep,
                po.waktu_diserahkan,
                po.created_at AS waktu_resep,
                (SELECT COUNT(*) FROM resep_obat ro WHERE ro.pemeriksaan_id = pm.id) AS jumlah_jenis_obat,
                (SELECT COUNT(*) FROM pemeriksaan p2 WHERE p2.pasien_id = pm.pasien_id) AS total_kunjungan_pasien
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN poli pol  ON pol.id = pm.poli_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            JOIN antrean a ON a.id  = pm.antrean_id
            LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
            WHERE pm.id = :id
        ");
        $stmtDet->execute([':id' => $selectedId]);
        $detailPenanganan = $stmtDet->fetch();
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[riwayat-penanganan-dokter] ' . $e->getMessage());
}

$hasDetail = !empty($detailPenanganan);
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

                <!-- Banner Pemeriksaan Aktif (Muncul jika ada pasien sedang diperiksa) -->
                <?php if ($pasienAktif): ?>
                    <div class="bg-white border border-gray-200 rounded-base p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-primary-50 border border-primary-100 text-primary-600 rounded-base flex items-center justify-center shrink-0">
                                <i class="fas fa-stethoscope text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Pemeriksaan aktif &bull; <?= e($pasienAktif['nama_pasien']) ?></h2>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?= e(str_replace(':', '.', substr($pasienAktif['jam_kunjungan'], 0, 5))) ?> WIB &bull; <?= e($pasienAktif['keluhan_awal']) ?> &bull; Hasil pemeriksaan belum selesai
                                </p>
                            </div>
                        </div>
                        <a href="riwayat-penanganan.php?periksa_antrean_id=<?= (int) $pasienAktif['antrean_id'] ?>" class="bg-primary text-white hover:bg-primary-700 rounded-base px-4 py-2.5 text-xs font-semibold flex items-center justify-center gap-2 transition-colors cursor-pointer shrink-0">
                            <i class="fas fa-notes-medical"></i> Catat Pemeriksaan &amp; Diagnosis
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Filter Controls Bar (Method GET) -->
                <form method="GET" action="riwayat-penanganan.php" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 flex-1">
                        <!-- Input Search -->
                        <div class="relative">
                            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama, NIM/NIP, atau diagnosis" class="w-full pl-4 pr-10 py-2 bg-white border border-gray-300 rounded-base text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-700 placeholder-gray-400">
                            <button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs cursor-pointer">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        <!-- Select Poli -->
                        <div class="relative">
                            <select name="poli_id" onchange="this.form.submit()" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 appearance-none pr-8 cursor-pointer">
                                <option value="">Semua Poli</option>
                                <?php foreach ($listPoli as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" <?= $filterPoli === (string) $p['id'] ? 'selected' : '' ?>>
                                        <?= e($p['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>

                        <!-- Select Pasien -->
                        <div class="relative">
                            <select name="pasien_id" onchange="this.form.submit()" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-base text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 appearance-none pr-8 cursor-pointer">
                                <option value="0">Semua pasien</option>
                                <?php foreach ($listPasienPernah as $psItem): ?>
                                    <option value="<?= (int) $psItem['id'] ?>" <?= $filterPasien === (int) $psItem['id'] ? 'selected' : '' ?>>
                                        <?= e($psItem['nama']) ?> (<?= e($psItem['nim_nip']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>

                        <!-- Reset -->
                        <div class="flex items-center">
                            <a href="riwayat-penanganan.php" class="text-xs font-semibold text-primary hover:underline px-2 py-2">Reset filter</a>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 font-medium text-right self-end lg:self-center shrink-0">
                        <?= count($riwayatRows) ?> kunjungan
                    </div>
                </form>

                <!-- Main Content Grid (2 Columns) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Left Column: Daftar Riwayat Kunjungan -->
                    <div id="left-card-penanganan" class="<?= $hasDetail ? 'lg:col-span-5' : 'lg:col-span-12' ?> flex flex-col">
                        <div class="bg-white border border-gray-200 rounded-base shadow-sm flex-1 flex flex-col">
                            <div class="p-5 border-b border-gray-100">
                                <h3 class="text-base font-bold text-gray-900">Riwayat kunjungan</h3>
                                <p class="text-xs text-gray-500 mt-0.5"><?= e($cntHariIni) ?> hari ini &bull; <?= e($cntSebelumnya) ?> kunjungan sebelumnya</p>
                            </div>

                            <!-- Visit Items List -->
                            <div class="divide-y divide-gray-100 flex-1 max-h-[650px] overflow-y-auto">
                                <?php if (empty($riwayatRows)): ?>
                                    <div class="p-8 text-center text-sm text-gray-500">
                                        Belum ada riwayat penanganan yang sesuai filter.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($riwayatRows as $rw): ?>
                                        <?php
                                        $isSelected = ((int) $rw['id'] === $selectedId);
                                        $lblId      = ($rw['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                                        $tsRw       = strtotime($rw['created_at']);
                                        $tglRwFmt   = date('d', $tsRw) . ' ' . $bulanSingkat[(int) date('n', $tsRw)] . ' ' . date('Y', $tsRw) . ' • ' . date('H.i', $tsRw) . ' WIB';
                                        $linkItem   = 'riwayat-penanganan.php?' . http_build_query([
                                            'q'         => $search,
                                            'poli_id'   => $filterPoli,
                                            'pasien_id' => $filterPasien > 0 ? $filterPasien : null,
                                            'id'        => (int) $rw['id'],
                                        ]);
                                        ?>
                                        <a href="<?= e($linkItem) ?>" class="block p-4 transition-colors cursor-pointer <?= $isSelected ? 'bg-primary-50/40 border-l-4 border-l-primary' : 'hover:bg-gray-50' ?>">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-bold text-gray-900 text-sm"><?= e($rw['nama_pasien']) ?></h4>
                                                <?php if ($isSelected): ?>
                                                    <i class="fas fa-chevron-right text-sm text-primary-600"></i>
                                                <?php else: ?>
                                                    <i class="far fa-check-circle text-base text-emerald-600"></i>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-gray-400 mb-1"><?= $lblId ?> <?= e($rw['nim_nip']) ?></p>
                                            <p class="text-xs text-gray-500 mb-2"><?= e($tglRwFmt) ?></p>
                                            <p class="text-xs font-bold <?= $isSelected ? 'text-primary-600' : 'text-gray-800' ?> mb-1"><?= e($rw['diagnosa'] ?: 'Pemeriksaan umum') ?></p>
                                            <p class="text-[11px] text-gray-400">Selesai &bull; <?= e($rw['nama_poli']) ?></p>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Left Footer -->
                            <div class="p-4 border-t border-gray-100 text-[11px] text-gray-400 leading-relaxed">
                                <p>Menampilkan <?= count($riwayatRows) ?> kunjungan selesai.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Detail Penanganan Pasien -->
                    <?php if ($hasDetail): ?>
                        <?php
                        $lblIdDet    = ($detailPenanganan['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                        $kataPs      = preg_split('/\s+/', trim((string) $detailPenanganan['nama_pasien']));
                        $inisialPs   = strtoupper(substr($kataPs[0] ?? 'P', 0, 1) . substr($kataPs[1] ?? '', 0, 1));
                        $tsDet       = strtotime($detailPenanganan['created_at']);
                        $tglDetFmt   = (int) date('j', $tsDet) . ' ' . $bulanFullMap[(int) date('n', $tsDet)] . ' ' . date('Y', $tsDet) . ' • ' . date('H.i', $tsDet) . ' WIB';
                        $kodePem     = 'PM-' . date('dmy', $tsDet) . '-' . str_pad((string) $detailPenanganan['id'], 3, '0', STR_PAD_LEFT);
                        $kodeResep   = 'RS-' . date('dmy', $tsDet) . '-' . str_pad((string) ($detailPenanganan['permintaan_obat_id'] ?? $detailPenanganan['id']), 3, '0', STR_PAD_LEFT);
                        $asalDet     = !empty($detailPenanganan['reservasi_id']) ? 'Reservasi online' : 'Kunjungan walk-in';
                        $statusResepDet = match ($detailPenanganan['status_resep'] ?? null) {
                            'menunggu'   => 'Menunggu',
                            'disiapkan'  => 'Diproses',
                            'diserahkan' => 'Diserahkan',
                            'dibatalkan' => 'Dibatalkan',
                            default      => 'Tanpa Resep',
                        };
                        ?>
                        <div id="right-card-penanganan" class="lg:col-span-7">
                            <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">

                                <!-- Detail Header -->
                                <div class="flex items-center justify-between mb-5">
                                    <h3 class="text-lg font-bold text-gray-900">Detail penanganan</h3>
                                    <div class="flex gap-3 items-center">
                                        <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                                    </div>
                                </div>

                                <!-- Patient Info Box -->
                                <div class="bg-gray-50/70 border border-gray-100 rounded-base p-4 mb-6 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-primary-100 text-primary-700 rounded-full font-bold flex items-center justify-center text-sm shrink-0">
                                            <?= e($inisialPs) ?>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm"><?= e($detailPenanganan['nama_pasien']) ?></h4>
                                            <p class="text-xs text-gray-500"><?= $lblIdDet ?> <?= e($detailPenanganan['nim_nip']) ?> &bull; <span class="capitalize"><?= e($detailPenanganan['status_civitas']) ?></span></p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700"><?= (int) $detailPenanganan['total_kunjungan_pasien'] ?> kunjungan</span>
                                </div>

                                <!-- Metadata Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-6 mb-6 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Tanggal penanganan</p>
                                        <p class="text-sm font-bold text-gray-900"><?= e($tglDetFmt) ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5">Pemeriksaan <?= e($kodePem) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 mb-1">Poli / dokter</p>
                                        <p class="text-sm font-bold text-gray-900"><?= e($detailPenanganan['nama_poli']) ?> &bull; <?= e($detailPenanganan['nama_dokter']) ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5"><?= e($asalDet) ?></p>
                                    </div>
                                </div>

                                <!-- Section: Keluhan -->
                                <div class="mb-6">
                                    <h5 class="text-sm font-bold text-gray-900 mb-2">Keluhan</h5>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        <?= e($detailPenanganan['keluhan'] ?: 'Tidak ada keluhan awal tercatat.') ?>
                                    </p>
                                </div>

                                <!-- Section: Diagnosis -->
                                <div class="mb-6">
                                    <h5 class="text-sm font-bold text-gray-900 mb-2">Diagnosis</h5>
                                    <p class="text-sm text-gray-700 font-medium">
                                        <?= e($detailPenanganan['diagnosa'] ?: '-') ?>
                                    </p>
                                </div>

                                <!-- Section: Catatan Hasil Pemeriksaan -->
                                <div class="mb-6">
                                    <h5 class="text-sm font-bold text-gray-900 mb-3">Catatan hasil pemeriksaan</h5>

                                    <!-- Vitals Grid -->
                                    <div class="bg-gray-50/80 border border-gray-200/60 rounded-base p-3 mb-3 grid grid-cols-3 text-center text-xs text-gray-700 font-medium">
                                        <div>TD <?= !empty($detailPenanganan['tekanan_darah']) ? e($detailPenanganan['tekanan_darah']) . ' mmHg' : '-' ?></div>
                                        <div class="border-x border-gray-200">Suhu <?= $detailPenanganan['suhu'] !== null ? e($detailPenanganan['suhu']) . ' °C' : '-' ?></div>
                                        <div>BB <?= $detailPenanganan['berat_badan'] !== null ? e($detailPenanganan['berat_badan']) . ' kg' : '-' ?></div>
                                    </div>

                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        <?= e($detailPenanganan['catatan'] ?: '-') ?>
                                    </p>
                                    <?php if (!empty($detailPenanganan['tindak_lanjut'])): ?>
                                        <p class="text-xs text-primary-700 font-medium mt-2">
                                            Saran / Tindak lanjut: <?= e($detailPenanganan['tindak_lanjut']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Section: Resep Box -->
                                <div class="bg-gray-50/70 border border-gray-200/70 rounded-base p-4 mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h6 class="font-bold text-gray-900 text-sm">
                                            <?= (int) $detailPenanganan['jumlah_jenis_obat'] > 0 ? 'Resep ' . e($kodeResep) : 'Tidak ada resep obat' ?>
                                        </h6>
                                        <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">
                                            <?= e($statusResepDet) ?>
                                        </span>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <p class="text-xs text-gray-500">
                                            <?= (int) $detailPenanganan['jumlah_jenis_obat'] ?> jenis obat
                                            <?= !empty($detailPenanganan['waktu_diserahkan']) ? ' • Diserahkan pukul ' . e(date('H.i', strtotime($detailPenanganan['waktu_diserahkan']))) . ' WIB' : '' ?>
                                        </p>
                                        <?php if ((int) $detailPenanganan['jumlah_jenis_obat'] > 0): ?>
                                            <a href="./detail-resep.php?pemeriksaan_id=<?= (int) $detailPenanganan['id'] ?>" class="border border-gray-300 text-primary-600 bg-white hover:bg-gray-50 rounded-base px-3.5 py-1.5 text-xs font-semibold transition-colors shadow-sm cursor-pointer self-start sm:self-auto">
                                                Lihat resep
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Detail Footer -->
                                <div class="text-[11px] text-gray-400">
                                    Rekam medis pemeriksaan pasien tersimpan di database SIPOLI.
                                </div>

                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </main>
        </div>
    </div>

    <!-- Render Modals -->
    <?= $htmlModalRingkasan ?>
    <?= $htmlModalFormPeriksa ?>

    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>