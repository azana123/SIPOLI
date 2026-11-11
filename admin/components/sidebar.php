<?php
$badgeKonfirmasiH1 = 0;
$badgeResep        = 0;
$badgeNotif        = 0;

if (isset($pdo)) {
    try {
        $uidSidebar = (int) ($_SESSION['user_id'] ?? 0);
        $stmtBadge  = $pdo->prepare("
            SELECT
                (
                    SELECT COUNT(*)
                    FROM reservasi r
                    JOIN jadwal j ON j.id = r.jadwal_id
                    JOIN poli po  ON po.id = j.poli_id
                    WHERE j.tanggal = fn_today() + 1
                      AND po.nama ILIKE '%konseling%'
                      AND r.status = 'menunggu'
                ) AS konfirmasi_h1,
                (
                    SELECT COUNT(*)
                    FROM permintaan_obat
                    WHERE status = 'menunggu'
                ) AS resep_menunggu,
                (
                    SELECT COUNT(*)
                    FROM inbox
                    WHERE user_id = :uid AND dibaca_at IS NULL
                ) AS notif_unread
        ");
        $stmtBadge->execute([':uid' => $uidSidebar]);
        $rowBadge = $stmtBadge->fetch();

        $badgeKonfirmasiH1 = (int) ($rowBadge['konfirmasi_h1'] ?? 0);
        $badgeResep        = (int) ($rowBadge['resep_menunggu'] ?? 0);
        $badgeNotif        = (int) ($rowBadge['notif_unread'] ?? 0);
    } catch (PDOException $e) {
        // Biarkan 0 jika query gagal agar sidebar tetap ter-render
    }
}
?>
<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-full h-screen transition-transform -translate-x-full md:translate-x-0 md:relative md:h-auto" aria-label="Sidebar">
    <div class="h-full px-3 py-4 bg-primary-500 border-e border-default">
        <a href="<?= BASE_URL ?>/dashboard.php" class="flex items-center ps-2.5 mb-5">
            <h3 class="text-white font-bold text-3xl">SIPOLI</h3>
        </a>
        <ul class="space-y-2 font-medium">
            <li>
                <a href="<?= BASE_URL ?>/dashboard.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Dashboard Admin" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/jadwal.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Jadwal & Kuota" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Jadwal & Kuota</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/reservasi.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Reservasi" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Reservasi</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/datamaster.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Datamaster" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Data Master</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/konfirmasi.php" class="flex items-center justify-between px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Konfirmasi H-1" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Konfirmasi H-1</span>
                    <?php if ($badgeKonfirmasiH1 > 0): ?>
                        <div class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary bg-white border-2 border-buffer rounded-full">
                            <?= $badgeKonfirmasiH1 ?>
                        </div>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/obat.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Obat & Stok" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Obat & Stok</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/resep.php" class="flex items-center justify-between px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Resep" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Resep</span>
                    <?php if ($badgeResep > 0): ?>
                        <div class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary bg-white border-2 border-buffer rounded-full">
                            <?= $badgeResep ?>
                        </div>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/kirim-informasi.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Kirim Informasi" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Kirim Informasi</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/notifikasi.php" class="flex items-center justify-between px-3 py-2.5 text-white rounded-base hover:bg-primary-400 <?= $page_title == "Notifikasi" ? 'btn-active' : '' ?>">
                    <span class="ms-3">Notifikasi</span>
                    <?php if ($badgeNotif > 0): ?>
                        <div class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary bg-white border-2 border-buffer rounded-full">
                            <?= $badgeNotif ?>
                        </div>
                    <?php endif; ?>
                </a>
            </li>
            <li class="text-center md:hidden">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" type="button" class="bg-white border border-transparent hover focus:ring-4 focus:ring-primary-400-05 shadow-xs font-medium leading-5 rounded-full text-sm px-1 py-1 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#1f1f1f">
                        <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                    </svg>
                </button>
            </li>
        </ul>
    </div>
</aside>