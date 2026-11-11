<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Kirim Informasi";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = trim($_POST['judul'] ?? '');
    $pesan  = trim($_POST['pesan'] ?? '');
    $target = $_POST['target_penerima'] ?? 'semua';

    if ($judul === '' or $pesan === '') {
        $_SESSION['flash_error'] = "Judul dan isi pesan informasi wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction();

            $sqlUser    = "SELECT id FROM users WHERE role = 'pasien' AND is_active = TRUE";
            $paramsUser = [];

            if (in_array($target, ['mahasiswa', 'dosen', 'staf'], true)) {
                $sqlUser = "
                    SELECT u.id 
                    FROM users u 
                    JOIN pasien p ON p.user_id = u.id 
                    WHERE u.role = 'pasien' AND u.is_active = TRUE AND p.status_civitas = :civ
                ";
                $paramsUser[':civ'] = $target;
            }

            $stmtTarget = $pdo->prepare($sqlUser);
            $stmtTarget->execute($paramsUser);
            $penerimaList = $stmtTarget->fetchAll();

            if (empty($penerimaList)) {
                throw new Exception("Belum ada akun pasien aktif pada kategori penerima yang dipilih.");
            }

            $stmtIns = $pdo->prepare("
                INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id)
                VALUES (:uid, 'info', :judul, :pesan, 'informasi', NULL)
            ");

            foreach ($penerimaList as $uRow) {
                $stmtIns->execute([
                    ':uid'   => (int) $uRow['id'],
                    ':judul' => $judul,
                    ':pesan' => $pesan,
                ]);
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Pengumuman berhasil dikirimkan kepada " . count($penerimaList) . " pasien.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = "Gagal mengirim pengumuman: " . $e->getMessage();
        }
    }

    header("Location: kirim-informasi.php");
    exit;
}

$riwayatInfo = [];
try {
    $riwayatInfo = $pdo->query("
        SELECT i.judul, i.pesan, i.created_at, COUNT(i.id) AS jumlah_penerima
        FROM inbox i
        WHERE i.ref_tipe = 'informasi'
        GROUP BY i.judul, i.pesan, i.created_at
        ORDER BY i.created_at DESC
        LIMIT 10
    ")->fetchAll();
} catch (PDOException $e) {
    $riwayatInfo = [];
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

                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                    <p class="text-sm text-gray-600 font-medium">Sampaikan informasi layanan dan pengumuman penting kepada pasien poliklinik.</p>
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-base bg-primary-50 text-primary-700 border border-primary-100">
                        Pengumuman Poliklinik
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    
                    <!-- Left: Tulis Informasi -->
                    <div class="lg:col-span-2 bg-white p-6 border border-gray-200 rounded-base shadow-sm flex flex-col">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Tulis pengumuman baru</h3>
                        
                        <form action="kirim-informasi.php" method="POST" class="flex flex-col gap-5 flex-1">
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-800">Penerima pengumuman *</label>
                                <select name="target_penerima" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-base block w-full p-2.5 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="semua">Seluruh Pasien Aktif</option>
                                    <option value="mahasiswa">Khusus Mahasiswa</option>
                                    <option value="dosen">Khusus Dosen</option>
                                    <option value="staf">Khusus Pegawai / Staf</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-800">Judul pengumuman *</label>
                                <input type="text" name="judul" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-base block w-full p-2.5 focus:ring-primary-500 focus:border-primary-500" placeholder="Contoh: Penyesuaian Jam Pelayanan Poliklinik">
                            </div>

                            <div class="flex-1">
                                <label class="block mb-2 text-sm font-semibold text-gray-800">Isi pesan informasi *</label>
                                <textarea name="pesan" rows="4" required class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-base border border-gray-300 focus:ring-primary-500 focus:border-primary-500 resize-none h-32" placeholder="Tuliskan isi pengumuman lengkap di sini..."></textarea>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit" class="inline-flex items-center text-white bg-primary hover:bg-primary-700 font-medium rounded-base text-sm px-5 py-2.5 cursor-pointer transition-colors">
                                    <i class="far fa-paper-plane mr-2"></i> Kirim Pengumuman
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Info Panel -->
                    <div class="bg-white p-6 border border-gray-200 rounded-base shadow-sm flex flex-col">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Panduan pengiriman</h3>
                        <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                            Pengumuman yang dikirimkan akan langsung tampil pada halaman pemberitahuan masing-masing pasien sesuai kelompok civitas yang dipilih.
                        </p>
                        
                        <div class="bg-primary-50/70 p-4 rounded-base border border-primary-100 mt-auto">
                            <p class="text-sm font-bold text-primary-700 mb-1">Periksa kembali sebelum mengirim</p>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Pastikan judul, jam layanan, dan isi informasi sudah tepat karena pengumuman yang telah dikirim akan langsung diterima oleh pasien.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Card Riwayat -->
                <div class="bg-white border border-gray-200 rounded-base shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Riwayat pengiriman informasi</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/60 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-semibold">Waktu Kirim</th>
                                    <th scope="col" class="px-4 py-3 font-semibold">Judul &amp; Isi Pengumuman</th>
                                    <th scope="col" class="px-4 py-3 font-semibold text-right">Jumlah Penerima</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (empty($riwayatInfo)): ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat pengiriman pengumuman.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($riwayatInfo as $rw): ?>
                                        <tr class="hover:bg-gray-50/60">
                                            <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-500">
                                                <?= e(date('d M Y, H.i', strtotime($rw['created_at']))) ?> WIB
                                            </td>
                                            <td class="px-4 py-4">
                                                <p class="font-bold text-gray-900"><?= e($rw['judul']) ?></p>
                                                <p class="text-xs text-gray-600 mt-0.5"><?= e($rw['pesan']) ?></p>
                                            </td>
                                            <td class="px-4 py-4 text-right font-semibold text-primary-700 whitespace-nowrap">
                                                <?= (int) $rw['jumlah_penerima'] ?> pasien
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
    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>