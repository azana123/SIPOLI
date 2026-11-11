<?php
$dokterIdSidebar = (int) ($_SESSION['profil_id'] ?? 0);

if (!isset($dokterProfil) && isset($pdo) && $dokterIdSidebar > 0) {
    try {
        $stmtDokSb = $pdo->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id, p.nama AS nama_poli, p.butuh_reservasi
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmtDokSb->execute([':id' => $dokterIdSidebar]);
        $dokterProfil = $stmtDokSb->fetch();
    } catch (PDOException $e) {
        $dokterProfil = null;
    }
}

$sbNama    = $dokterProfil['nama'] ?? ($_SESSION['username'] ?? 'Dokter');
$sbProfesi = !empty($dokterProfil['profesi']) ? ucwords($dokterProfil['profesi']) : 'Dokter';
$sbPoli    = $dokterProfil['nama_poli'] ?? 'Poliklinik';

$namaBersih = preg_replace('/^(drg?\.|psi\.)\s*/i', '', $sbNama);
$kataNama   = preg_split('/\s+/', trim((string) $namaBersih));
$sbInisial  = strtoupper(substr($kataNama[0] ?? 'D', 0, 1) . substr($kataNama[1] ?? '', 0, 1));
if ($sbInisial === '') {
    $sbInisial = 'DR';
}

// Ambil kontak admin bertugas dari fungsi fn_kontak_admin()
$linkBantuanAdmin = '#';
$namaAdminJaga    = 'Admin Poliklinik';
if (isset($pdo)) {
    try {
        $admKontak = $pdo->query("SELECT nama, no_hp FROM fn_kontak_admin() LIMIT 1")->fetch();
        if ($admKontak && !empty($admKontak['no_hp'])) {
            $namaAdminJaga = $admKontak['nama'];
            $hpDigits      = preg_replace('/[^0-9]/', '', (string) $admKontak['no_hp']);
            if (str_starts_with($hpDigits, '0')) {
                $hpDigits = '62' . substr($hpDigits, 1);
            }
            $pesanWa          = rawurlencode("Halo Admin {$namaAdminJaga}, saya {$sbNama} ({$sbPoli}) ingin berkoordinasi terkait pelayanan poliklinik.");
            $linkBantuanAdmin = "https://wa.me/{$hpDigits}?text={$pesanWa}";
        }
    } catch (PDOException $e) {
        $linkBantuanAdmin = '#';
    }
}

$activePage = $page_title ?? '';
?>
<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full md:translate-x-0 md:relative md:h-auto" aria-label="Sidebar">
    <div class="flex flex-col h-full px-4 py-6 bg-primary-500 border-e border-default overflow-y-auto">
        
        <!-- Header / Logo -->
        <div class="ps-2.5 mb-8">
            <h3 class="text-white font-bold text-4xl mb-1">Sipoli</h3>
            <p class="text-primary-100 text-sm">Politeknik Negeri Malang</p>
        </div>

        <!-- Menu Navigasi Utama -->
        <ul class="space-y-3 font-medium flex-1">
            <li>
                <a href="./dashboard.php" class="flex items-center px-4 py-3 text-white rounded-base hover:bg-primary-400 transition-colors <?= in_array($activePage, ['Dashboard Dokter', 'Beranda'], true) ? 'btn-active' : '' ?>">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="ms-4 text-lg">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="./jadwal.php" class="flex items-center px-4 py-3 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Jadwal Saya' ? 'btn-active' : '' ?>">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="ms-4 text-lg">Jadwal Saya</span>
                </a>
            </li>
            <li>
                <a href="./daftar-pasien.php" class="flex items-center px-4 py-3 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Daftar Pasien' ? 'btn-active' : '' ?>">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="ms-4 text-lg">Daftar Pasien</span>
                </a>
            </li>
            <li>
                <a href="./riwayat-penanganan.php" class="flex items-center px-4 py-3 text-white rounded-base hover:bg-primary-400 transition-colors <?= $activePage === 'Riwayat Penanganan' ? 'btn-active' : '' ?>">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span class="ms-4 text-lg leading-tight">Riwayat<br>Penanganan</span>
                </a>
            </li>
            <li>
                <a href="./detail-resep.php" class="flex items-center px-4 py-3 text-white rounded-base hover:bg-primary-400 transition-colors <?= in_array($activePage, ['Resep', 'Detail Resep'], true) ? 'btn-active' : '' ?>">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span class="ms-4 text-lg">Resep</span>
                </a>
            </li>
        </ul>

        <!-- Bagian Bawah (Bantuan & Profil) -->
        <div class="mt-8">
            <div class="bg-white/10 rounded-base p-4 mb-6">
                <svg class="w-6 h-6 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <h4 class="text-white font-bold mb-1">Butuh bantuan?</h4>
                <p class="text-primary-100 text-xs mb-3">Hubungi <?= htmlspecialchars($namaAdminJaga, ENT_QUOTES, 'UTF-8') ?> untuk koordinasi pelayanan.</p>
                <a href="<?= htmlspecialchars($linkBantuanAdmin, ENT_QUOTES, 'UTF-8') ?>" <?= $linkBantuanAdmin !== '#' ? 'target="_blank" rel="noopener"' : '' ?> class="text-white font-semibold text-sm flex items-center hover:underline">
                    Hubungi admin &rarr;
                </a>
            </div>

            <hr class="border-primary-400 mb-6">

            <!-- Profil Dokter -->
            <div class="flex items-center text-white px-1">
                <div class="w-11 h-11 rounded-full bg-primary-400 flex items-center justify-center font-bold text-lg shrink-0 mr-3">
                    <?= htmlspecialchars($sbInisial, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm truncate"><?= htmlspecialchars($sbNama, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-primary-100 truncate"><?= htmlspecialchars($sbProfesi . ' • ' . $sbPoli, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <a href="../auth/logout.php" class="text-white hover:text-primary-100 transition-colors ml-2" aria-label="Keluar" title="Keluar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>

    </div>
</aside>