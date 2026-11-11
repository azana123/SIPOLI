<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Notifikasi";
$app_name   = "SIPOLI";

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'baca_semua') {
    try {
        $stmt = $pdo->prepare("UPDATE inbox SET dibaca_at = NOW() WHERE user_id = :uid AND dibaca_at IS NULL");
        $stmt->execute([':uid' => $currentUserId]);
        $_SESSION['flash_success'] = "Seluruh pemberitahuan telah ditandai sudah dibaca.";
    } catch (PDOException $e) {
        error_log('[notifikasi-admin] ' . $e->getMessage());
    }
    header("Location: notifikasi.php");
    exit;
}

$listInbox = [];
$errorDb   = false;

try {
    $stmt = $pdo->prepare("
        SELECT id, tipe, judul, pesan, ref_tipe, ref_id, dibaca_at, created_at
        FROM inbox
        WHERE user_id = :uid
        ORDER BY created_at DESC
        LIMIT 30
    ");
    $stmt->execute([':uid' => $currentUserId]);
    $listInbox = $stmt->fetchAll();

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[notifikasi-read] ' . $e->getMessage());
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
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <div class="flex min-w-0 flex-col w-full">
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <main class="flex-1 min-w-0 bg-page p-6 md:p-8">
                <?php if ($flashSuccess): ?>
                    <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 border border-emerald-300 rounded-base" role="alert"><?= e($flashSuccess) ?></div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert"><?= e($flashError) ?></div>
                <?php endif; ?>

                <div class="bg-white block p-6 border border-gray-200 rounded-base shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar pemberitahuan</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Informasi stok obat, pengajuan perubahan jadwal dokter, dan konfirmasi kehadiran pasien</p>
                        </div>
                        <?php if (!empty($listInbox)): ?>
                            <form method="POST" action="notifikasi.php">
                                <input type="hidden" name="action" value="baca_semua">
                                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-primary bg-primary-50 border border-primary-100 rounded-base hover:bg-primary-100 cursor-pointer transition-colors">
                                    <i class="fas fa-check-double mr-1.5"></i> Tandai Semua Dibaca
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col gap-3">
                        <?php if (empty($listInbox)): ?>
                            <div class="p-8 text-center text-gray-500 bg-gray-50/60 rounded-base border border-gray-100">
                                Belum ada pemberitahuan baru untuk saat ini.
                            </div>
                        <?php else: ?>
                            <?php foreach ($listInbox as $inbox): ?>
                                <?php
                                $isUnread  = empty($inbox['dibaca_at']);
                                $badgeTipe = match ($inbox['tipe']) {
                                    'stok_minimum'         => ['Stok Obat Menipis', 'bg-orange-100 text-orange-800'],
                                    'obat_kadaluarsa'      => ['Masa Berlaku Obat', 'bg-red-100 text-red-800'],
                                    'permintaan_obat'      => ['Resep Baru', 'bg-blue-100 text-blue-800'],
                                    'konfirmasi_kehadiran' => ['Konfirmasi Pasien', 'bg-emerald-100 text-emerald-800'],
                                    default                => ['Pemberitahuan', 'bg-primary-50 text-primary-700'],
                                };
                                ?>
                                <div class="<?= $isUnread ? 'bg-primary-50/40 border-primary-200 border-l-4 border-l-primary' : 'bg-white border-gray-200' ?> border rounded-base p-5 transition-all">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs text-gray-500"><?= e(date('d M Y, H.i', strtotime($inbox['created_at']))) ?> WIB</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-base <?= $badgeTipe[1] ?>">
                                            <?= e($badgeTipe[0]) ?>
                                        </span>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-900 mb-1"><?= e($inbox['judul']) ?></h4>
                                    <p class="text-sm text-gray-600 leading-relaxed"><?= e($inbox['pesan']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>