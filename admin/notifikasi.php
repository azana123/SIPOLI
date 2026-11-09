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

$currentUserId = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'baca_semua') {
    try {
        $stmt = $pdo->prepare("UPDATE inbox SET dibaca_at = NOW() WHERE user_id = :uid AND dibaca_at IS NULL");
        $stmt->execute([':uid' => $currentUserId]);
        $_SESSION['flash_success'] = "Semua notifikasi telah ditandai sebagai dibaca.";
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
    $listInbox = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <div class="flex min-w-0 flex-col w-full">
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <main class="flex-1 min-w-0 bg-page p-6">
                <div class="bg-white block p-6 md:p-8 border border-gray-200 rounded-base shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900">Daftar pemberitahuan</h4>
                            <p class="text-sm text-gray-500 mt-1">Kelola peringatan stok obat, kabar dari dokter, dan konfirmasi pasien</p>
                        </div>
                        <?php if (!empty($listInbox)): ?>
                            <form method="POST" action="notifikasi.php">
                                <input type="hidden" name="action" value="baca_semua">
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-primary bg-primary-50 rounded hover:bg-primary-100">
                                    Tandai Semua Dibaca
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col gap-4">
                        <?php if (empty($listInbox)): ?>
                            <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-base border border-gray-100">
                                Belum ada pemberitahuan atau pesan di inbox Anda.
                            </div>
                        <?php else: ?>
                            <?php foreach ($listInbox as $inbox): ?>
                                <?php
                                $isUnread = empty($inbox['dibaca_at']);
                                $badgeTipe = match ($inbox['tipe']) {
                                    'stok_minimum'        => ['Stok Obat', 'bg-orange-100 text-orange-800'],
                                    'obat_kadaluarsa'     => ['Kedaluwarsa', 'bg-red-100 text-red-800'],
                                    'permintaan_obat'     => ['Apotek', 'bg-blue-100 text-blue-800'],
                                    'konfirmasi_kehadiran'=> ['Konfirmasi', 'bg-emerald-100 text-emerald-800'],
                                    default               => ['Informasi', 'bg-primary-50 text-primary-700'],
                                };
                                ?>
                                <div class="<?= $isUnread ? 'bg-primary-50 border-primary-200' : 'bg-white border-gray-200' ?> border rounded-base p-5 transition-all">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs text-gray-500"><?= e(date('d M Y, H:i', strtotime($inbox['created_at']))) ?></span>
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded <?= $badgeTipe[1] ?>">
                                            <?= e($badgeTipe[0]) ?>
                                        </span>
                                    </div>
                                    <h5 class="text-base font-bold text-gray-900 mb-1"><?= e($inbox['judul']) ?></h5>
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