<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Datamaster";
$app_name   = "SIPOLI";
$errorDb    = false;

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$validTabs = ['pasien', 'tenaga-layanan', 'poli', 'admin'];
$activeTab = isset($_GET['tab']) && in_array($_GET['tab'], $validTabs, true) ? $_GET['tab'] : 'pasien';

$search  = trim($_GET['q'] ?? '');
$status  = trim($_GET['status'] ?? '');
$civitas = trim($_GET['civitas'] ?? '');
$poliId  = trim($_GET['poli_id'] ?? '');
$reserv  = trim($_GET['reservasi'] ?? '');
$tipeAdm = trim($_GET['tipe_admin'] ?? '');

$totalPasien = 0;
$totalDokter = 0;
$totalPoli   = 0;
$totalAdmin  = 0;

$rows     = [];
$listPoli = [];

try {
    $stats = $pdo->query("
        SELECT 
            (
                SELECT COUNT(*)
                FROM admin a
                JOIN users u ON a.user_id = u.id
                WHERE u.is_active = TRUE
            ) AS total_admin,
            (
                SELECT COUNT(*)
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.is_active = TRUE AND (u.id IS NULL OR u.is_active = TRUE)
            ) AS total_pasien,
            (
                SELECT COUNT(*)
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                WHERE d.is_active = TRUE AND u.is_active = TRUE
            ) AS total_dokter,
            (
                SELECT COUNT(*)
                FROM poli
            ) AS total_poli
    ")->fetch();

    $totalAdmin  = (int) ($stats['total_admin'] ?? 0);
    $totalPasien = (int) ($stats['total_pasien'] ?? 0);
    $totalDokter = (int) ($stats['total_dokter'] ?? 0);
    $totalPoli   = (int) ($stats['total_poli'] ?? 0);

    $where  = [];
    $params = [];

    if ($activeTab === 'pasien') {
        if ($search !== '') {
            $where[] = "(p.nama ILIKE :q OR p.nim_nip ILIKE :q OR COALESCE(p.no_hp, '') ILIKE :q OR COALESCE(u.username, '') ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if (in_array($civitas, ['mahasiswa', 'dosen', 'staf'], true)) {
            $where[] = "p.status_civitas = :civitas";
            $params[':civitas'] = $civitas;
        }
        if ($status === 'aktif') {
            $where[] = "(p.is_active = TRUE AND (u.id IS NULL OR u.is_active = TRUE))";
        } elseif ($status === 'non-aktif') {
            $where[] = "(p.is_active = FALSE OR u.is_active = FALSE)";
        }

        $sql = "
            SELECT p.id, p.nim_nip, p.nama, p.jenis_kelamin, p.tanggal_lahir,
                   p.no_hp, p.status_civitas, p.golongan_darah,
                   u.username,
                   (p.is_active AND COALESCE(u.is_active, TRUE)) AS status_aktif
            FROM pasien p
            LEFT JOIN users u ON p.user_id = u.id
        ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY p.nama ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } elseif ($activeTab === 'tenaga-layanan') {
        $listPoli = $pdo->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();

        if ($search !== '') {
            $where[] = "(d.nama ILIKE :q OR COALESCE(d.no_str, '') ILIKE :q OR COALESCE(d.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if ($poliId !== '' && ctype_digit($poliId)) {
            $where[] = "d.poli_id = :poli_id";
            $params[':poli_id'] = (int) $poliId;
        }
        if ($status === 'aktif') {
            $where[] = "(d.is_active = TRUE AND u.is_active = TRUE)";
        } elseif ($status === 'non-aktif') {
            $where[] = "(d.is_active = FALSE OR u.is_active = FALSE)";
        }

        $sql = "
            SELECT d.id, d.nama, d.no_str, d.no_hp, d.profesi,
                   po.nama AS nama_poli, u.username,
                   (d.is_active AND u.is_active) AS status_aktif
            FROM dokter d
            JOIN users u ON d.user_id = u.id
            JOIN poli po ON d.poli_id = po.id
        ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY d.nama ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } elseif ($activeTab === 'poli') {
        if ($search !== '') {
            $where[] = "po.nama ILIKE :q";
            $params[':q'] = '%' . $search . '%';
        }
        if ($reserv === 'ya') {
            $where[] = "po.butuh_reservasi = TRUE";
        } elseif ($reserv === 'tidak') {
            $where[] = "po.butuh_reservasi = FALSE";
        }

        $sql = "
            SELECT po.id, po.nama, po.butuh_reservasi, po.kuota_harian, po.durasi_menit,
                   COUNT(d.id) FILTER (WHERE d.is_active = TRUE) AS jumlah_dokter
            FROM poli po
            LEFT JOIN dokter d ON d.poli_id = po.id
        ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " GROUP BY po.id ORDER BY po.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

    } elseif ($activeTab === 'admin') {
        if ($search !== '') {
            $where[] = "(a.nama ILIKE :q OR COALESCE(a.nim_nip, '') ILIKE :q OR COALESCE(a.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
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

        $sql = "
            SELECT a.id, a.nama, a.nim_nip, a.no_hp, a.is_super,
                   u.username, u.is_active AS status_aktif,
                   dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin
            FROM admin a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
        ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY a.is_super DESC, a.nama ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">

                <?php if ($errorDb): ?>
                    <div class="mb-3 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        Data master gagal dimuat. Angka di bawah belum tentu benar. Periksa log server.
                    </div>
                <?php endif; ?>

                <div class="title-datamaster mb-2 text-gray-500 font-medium">
                    <p>Kelola data civitas akademika & layanan · Politeknik Negeri Malang</p>
                </div>

                <!-- Selector Tab Datamaster -->
                <div class="datamaster grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div class="pasien">
                        <input type="radio" id="pasien" name="datamaster" value="pasien" class="hidden peer"
                            <?= $activeTab === 'pasien' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=pasien'" />
                        <label for="pasien" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Pasien</div>
                                <div class="w-full text-sm text-gray-500">Civitas Akademika</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                <p><?= e($totalPasien); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="tenaga-layanan">
                        <input type="radio" id="tenaga-layanan" name="datamaster" value="tenaga-layanan" class="hidden peer"
                            <?= $activeTab === 'tenaga-layanan' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=tenaga-layanan'" />
                        <label for="tenaga-layanan" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Dokter & Psikolog</div>
                                <div class="w-full text-sm text-gray-500">Tenaga Layanan</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                <p><?= e($totalDokter); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="poli">
                        <input type="radio" id="poli" name="datamaster" value="poli" class="hidden peer"
                            <?= $activeTab === 'poli' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=poli'" />
                        <label for="poli" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Poli</div>
                                <div class="w-full text-sm text-gray-500">Layanan medis tersedia</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                <p><?= e($totalPoli); ?></p>
                            </div>
                        </label>
                    </div>

                    <div class="admin">
                        <input type="radio" id="admin" name="datamaster" value="admin" class="hidden peer"
                            <?= $activeTab === 'admin' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=admin'" />
                        <label for="admin" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Admin</div>
                                <div class="w-full text-sm text-gray-500">Pengelola SIPOLI</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                <p><?= e($totalAdmin); ?></p>
                            </div>
                        </label>
                    </div>
                </div>

                <?php
                switch ($activeTab) {
                    case 'tenaga-layanan':
                        require_once __DIR__ . '/components/datamaster/card-data-tenaga-layanan.php';
                        break;
                    case 'poli':
                        require_once __DIR__ . '/components/datamaster/card-data-poli.php';
                        break;
                    case 'admin':
                        require_once __DIR__ . '/components/datamaster/card-data-admin.php';
                        break;
                    case 'pasien':
                    default:
                        require_once __DIR__ . '/components/datamaster/pasien/card-data-pasien.php';
                        break;
                }
                ?>
            </main>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/detail-reservasi.php'; ?>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>