<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/_load.php';

$page_title = "Obat & Stok";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

try {
    $todayDb = QueryAdminObat::todayDb() ?: date('Y-m-d');
} catch (PDOException $e) {
    $todayDb = date('Y-m-d');
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);


$search       = trim($_GET['q'] ?? '');
$filterBentuk = trim($_GET['bentuk'] ?? '');
$filterStok   = trim($_GET['stok'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$detailId     = isset($_GET['detail_id']) && ctype_digit((string) $_GET['detail_id']) ? (int) $_GET['detail_id'] : null;
$stokId       = isset($_GET['stok_id']) && ctype_digit((string) $_GET['stok_id']) ? (int) $_GET['stok_id'] : null;

$statTotal     = 0;
$statAktif     = 0;
$statPerhatian = 0;
$statMenipis   = 0;
$statHabis     = 0;
$statNonaktif  = 0;
$lastUpdated   = '-';
$listBentuk    = [];
$obatRows      = [];
$detailObat    = null;
$stokObat      = null;
$riwayatStok   = [];
$errorDb       = false;

try {
    $st = QueryAdminObat::st();

    $statTotal     = (int) ($st['total'] ?? 0);
    $statAktif     = (int) ($st['aktif'] ?? 0);
    $statNonaktif  = (int) ($st['nonaktif'] ?? 0);
    $statHabis     = (int) ($st['habis'] ?? 0);
    $statMenipis   = (int) ($st['menipis'] ?? 0);
    $statPerhatian = (int) ($st['perlu_perhatian'] ?? 0);
    if (!empty($st['last_upd'])) {
        $lastUpdated = date('d M Y, H.i', strtotime($st['last_upd'])) . ' WIB';
    }

    $listBentuk = QueryAdminObat::listBentuk();

    $obatRows = QueryDaftar::obatAdmin([
        'q' => $search, 'bentuk' => $filterBentuk,
        'stok' => $filterStok, 'status' => $filterStatus,
    ]);

    if ($detailId !== null) {
        $stmtDet = QueryAdminObat::det([':id' => $detailId]);
        $detailObat = $stmtDet->fetch();

        if ($detailObat) {
            $stmtLog = QueryAdminObat::log([':id' => $detailId]);
            $riwayatStok = $stmtLog->fetchAll();
        }
    }

    if ($stokId !== null) {
        $stmtStok = QueryAdminObat::stok([':id' => $stokId]);
        $stokObat = $stmtStok->fetch();
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[obat-admin-read] ' . $e->getMessage());
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
                        Data katalog obat belum dapat dimuat saat ini. Silakan muat ulang halaman.
                    </div>
                <?php endif; ?>

                <!-- Page Header & Action -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <p class="text-sm text-gray-600 font-medium">Kelola katalog obat dan ketersediaan stok poliklinik.</p>
                    <button type="button" data-modal-target="modal-tambah-obat" data-modal-toggle="modal-tambah-obat" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-base hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 cursor-pointer transition-colors">
                        <i class="fas fa-plus mr-2"></i> Tambah Obat
                    </button>
                </div>

                <!-- Stats Counter -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Total jenis obat</p>
                            <i class="fas fa-capsules text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($statTotal) ?></h3>
                        <p class="text-xs text-gray-500">Seluruh data obat terdaftar</p>
                    </div>
                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-emerald-900">Obat aktif</p>
                            <i class="far fa-check-circle text-emerald-700"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-emerald-800 mb-1"><?= e($statAktif) ?></h3>
                        <p class="text-xs text-emerald-800">Dapat digunakan pada resep</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-orange-800">Perlu perhatian</p>
                            <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-orange-800 mb-1"><?= e($statPerhatian) ?></h3>
                        <p class="text-xs text-orange-800"><?= e($statMenipis) ?> stok menipis &bull; <?= e($statHabis) ?> stok habis</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-base p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-semibold text-gray-600">Obat nonaktif</p>
                            <i class="fas fa-ban text-gray-400"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($statNonaktif) ?></h3>
                        <p class="text-xs text-gray-500">Tidak ditampilkan pada resep baru</p>
                    </div>
                </div>

                <!-- Main Data Card -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">

                    <!-- Card Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar inventaris obat</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Terakhir diperbarui: <?= e($lastUpdated) ?></p>
                        </div>
                        <span class="text-xs font-semibold text-primary-700 bg-primary-50 px-3 py-1 rounded-base">
                            <?= count($obatRows) ?> dari <?= e($statTotal) ?> obat
                        </span>
                    </div>

                    <!-- Filters (Method GET) -->
                    <form method="GET" action="obat.php" class="grid grid-cols-1 md:grid-cols-6 gap-3 my-4 items-center">
                        <div class="relative md:col-span-2">
                            <input type="search" name="q" value="<?= e($search) ?>" class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-base bg-gray-50/50 focus:ring-primary-500 focus:border-primary-500" placeholder="Cari nama atau kode obat..." />
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                        </div>

                        <select name="bentuk" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-base p-2.5 bg-white">
                            <option value="">Semua Bentuk</option>
                            <?php foreach ($listBentuk as $b): ?>
                                <?php $namaBentuk = (string) ($b['bentuk'] ?? ''); ?>
                                <option value="<?= e($namaBentuk) ?>" <?= $filterBentuk === $namaBentuk ? 'selected' : '' ?>>
                                    <?= e(ucfirst($namaBentuk)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="stok" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-base p-2.5 bg-white">
                            <option value="">Semua Ketersediaan</option>
                            <option value="tersedia" <?= $filterStok === 'tersedia' ? 'selected' : '' ?>>Tersedia Aman</option>
                            <option value="menipis" <?= $filterStok === 'menipis' ? 'selected' : '' ?>>Stok Menipis</option>
                            <option value="habis" <?= $filterStok === 'habis' ? 'selected' : '' ?>>Stok Habis (0)</option>
                            <option value="kadaluarsa" <?= $filterStok === 'kadaluarsa' ? 'selected' : '' ?>>Mendekati Kedaluwarsa</option>
                        </select>

                        <select name="status" onchange="this.form.submit()" class="border border-gray-300 text-gray-900 text-sm rounded-base p-2.5 bg-white">
                            <option value="">Semua Status</option>
                            <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $filterStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>

                        <div class="flex items-center gap-3">
                            <a href="obat.php" class="text-xs font-semibold text-primary-600 hover:underline">Reset filter</a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/60 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Kode</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Nama Obat</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Bentuk</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Satuan</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Stok &amp; Ketersediaan</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold">Status</th>
                                    <th scope="col" class="px-4 py-3.5 font-semibold text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($obatRows)): ?>
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                            Tidak ada data obat yang sesuai filter.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($obatRows as $row): ?>
                                        <?php
                                        $stokVal = (int) $row['stok'];
                                        $stokMin = (int) $row['stok_minimum'];
                                        $baseQ   = http_build_query([
                                            'q'      => $search,
                                            'bentuk' => $filterBentuk,
                                            'stok'   => $filterStok,
                                            'status' => $filterStatus,
                                        ]);
                                        ?>
                                        <tr class="<?= $row['is_active'] ? 'bg-white hover:bg-gray-50/60' : 'bg-gray-50 opacity-75' ?> transition-colors">
                                            <td class="px-4 py-4 text-gray-500 font-semibold"><?= e($row['kode_obat']) ?></td>
                                            <td class="px-4 py-4">
                                                <div class="font-bold text-gray-900"><?= e($row['nama_obat']) ?></div>
                                                <div class="text-xs text-gray-500">
                                                    Batas min: <?= $stokMin ?> <?= e($row['satuan']) ?>
                                                    <?php if (!empty($row['tanggal_kadaluarsa'])): ?>
                                                        &middot; Berlaku s.d. <?= e(date('d M Y', strtotime($row['tanggal_kadaluarsa']))) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 capitalize"><?= e($row['bentuk'] ?? '-') ?></td>
                                            <td class="px-4 py-4 capitalize"><?= e($row['satuan']) ?></td>
                                            <td class="px-4 py-4 font-bold text-gray-900">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span><?= $stokVal ?></span>
                                                    <?php if ($row['is_expired']): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 text-red-800 text-xs font-semibold rounded-base bg-red-100">Kedaluwarsa</span>
                                                    <?php elseif ($stokVal === 0): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 text-red-800 text-xs font-semibold rounded-base bg-red-100">Habis</span>
                                                    <?php elseif ($stokVal <= $stokMin): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-base bg-orange-100 text-orange-800">Menipis</span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 text-emerald-700 text-xs font-semibold rounded-base bg-emerald-50 border border-emerald-200">Tersedia</span>
                                                    <?php endif; ?>

                                                    <?php if (!$row['is_expired'] && $row['is_near_expired']): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded bg-amber-50 text-amber-700">Hampir Kedaluwarsa</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <?php if ($row['is_active']): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 text-emerald-700 text-xs font-semibold rounded-base bg-emerald-50 border border-emerald-200">Aktif</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 text-gray-700 text-xs font-semibold rounded-base bg-gray-200">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 font-medium text-primary-600 whitespace-nowrap text-right">
                                                <div class="flex justify-end items-center gap-3">
                                                    <a href="obat.php?<?= $baseQ ?>&detail_id=<?= (int) $row['id'] ?>" class="text-xs font-semibold hover:underline">Lihat</a>
                                                    <a href="obat.php?<?= $baseQ ?>&stok_id=<?= (int) $row['id'] ?>" class="text-xs font-semibold hover:underline">Atur Stok</a>

                                                    <form method="POST" action="obat.php" data-api-endpoint="/api/admin/obat.php" data-api-redirect="/admin/obat.php" class="inline" onsubmit="return confirm('<?= $row['is_active'] ? 'Nonaktifkan obat ini agar tidak ditampilkan pada pilihan resep baru?' : 'Aktifkan kembali obat ini?' ?>')">
                                                        <input type="hidden" name="action" value="toggle_active_obat">
                                                        <input type="hidden" name="obat_id" value="<?= (int) $row['id'] ?>">
                                                        <input type="hidden" name="new_state" value="<?= $row['is_active'] ? '0' : '1' ?>">
                                                        <button type="submit" class="text-xs px-2.5 py-1 rounded-base font-semibold cursor-pointer transition-colors <?= $row['is_active'] ? 'text-red-700 bg-red-50 hover:bg-red-600 hover:text-white' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white' ?>">
                                                            <?= $row['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                        </button>
                                                    </form>
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

    <?php require_once __DIR__ . '/components/obat/form-tambah-obat.php'; ?>
    <?php require_once __DIR__ . '/components/obat/detail-obat.php'; ?>
    <?php require_once __DIR__ . '/components/obat/atur-stok.php'; ?>

    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>