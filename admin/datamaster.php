<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/_load.php';

$page_title = "Datamaster";
$app_name   = "SIPOLI";
$errorDb    = false;

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$isSuperAdmin   = false;
$currentAdminId = (int) ($_SESSION['profil_id'] ?? 0);
try {
    if ($currentAdminId > 0) {
        $stmtSuper = QueryAdminDatamaster::super([':id' => $currentAdminId]);
        $admRow = $stmtSuper->fetch();
        $isSuperAdmin = !empty($admRow['is_super']);
    }
} catch (PDOException $e) {
    $isSuperAdmin = false;
}

$validTabs = ['pasien', 'tenaga-layanan', 'poli', 'admin'];
$activeTab = isset($_GET['tab']) && in_array($_GET['tab'], $validTabs, true) ? $_GET['tab'] : 'pasien';

$search   = trim($_GET['q'] ?? '');
$status   = trim($_GET['status'] ?? '');
$civitas  = trim($_GET['civitas'] ?? '');
$poliId   = trim($_GET['poli_id'] ?? '');
$reserv   = trim($_GET['reservasi'] ?? '');
$tipeAdm  = trim($_GET['tipe_admin'] ?? '');
$detailId = isset($_GET['detail_id']) && ctype_digit((string) $_GET['detail_id']) ? (int) $_GET['detail_id'] : null;
$editId   = isset($_GET['edit_id']) && ctype_digit((string) $_GET['edit_id']) ? (int) $_GET['edit_id'] : null;

$totalPasien  = 0;
$totalDokter  = 0;
$totalPoli    = 0;
$totalAdmin   = 0;
$rows         = [];
$listPoli     = [];

$detailPasien = null;
$editPasien   = null;
$detailDokter = null;
$editDokter   = null;
$editPoli     = null;
$detailAdmin  = null;
$editAdmin    = null;

