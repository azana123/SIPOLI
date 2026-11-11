<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('dokter');
require_once __DIR__ . '/../config/database.php';

$page_title = "Dashboard Dokter";
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
    $dokterProfil = null;
}

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

$poliIdDokter    = (int) ($dokterProfil['poli_id'] ?? 0);
$namaPoliDokter  = $dokterProfil['nama_poli'] ?? 'Poli';
$jadwalHariIni   = null;
$cntTerjadwal    = 0;
$cntMenunggu     = 0;
$cntDiperiksa    = 0;
$cntSelesai      = 0;
$pasienHariIni   = [];
$pasienAktif     = null;
$resepMenunggu   = 0;
$resepDiproses   = 0;
$errorDb         = false;

try {
    // Jadwal praktik dokter hari ini
    $stmtJdw = $pdo->prepare("
        SELECT id, tanggal, jam_mulai, jam_selesai, kuota, status
        FROM jadwal
        WHERE dokter_id = :dok_id AND tanggal = fn_today() AND status <> 'tutup'
        ORDER BY jam_mulai ASC
        LIMIT 1
    ");
    $stmtJdw->execute([':dok_id' => $dokterId]);
    $jadwalHariIni =$stmtJdw->fetch();

    // Daftar antrean & kunjungan pasien hari ini untuk dokter/poli ini
    $stmtList =$pdo->prepare("
        SELECT 
            a.id AS antrean_id,
            a.no_antrean,
            a.status,
            a.waktu_daftar,
            a.waktu_mulai,
            a.waktu_selesai,
            ps.id AS pasien_id,
            ps.nama AS nama_pasien,
            ps.nim_nip,
            ps.status_civitas,
            COALESCE(j.jam_mulai, (a.waktu_daftar AT TIME ZONE 'Asia/Jakarta')::time) AS jam_kunjungan,
            COALESCE(pm.keluhan, r.keluhan, 'Pemeriksaan langsung') AS keluhan_awal,
            pm.id AS pemeriksaan_id
        FROM antrean a
        JOIN pasien ps ON ps.id = a.pasien_id
        LEFT JOIN jadwal j      ON j.id = a.jadwal_id
        LEFT JOIN reservasi r   ON r.id = a.reservasi_id
        LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
        WHERE a.tanggal = fn_today()
          AND a.poli_id = :poli_id
          AND (a.dokter_id = :dok_id OR a.dokter_id IS NULL)
          AND a.status <> 'batal'
        ORDER BY 
            CASE a.status
                WHEN 'diperiksa' THEN 1
                WHEN 'menunggu'  THEN 2
                WHEN 'selesai'   THEN 3
                ELSE 4
            END ASC,
            a.no_antrean ASC
    ");
    $stmtList->execute([
        ':poli_id' => $poliIdDokter,
        ':dok_id'  => $dokterId,
    ]);
    $pasienHariIni =$stmtList->fetchAll();

    $cntTerjadwal = count($pasienHariIni);
    foreach ($pasienHariIni as$pRow) {
        if ($pRow['status'] === 'menunggu') {$cntMenunggu++;
        } elseif ($pRow['status'] === 'diperiksa') {$cntDiperiksa++;
            if ($pasienAktif === null) {
                $pasienAktif =$pRow;
            }
        } elseif ($pRow['status'] === 'selesai') {$cntSelesai++;
        }
    }

    // Jika belum ada yang berstatus 'diperiksa', ambil pasien 'menunggu' urutan pertama untuk kartu bawah
    if ($pasienAktif === null) {
        foreach ($pasienHariIni as$pRow) {
            if ($pRow['status'] === 'menunggu') {
                $pasienAktif =$pRow;
                break;
            }
        }
    }

    // Statistik Resep Hari Ini yang ditulis oleh dokter ini
    $stmtRsp =$pdo->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE po.status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE po.status IN ('disiapkan', 'diserahkan')) AS diproses
        FROM permintaan_obat po
        JOIN pemeriksaan pm ON pm.id = po.pemeriksaan_id
        WHERE pm.dokter_id = :dok_id AND pm.tanggal = fn_today()
    ");
    $stmtRsp->execute([':dok_id' =>$dokterId]);
    $rspRow =$stmtRsp->fetch();
    $resepMenunggu = (int) ($rspRow['menunggu'] ?? 0);
    $resepDiproses = (int) ($rspRow['diproses'] ?? 0);

} catch (PDOException $e) {$errorDb = true;
    error_log('[dashboard-dokter] ' . $e->getMessage());
}

$jamPraktikBanner =$jadwalHariIni
    ? str_replace(':', '.', substr($jadwalHariIni['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($jadwalHariIni['jam_selesai'], 0, 5)) . ' WIB'
    : 'Sesuai jam operasional poliklinik';
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
                <?php if ($errorDb): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        Data dashboard gagal dimuat. Periksa log server.
                    </div>
                <?php endif; ?>

                <!-- Banner Praktik -->
                <div class="bg-white border border-gray-200 rounded-base p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 md:w-12 md:h-12 bg-primary-50 border border-primary-100 text-primary rounded-base flex items-center justify-center shrink-0">
                            <i class="far fa-calendar-check text-base md:text-xl"></i>
                        </div>
                        <div>
                            <div class="flex flex-col-reverse md:flex-row md:items-center md:gap-3 mb-1">
                                <h2 class="text-lg font-bold text-gray-900">Praktik saya hari ini</h2>
                                <?php if ($jadwalHariIni): ?>
                                    <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Sedang Berlangsung</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 text-gray-700 text-xs font-medium rounded bg-gray-100">Layanan Harian</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500"><?= e($namaPoliDokter) ?> &bull; <?= e($jamPraktikBanner) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-col md:items-end">
                        <button type="button" data-modal-target="modal-informasi-perubahan" data-modal-toggle="modal-informasi-perubahan" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 mb-2 transition-colors cursor-pointer">
                            <i class="far fa-comment-alt"></i> Informasikan perubahan jadwal
                        </button>
                        <p class="text-xs text-gray-500 text-center md:text-left">Perubahan jadwal dikonfirmasi oleh admin</p>
                    </div>
                </div>

                <!-- Counters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Terjadwal -->
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Pasien hari ini</p>
                            <i class="fas fa-user-friends text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($cntTerjadwal) ?></h3>
                        <p class="text-xs text-gray-500">Kunjungan sesi hari ini</p>
                    </div>
                    <!-- Menunggu -->
                    <div class="bg-yellow-100 border border-yellow-300 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Menunggu pemeriksaan</p>
                            <i class="far fa-clock text-yellow-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-yellow-700 mb-1"><?= e($cntMenunggu) ?></h3>
                        <p class="text-xs text-gray-500">Pasien dalam antrean saya</p>
                    </div>
                    <!-- Diperiksa -->
                    <div class="bg-primary-50/50 border border-primary-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Sedang diperiksa</p>
                            <i class="fas fa-stethoscope text-primary-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-primary mb-1"><?= e($cntDiperiksa) ?></h3>
                        <p class="text-xs text-gray-500">Pemeriksaan belum selesai</p>
                    </div>
                    <!-- Selesai -->
                    <div class="bg-green-50/50 border border-green-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-700">Pemeriksaan selesai</p>
                            <i class="far fa-check-circle text-green-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-green-700 mb-1"><?= e($cntSelesai) ?></h3>
                        <p class="text-xs text-gray-500">Penanganan telah dicatat</p>
                    </div>
                </div>

                <!-- Tabel Pasien -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm mb-6">
                    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Pasien saya hari ini</h3>
                            <p class="text-sm text-gray-500">Antrean <?= e($namaPoliDokter) ?> &bull; <?= e($jamPraktikBanner) ?></p>
                        </div>
                        <a href="./daftar-pasien.php" class="text-primary hover:text-primary-700 text-sm font-medium mt-3 md:mt-0 flex items-center gap-1">
                            Buka daftar pasien <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-gray-50/50 border-b border-gray-200 uppercase">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">Pasien / NIM atau NIP</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Jam / Antrean</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Keluhan awal</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($pasienHariIni)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            Belum ada pasien dalam antrean untuk hari ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pasienHariIni as$row): ?>
                                        <?php
                                        $lblId = ($row['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';$jamFmt = !empty($row['jam_kunjungan']) ? str_replace(':', '.', substr($row['jam_kunjungan'], 0, 5)) : '-';
                                        $isBeingExamined = ($row['status'] === 'diperiksa');
                                        ?>
                                        <tr class="<?= $isBeingExamined ? 'bg-primary-50/20 hover:bg-primary-50/40' : 'hover:bg-gray-50/50' ?>">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900"><?= e($row['nama_pasien']) ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?= $lblId ?> <?= e($row['nim_nip']) ?> &bull; <span class="capitalize"><?= e($row['status_civitas']) ?></span></div>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                <div><?= e($jamFmt) ?></div>
                                                <div class="text-xs text-gray-400">Antrean #<?= (int) $row['no_antrean'] ?></div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-500"><?= e($row['keluhan_awal']) ?></td>
                                            <td class="px-6 py-4">
                                                <?php if ($row['status'] === 'selesai'): ?>
                                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                                                <?php elseif ($row['status'] === 'diperiksa'): ?>
                                                    <span class="text-primary-600 font-semibold text-sm">Diperiksa</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <?php if ($row['status'] === 'selesai'): ?>
                                                    <a href="dashboard.php?ringkasan_antrean_id=<?= (int) $row['antrean_id'] ?>" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors inline-block">
                                                        Lihat Riwayat
                                                    </a>
                                                <?php elseif ($row['status'] === 'diperiksa'): ?>
                                                    <a href="dashboard.php?periksa_antrean_id=<?= (int) $row['antrean_id'] ?>" class="bg-primary-500 text-white hover:bg-primary-600 rounded-base px-3 py-1.5 text-sm font-medium inline-flex items-center gap-1 transition-colors">
                                                        <i class="fas fa-arrow-right text-xs"></i> Lanjutkan
                                                    </a>
                                                <?php else: ?>
                                                    <a href="dashboard.php?periksa_antrean_id=<?= (int) $row['antrean_id'] ?>" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3 py-1.5 text-sm font-medium transition-colors inline-block">
                                                        Mulai Pemeriksaan
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                        <span>Menampilkan seluruh <?= count($pasienHariIni) ?> pasien pada antrean hari ini</span>
                        <span>Terhubung ke Database</span>
                    </div>
                </div>

                <!-- Bottom Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Lanjutkan Pemeriksaan -->
                    <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">
                                <?= ($pasienAktif &&$pasienAktif['status'] === 'diperiksa') ? 'Lanjutkan pemeriksaan' : 'Mulai pemeriksaan berikutnya' ?>
                            </h3>
                            <?php if ($pasienAktif): ?>
                                <span class="text-sm font-medium text-primary">
                                    <?= e($pasienAktif['nama_pasien']) ?> &bull; Antrean #<?= (int) $pasienAktif['no_antrean'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">
                            <?= $pasienAktif
                                ? 'Lengkapi hasil pemeriksaan dan diagnosis untuk ' . e($pasienAktif['nama_pasien']) . ', lalu buat resep bila diperlukan.'
                                : 'Saat ini tidak ada pasien yang sedang menunggu atau diperiksa.' ?>
                        </p>
                        <?php if ($pasienAktif): ?>
                            <div class="flex flex-wrap gap-3">
                                <a href="dashboard.php?periksa_antrean_id=<?= (int) $pasienAktif['antrean_id'] ?>" class="bg-primary text-white hover:bg-primary-700 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-colors">
                                    <i class="fas fa-notes-medical"></i> Catat Pemeriksaan &amp; Diagnosis
                                </a>
                                <a href="dashboard.php?periksa_antrean_id=<?= (int) $pasienAktif['antrean_id'] ?>#container-resep-obat" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-colors">
                                    <i class="fas fa-prescription-bottle-alt"></i> Buat Resep
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Resep Hari Ini -->
                    <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Resep hari ini</h3>
                            <a href="./detail-resep.php" class="text-primary hover:text-primary-700 text-sm font-medium flex items-center gap-1">
                                Lihat resep <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                        <ul class="space-y-4 mb-6">
                            <li class="flex items-center gap-3 text-sm text-gray-600">
                                <i class="far fa-paper-plane text-primary-500"></i>
                                <span><?= e($resepMenunggu) ?> resep menunggu diproses admin</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-gray-600">
                                <i class="far fa-check-circle text-green-700"></i>
                                <span><?= e($resepDiproses) ?> resep telah diproses admin</span>
                            </li>
                        </ul>
                        <p class="text-xs text-gray-400">Resep digital diteruskan ke admin untuk pelayanan obat.</p>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <!-- Render Ketiga Modal -->
    <?= $htmlModalRingkasan ?>
    <?= $htmlModalFormPeriksa ?>
    <?= $htmlModalPerubahanJadwal ?>

    <!-- Script Flowbite -->
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>