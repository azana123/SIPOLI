<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('dokter');
require_once __DIR__ . '/../config/database.php';

$page_title = "Detail Resep";
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
    $dokterProfil = null;
}

$poliIdDokter  = (int) ($dokterProfil['poli_id'] ?? 0);
$pemeriksaanId = isset($_GET['pemeriksaan_id']) && ctype_digit((string) $_GET['pemeriksaan_id']) ? (int) $_GET['pemeriksaan_id'] : null;

$daftarResepDokter = [];
$dataResep         = null;
$itemObatList      = [];
$errorDb           = false;

$bulanFullMap = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

try {
    // Ambil daftar pemeriksaan milik dokter ini yang memiliki resep obat (untuk switcher cepat)
    $stmtListRsp = $pdo->prepare("
        SELECT pm.id AS pemeriksaan_id, pm.tanggal, pm.created_at,
               ps.nama AS nama_pasien, ps.nim_nip,
               po.id AS permintaan_id, po.status AS status_resep
        FROM pemeriksaan pm
        JOIN pasien ps ON ps.id = pm.pasien_id
        LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
        WHERE (pm.dokter_id = :dok_id OR pm.poli_id = :poli_id)
          AND EXISTS (SELECT 1 FROM resep_obat ro WHERE ro.pemeriksaan_id = pm.id)
        ORDER BY pm.created_at DESC
        LIMIT 30
    ");
    $stmtListRsp->execute([
        ':dok_id'  => $dokterId,
        ':poli_id' => $poliIdDokter,
    ]);
    $daftarResepDokter = $stmtListRsp->fetchAll();

    if ($pemeriksaanId === null && !empty($daftarResepDokter)) {
        $pemeriksaanId = (int) $daftarResepDokter[0]['pemeriksaan_id'];
    }

    // Ambil detail pemeriksaan & permintaan_obat terkait
    if ($pemeriksaanId !== null) {
        $stmtDet = $pdo->prepare("
            SELECT 
                pm.*,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                d.nama AS nama_dokter,
                pol.nama AS nama_poli,
                a.reservasi_id,
                po.id AS permintaan_id,
                po.status AS status_resep,
                po.catatan AS catatan_apotek,
                po.waktu_diserahkan,
                po.created_at AS waktu_permintaan
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            JOIN poli pol  ON pol.id = pm.poli_id
            JOIN antrean a ON a.id  = pm.antrean_id
            LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
            WHERE pm.id = :id
        ");
        $stmtDet->execute([':id' => $pemeriksaanId]);
        $dataResep = $stmtDet->fetch();

        if ($dataResep) {
            $stmtItems = $pdo->prepare("
                SELECT ro.id, ro.jumlah, ro.aturan_pakai,
                       o.kode_obat, o.nama_obat, o.bentuk, o.satuan, o.keterangan
                FROM resep_obat ro
                JOIN obat o ON o.id = ro.obat_id
                WHERE ro.pemeriksaan_id = :pem_id
                ORDER BY ro.id ASC
            ");
            $stmtItems->execute([':pem_id' => $pemeriksaanId]);
            $itemObatList = $stmtItems->fetchAll();
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[detail-resep-dokter] ' . $e->getMessage());
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

<body class="bg-page antialiased text-gray-800">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar Component -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Main Content Container -->
            <main class="flex-1 min-w-0 p-6 md:p-8 space-y-6">

                <!-- Top Navigation & Selector Resep -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <a href="./riwayat-penanganan.php<?= $pemeriksaanId ? '?id=' . (int) $pemeriksaanId : '' ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke Riwayat Penanganan</span>
                    </a>

                    <?php if (count($daftarResepDokter) > 1): ?>
                        <form method="GET" action="detail-resep.php" class="flex items-center gap-2">
                            <label for="pilih_resep" class="text-xs text-gray-500 font-medium">Pilih Resep:</label>
                            <select id="pilih_resep" name="pemeriksaan_id" onchange="this.form.submit()" class="bg-white border border-gray-300 text-gray-800 text-xs rounded-base px-3 py-1.5">
                                <?php foreach ($daftarResepDokter as $dr): ?>
                                    <?php
                                    $tsDr   = strtotime($dr['created_at']);
                                    $kodeDr = 'RS-' . date('dmy', $tsDr) . '-' . str_pad((string) ($dr['permintaan_id'] ?? $dr['pemeriksaan_id']), 3, '0', STR_PAD_LEFT);
                                    ?>
                                    <option value="<?= (int) $dr['pemeriksaan_id'] ?>" <?= (int) $dr['pemeriksaan_id'] === $pemeriksaanId ? 'selected' : '' ?>>
                                        <?= e($kodeDr . ' • ' . $dr['nama_pasien'] . ' (' . date('d/m/Y', $tsDr) . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (!$dataResep): ?>
                    <div class="bg-white border border-gray-200 rounded-base p-8 text-center text-gray-500 shadow-sm">
                        Belum ada data resep obat yang tercatat untuk ditampilkan.
                    </div>
                <?php else: ?>
                    <?php
                    $lblId       = ($dataResep['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                    $kataPs      = preg_split('/\s+/', trim((string) $dataResep['nama_pasien']));
                    $inisialPs   = strtoupper(substr($kataPs[0] ?? 'P', 0, 1) . substr($kataPs[1] ?? '', 0, 1));
                    $tsRsp       = strtotime($dataResep['created_at']);
                    $tglRspFmt   = (int) date('j', $tsRsp) . ' ' . $bulanFullMap[(int) date('n', $tsRsp)] . ' ' . date('Y', $tsRsp);
                    $jamRspFmt   = date('H.i', $tsRsp) . ' WIB';
                    $kodeResep   = 'RS-' . date('dmy', $tsRsp) . '-' . str_pad((string) ($dataResep['permintaan_id'] ?? $dataResep['id']), 3, '0', STR_PAD_LEFT);
                    $kodePem     = 'PM-' . date('dmy', $tsRsp) . '-' . str_pad((string) $dataResep['id'], 3, '0', STR_PAD_LEFT);
                    $asalKunj    = !empty($dataResep['reservasi_id']) ? 'Reservasi online' : 'Kunjungan walk-in';

                    $statusResepLabel = match ($dataResep['status_resep'] ?? null) {
                        'menunggu'   => 'Menunggu Diproses',
                        'disiapkan'  => 'Diproses',
                        'diserahkan' => 'Telah Diserahkan',
                        'dibatalkan' => 'Dibatalkan',
                        default      => 'Tercatat',
                    };
                    ?>

                    <!-- Top Summary Card: Patient & Prescription Header -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-6 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                            <!-- Patient Profile -->
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-50 text-primary border border-primary-100 rounded-full font-bold text-xs flex items-center justify-center shrink-0">
                                    <?= e($inisialPs) ?>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900"><?= e($dataResep['nama_pasien']) ?></h2>
                                    <p class="text-xs text-gray-500"><?= $lblId ?> <?= e($dataResep['nim_nip']) ?> &bull; <span class="capitalize"><?= e($dataResep['status_civitas']) ?></span></p>
                                </div>
                            </div>

                            <!-- Prescription Meta -->
                            <div class="pt-3 sm:pt-0 sm:pl-6">
                                <h3 class="text-sm font-bold text-gray-900">Resep <?= e($kodeResep) ?></h3>
                                <p class="text-xs text-gray-500"><?= e($tglRspFmt) ?> &bull; <?= e($dataResep['nama_poli']) ?></p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="shrink-0">
                            <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">
                                <?= e($statusResepLabel) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Two Column Main Layout Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                        <!-- Left Column: Rincian Resep (7/12) -->
                        <div class="lg:col-span-7 bg-white border border-gray-200 rounded-base shadow-sm p-6 space-y-6">

                            <!-- Header Rincian -->
                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                <h3 class="text-base font-bold text-gray-900">Rincian resep</h3>
                                <span class="text-xs text-gray-400"><?= count($itemObatList) ?> jenis obat</span>
                            </div>

                            <!-- Info Tanggal & Dokter -->
                            <div class="grid grid-cols-2 gap-4 text-xs">
                                <div>
                                    <p class="text-gray-400 mb-1">Tanggal resep</p>
                                    <p class="font-bold text-gray-900"><?= e($tglRspFmt) ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-1">Dokter penulis</p>
                                    <p class="font-bold text-gray-900"><?= e($dataResep['nama_dokter']) ?></p>
                                </div>
                            </div>

                            <!-- Daftar Obat Card Container -->
                            <?php if (empty($itemObatList)): ?>
                                <div class="bg-gray-50/70 border border-gray-100 rounded-base p-4 text-xs text-gray-500">
                                    Tidak ada item obat yang tercatat pada pemeriksaan ini.
                                </div>
                            <?php else: ?>
                                <?php foreach ($itemObatList as $item): ?>
                                    <div class="bg-gray-50/70 border border-gray-100 rounded-base p-4 space-y-4">
                                        <!-- Title Nama Obat -->
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 bg-primary-50 text-primary-500 border border-primary-100 rounded-base flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fas fa-pills text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-400">Nama obat (<?= e($item['kode_obat']) ?>)</p>
                                                <h4 class="text-sm font-bold text-gray-900"><?= e($item['nama_obat']) ?></h4>
                                            </div>
                                        </div>

                                        <!-- Detail Spec Grid -->
                                        <div class="grid grid-cols-2 gap-4 text-xs pt-2 border-t border-gray-200/60">
                                            <div>
                                                <p class="text-gray-400 mb-0.5">Bentuk / satuan</p>
                                                <p class="font-medium text-gray-900 capitalize"><?= e(($item['bentuk'] ?? '-') . ' / ' . $item['satuan']) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-400 mb-0.5">Jumlah</p>
                                                <p class="font-medium text-gray-900"><?= (int) $item['jumlah'] ?> <?= e($item['satuan']) ?></p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-gray-400 mb-0.5">Dosis &amp; aturan pakai</p>
                                                <p class="font-medium text-gray-900"><?= e($item['aturan_pakai'] ?: 'Sesuai anjuran dokter') ?></p>
                                            </div>
                                        </div>

                                        <?php if (!empty($item['keterangan'])): ?>
                                            <div class="pt-2">
                                                <p class="text-gray-400 text-xs mb-0.5">Keterangan katalog obat</p>
                                                <p class="text-xs font-medium text-gray-700"><?= e($item['keterangan']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Catatan Resep / Apotek -->
                            <div class="text-xs">
                                <p class="text-gray-400 mb-1">Catatan pelayanan resep</p>
                                <p class="font-medium text-gray-900"><?= e($dataResep['catatan_apotek'] ?: 'Resep diteruskan ke Admin untuk penyiapan dan penyerahan obat.') ?></p>
                            </div>

                        </div>

                        <!-- Right Column: Kunjungan Terkait (5/12) -->
                        <div class="lg:col-span-5 bg-white border border-gray-200 rounded-base shadow-sm p-6 space-y-5">

                            <!-- Header Status -->
                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                <h3 class="text-base font-bold text-gray-900">Kunjungan terkait</h3>
                                <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                            </div>

                            <!-- Meta Pemeriksaan -->
                            <div class="text-xs space-y-1">
                                <h4 class="font-bold text-gray-900">Pemeriksaan <?= e($kodePem) ?></h4>
                                <p class="text-gray-500"><?= e($tglRspFmt) ?> &bull; <?= e($jamRspFmt) ?></p>
                                <p class="text-gray-500"><?= e($dataResep['nama_poli']) ?> &bull; <?= e($dataResep['nama_dokter']) ?></p>
                                <p class="text-gray-400 text-xs"><?= e($asalKunj) ?></p>
                            </div>

                            <hr class="border-gray-100">

                            <!-- Diagnosis -->
                            <div class="text-xs">
                                <h4 class="font-bold text-gray-900 mb-1">Diagnosis</h4>
                                <p class="text-gray-600"><?= e($dataResep['diagnosa'] ?: '-') ?></p>
                            </div>

                            <!-- Keluhan -->
                            <div class="text-xs">
                                <h4 class="font-bold text-gray-900 mb-1">Keluhan</h4>
                                <p class="text-gray-600 leading-relaxed"><?= e($dataResep['keluhan'] ?: '-') ?></p>
                            </div>

                            <!-- Catatan Hasil Pemeriksaan -->
                            <div class="text-xs space-y-3">
                                <h4 class="font-bold text-gray-900">Catatan hasil pemeriksaan</h4>

                                <!-- Vitals Strip Box -->
                                <div class="bg-gray-50 border border-gray-100 rounded-base p-3 text-center text-gray-700 font-medium text-xs space-y-1">
                                    <div>
                                        TD <?= !empty($dataResep['tekanan_darah']) ? e($dataResep['tekanan_darah']) . ' mmHg' : '-' ?>
                                        &bull; Suhu <?= $dataResep['suhu'] !== null ? e($dataResep['suhu']) . ' °C' : '-' ?>
                                    </div>
                                    <div>
                                        BB <?= $dataResep['berat_badan'] !== null ? e($dataResep['berat_badan']) . ' kg' : '-' ?>
                                    </div>
                                </div>

                                <p class="text-gray-600 leading-relaxed">
                                    <?= e($dataResep['catatan'] ?: '-') ?>
                                </p>
                            </div>

                            <!-- Readonly Info Footer -->
                            <div class="pt-3 border-t border-gray-100 text-xs text-primary-600 font-medium">
                                Data kunjungan selesai &bull; Hanya baca
                            </div>

                        </div>

                    </div>

                    <!-- Bottom Action Banner Card -->
                    <div class="bg-white border border-gray-200 rounded-base p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-50 border border-green-200 text-green-700 rounded-base flex items-center justify-center shrink-0">
                                <i class="far fa-clipboard text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">
                                    Status pelayanan obat: <?= e($statusResepLabel) ?>
                                    <?= !empty($dataResep['waktu_diserahkan']) ? ' (' . e(date('H.i', strtotime($dataResep['waktu_diserahkan']))) . ' WIB)' : '' ?>
                                </h4>
                                <p class="text-xs text-gray-500">Penyiapan dan penyerahan obat kepada pasien dilakukan oleh Admin.</p>
                            </div>
                        </div>
                        <a href="./riwayat-penanganan.php?id=<?= (int) $dataResep['id'] ?>" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-base px-4 py-2.5 transition-colors text-center shrink-0">
                            Kembali ke riwayat
                        </a>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>