<?php
$badgeKonfirmasiH1 = 0;
$badgeResep        = 0;
$badgeNotif        = 0;
$adminIdSidebar    = (int) ($_SESSION['profil_id'] ?? 0);

if (isset($pdo)) {
    try {
        if (!isset($adminProfil) && $adminIdSidebar > 0) {
            $stmtAdmSb = $pdo->prepare("SELECT id, nama, nim_nip, is_super, no_hp FROM admin WHERE id = :id");
            $stmtAdmSb->execute([':id' => $adminIdSidebar]);
            $adminProfil = $stmtAdmSb->fetch();
        }

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

    }
}

$sbAdminNama  = $adminProfil['nama'] ?? ($_SESSION['username'] ?? 'Admin');
$sbAdminPeran = !empty($adminProfil['is_super']) ? 'Super Admin' : 'Admin Poliklinik';

$kataAdmin    = preg_split('/\s+/', trim((string) $sbAdminNama));
$sbAdmInisial = strtoupper(substr($kataAdmin[0] ?? 'A', 0, 1) . substr($kataAdmin[1] ?? '', 0, 1));
if ($sbAdmInisial === '') {
    $sbAdmInisial = 'AD';
}

$activePage = $page_title ?? '';
?>
<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full md:translate-x-0 md:relative md:h-auto" aria-label="Sidebar">
    <div class="flex flex-col h-full px-4 py-6 bg-primary-500 border-e border-default overflow-y-auto">
        
        <div class="ps-2.5 mb-8">
            <a href="./dashboard.php" class="block">
                <h3 class="text-white font-bold text-4xl mb-1">Sipoli</h3>
                <p class="text-primary-100 text-sm">Politeknik Negeri Malang</p>
            </a>
        </div>

        <!-- Menu Navigasi Utama -->
        <ul class="space-y-2 font-medium flex-1">
            <li>
                <a href="./dashboard.php" class="flex items-center px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Dashboard Admin' ? 'btn-active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="ms-3.5 text-base">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="./jadwal.php" class="flex items-center px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Jadwal & Kuota' ? 'btn-active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="ms-3.5 text-base">Jadwal &amp; Kuota</span>
                </a>
            </li>
            <li>
                <a href="./reservasi.php" class="flex items-center px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Reservasi' ? 'btn-active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span class="ms-3.5 text-base">Reservasi</span>
                </a>
            </li>
            <li>
                <a href="./datamaster.php" class="flex items-center px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Datamaster' ? 'btn-active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="ms-3.5 text-base">Data Master</span>
                </a>
            </li>
            <li>
                <a href="./konfirmasi.php" class="flex items-center justify-between px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Konfirmasi H-1' ? 'btn-active' : '' ?>">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="ms-3.5 text-base">Konfirmasi H-1</span>
                    </div>
                    <?php if ($badgeKonfirmasiH1 > 0): ?>
                        <div class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary bg-white rounded-full">
                            <?= $badgeKonfirmasiH1 ?>
                        </div>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="./obat.php" class="flex items-center px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Obat & Stok' ? 'btn-active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    <span class="ms-3.5 text-base">Obat &amp; Stok</span>
                </a>
            </li>
            <li>
                <a href="./resep.php" class="flex items-center justify-between px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Resep' ? 'btn-active' : '' ?>">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span class="ms-3.5 text-base">Resep</span>
                    </div>
                    <?php if ($badgeResep > 0): ?>
                        <div class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary bg-white rounded-full">
                            <?= $badgeResep ?>
                        </div>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="./kirim-informasi.php" class="flex items-center px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Kirim Informasi' ? 'btn-active' : '' ?>">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    <span class="ms-3.5 text-base">Kirim Informasi</span>
                </a>
            </li>
            <li>
                <a href="./notifikasi.php" class="flex items-center justify-between px-4 py-2.5 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Notifikasi' ? 'btn-active' : '' ?>">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="ms-3.5 text-base">Notifikasi</span>
                    </div>
                    <?php if ($badgeNotif > 0): ?>
                        <div class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-primary bg-white rounded-full">
                            <?= $badgeNotif ?>
                        </div>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="mt-8">
            <hr class="border-primary-400 mb-6">

            <div class="flex items-center text-white px-1">
                <div class="w-11 h-11 rounded-full bg-primary-400 flex items-center justify-center font-bold text-lg shrink-0 mr-3">
                    <?= htmlspecialchars($sbAdmInisial, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm truncate"><?= htmlspecialchars($sbAdminNama, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-primary-100 truncate"><?= htmlspecialchars($sbAdminPeran . ' • SIPOLI', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <a href="../auth/logout.php" class="text-white hover:text-primary-100 transition-colors ml-2" aria-label="Keluar" title="Keluar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>

    </div>
</aside>