try {
    $stats = QueryAdminDatamaster::stats();

    $totalAdmin  = (int) ($stats['total_admin'] ?? 0);
    $totalPasien = (int) ($stats['total_pasien'] ?? 0);
    $totalDokter = (int) ($stats['total_dokter'] ?? 0);
    $totalPoli   = (int) ($stats['total_poli'] ?? 0);

    if ($activeTab === 'pasien') {
        $rows = QueryDaftar::datamaster($activeTab, [
        'q' => $search, 'status' => $status, 'civitas' => $civitas,
        'poli_id' => $poliId, 'reservasi' => $reserv, 'tipe_admin' => $tipeAdm,
    ]);

        if ($detailId !== null) {
            $stmtDet = QueryAdminDatamaster::det([':id' => $detailId]);
            $detailPasien = $stmtDet->fetch();
        }
        if ($editId !== null) {
            $stmtEd = QueryAdminDatamaster::ed([':id' => $editId]);
            $editPasien = $stmtEd->fetch();
        }

    } elseif ($activeTab === 'tenaga-layanan') {
        $listPoli = QueryAdminDatamaster::listPoli();

        if ($search !== '') {
            $where[]      = "(d.nama ILIKE :q OR COALESCE(d.no_str, '') ILIKE :q OR COALESCE(d.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if ($poliId !== '' && ctype_digit($poliId)) {
            $where[]            = "d.poli_id = :poli_id";
            $params[':poli_id'] = (int) $poliId;
        }
        if ($status === 'aktif') {
            $where[] = "(d.is_active = TRUE AND u.is_active = TRUE)";
        } elseif ($status === 'non-aktif') {
            $where[] = "(d.is_active = FALSE OR u.is_active = FALSE)";
        }

        $rows = QueryDaftar::datamaster($activeTab, [
            'q' => $search, 'status' => $status, 'civitas' => $civitas,
            'poli_id' => $poliId, 'reservasi' => $reserv, 'tipe_admin' => $tipeAdm,
        ]);

        if ($detailId !== null) {
            $stmtDet = QueryAdminDatamaster::det2([':id' => $detailId]);
            $detailDokter = $stmtDet->fetch();
        }
        if ($editId !== null) {
            $stmtEd = QueryAdminDatamaster::ed2([':id' => $editId]);
            $editDokter = $stmtEd->fetch();
        }

    } elseif ($activeTab === 'poli') {
        if ($search !== '') {
            $where[]      = "po.nama ILIKE :q";
            $params[':q'] = '%' . $search . '%';
        }
        if ($reserv === 'ya') {
            $where[] = "po.butuh_reservasi = TRUE";
        } elseif ($reserv === 'tidak') {
            $where[] = "po.butuh_reservasi = FALSE";
        }

        $rows = QueryDaftar::datamaster($activeTab, [
            'q' => $search, 'status' => $status, 'civitas' => $civitas,
            'poli_id' => $poliId, 'reservasi' => $reserv, 'tipe_admin' => $tipeAdm,
        ]);

        if ($editId !== null) {
            $stmtEd = QueryAdminDatamaster::ed3([':id' => $editId]);
            $editPoli = $stmtEd->fetch();
        }

    } elseif ($activeTab === 'admin') {
        if ($search !== '') {
            $where[]      = "(a.nama ILIKE :q OR COALESCE(a.nim_nip, '') ILIKE :q OR COALESCE(a.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if ($tipeAdm === 'super') {
            $where[] = "a.is_super = TRUE";
        } elseif ($tipeAdm === 'regular') {
            $where[] = "a.is_super = FALSE";
        }
        if ($status === 'aktif') {
            $where[] = "u.is_active = TRUE";
        } elseif ($status === 'non-aktif') {
            $where[] = "u.is_active = FALSE";
        }

        $rows = QueryDaftar::datamaster($activeTab, [
            'q' => $search, 'status' => $status, 'civitas' => $civitas,
            'poli_id' => $poliId, 'reservasi' => $reserv, 'tipe_admin' => $tipeAdm,
        ]);

        if ($detailId !== null) {
            $stmtDet = QueryAdminDatamaster::det3([':id' => $detailId]);
            $detailAdmin = $stmtDet->fetch();
        }
        if ($editId !== null) {
            $stmtEd = QueryAdminDatamaster::ed4([':id' => $editId]);
            $editAdmin = $stmtEd->fetch();
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[datamaster-admin] ' . $e->getMessage());
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
                        Data master belum dapat dimuat saat ini. Silakan muat ulang halaman.
                    </div>
                <?php endif; ?>

                <div class="mb-4 text-sm text-gray-600 font-medium">
                    <p>Kelola data civitas akademika, tenaga layanan, dan poli &middot; Politeknik Negeri Malang</p>
                </div>

                <!-- Selector Tab Datamaster -->
                <div class="datamaster grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div>
                        <input type="radio" id="pasien" name="datamaster" value="pasien" class="hidden peer"
                            <?= $activeTab === 'pasien' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=pasien'" />
                        <label for="pasien" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Pasien</span>
                                <i class="fas fa-user-injured text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalPasien) ?></h3>
                            <span class="text-xs text-gray-500">Civitas Akademika Aktif</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" id="tenaga-layanan" name="datamaster" value="tenaga-layanan" class="hidden peer"
                            <?= $activeTab === 'tenaga-layanan' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=tenaga-layanan'" />
                        <label for="tenaga-layanan" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Dokter &amp; Psikolog</span>
                                <i class="fas fa-user-md text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalDokter) ?></h3>
                            <span class="text-xs text-gray-500">Tenaga Layanan Aktif</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" id="poli" name="datamaster" value="poli" class="hidden peer"
                            <?= $activeTab === 'poli' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=poli'" />
                        <label for="poli" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Poli Layanan</span>
                                <i class="far fa-hospital text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalPoli) ?></h3>
                            <span class="text-xs text-gray-500">Unit Layanan Medis</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" id="admin" name="datamaster" value="admin" class="hidden peer"
                            <?= $activeTab === 'admin' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=admin'" />
                        <label for="admin" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Admin</span>
                                <i class="fas fa-user-shield text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalAdmin) ?></h3>
                            <span class="text-xs text-gray-500">Pengelola Poliklinik</span>
                        </label>
                    </div>
                </div>

                <?php
                switch ($activeTab) {
                    case 'tenaga-layanan':
                        require_once __DIR__ . '/components/datamaster/tenaga-layanan/card-data-tenaga-layanan.php';
                        require_once __DIR__ . '/components/datamaster/tenaga-layanan/detail-tenaga-layanan.php';
                        require_once __DIR__ . '/components/datamaster/tenaga-layanan/form-data-tenaga-layanan.php';
                        break;
                    case 'poli':
                        require_once __DIR__ . '/components/datamaster/poli/card-data-poli.php';
                        require_once __DIR__ . '/components/datamaster/poli/form-data-poli.php';
                        break;
                    case 'admin':
                        require_once __DIR__ . '/components/datamaster/admin/card-data-admin.php';
                        require_once __DIR__ . '/components/datamaster/admin/detail-admin.php';
                        require_once __DIR__ . '/components/datamaster/admin/form-data-admin.php';
                        break;
                    case 'pasien':
                    default:
                        require_once __DIR__ . '/components/datamaster/pasien/card-data-pasien.php';
                        require_once __DIR__ . '/components/datamaster/pasien/detail-pasien.php';
                        require_once __DIR__ . '/components/datamaster/pasien/form-data-pasien.php';
                        break;
                }
                ?>
            </main>
        </div>
    </div>

    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>