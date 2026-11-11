<?php
$currentTitle = $page_title ?? 'Dashboard Dokter';
$dokterIdSession = (int) ($_SESSION['profil_id'] ?? 0);

if (!isset($dokterProfil) && isset($pdo) && $dokterIdSession > 0) {
    try {
        $stmtDokNav = $pdo->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id, p.nama AS nama_poli, p.butuh_reservasi
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmtDokNav->execute([':id' => $dokterIdSession]);
        $dokterProfil = $stmtDokNav->fetch();
    } catch (PDOException $e) {
        $dokterProfil = null;
    }
}

$dokterNama    = $dokterProfil['nama'] ?? ($_SESSION['username'] ?? 'Dokter');
$dokterProfesi = !empty($dokterProfil['profesi']) ? ucwords($dokterProfil['profesi']) : 'Tenaga Layanan';
$dokterPoli    = $dokterProfil['nama_poli'] ?? 'Poliklinik';

$hariIndoNav = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
$bulanIndoNav = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$todayDateNav = date('Y-m-d');
$jamWibNav    = date('H.i');

if (isset($pdo)) {
    try {
        $rowTimeNav = $pdo->query("
            SELECT fn_today()::text AS tgl,
                   TO_CHAR((NOW() AT TIME ZONE 'Asia/Jakarta'), 'HH24.MI') AS jam
        ")->fetch();
        if ($rowTimeNav) {
            $todayDateNav = $rowTimeNav['tgl'];
            $jamWibNav    = $rowTimeNav['jam'];
        }
    } catch (PDOException $e) {

    }
}

$tsNav = strtotime($todayDateNav);
$formattedDate = $hariIndoNav[(int) date('N', $tsNav)] . ', ' . (int) date('j', $tsNav) . ' ' . $bulanIndoNav[(int) date('n', $tsNav)] . ' ' . date('Y', $tsNav);
?>
<nav class="bg-neutral-primary sticky top-0 z-30 border-b border-default">
    <div class="flex flex-wrap items-center justify-between mx-auto px-4 md:px-8 py-3.5">
        <div class="flex items-center justify-start">
            <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 mr-3 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <span class="sr-only">Buka sidebar</span>
                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                </svg>
            </button>

            <div class="page-title">
                <h5 class="font-bold text-base text-heading"><?= htmlspecialchars($currentTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                <span class="date text-gray-500 text-xs"><?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <div class="right-nav flex items-center gap-4">
            <div class="hidden sm:flex items-center gap-2 text-xs text-gray-600 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-base">
                <i class="far fa-clock text-primary"></i>
                <span class="font-medium"><?= htmlspecialchars($jamWibNav, ENT_QUOTES, 'UTF-8') ?> WIB</span>
            </div>

            <!-- Greeting User -->
            <div class="flex items-center">
                <div class="flex items-center">
                    <div>
                        <button type="button" class="flex font-medium text-sm text-gray-700 focus:ring-gray-300 cursor-pointer items-center gap-2" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Buka menu pengguna</span>
                            <span>Halo, <strong class="text-gray-900"><?= htmlspecialchars($dokterNama, ENT_QUOTES, 'UTF-8') ?></strong></span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                    </div>
                    <div class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-52" id="dropdown-user">
                        <div class="px-4 py-3 border-b border-default-medium" role="none">
                            <p class="text-sm font-bold text-heading truncate" role="none">
                                <?= htmlspecialchars($dokterNama, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-xs text-body truncate mt-0.5" role="none">
                                <?= htmlspecialchars($dokterProfesi . ' • ' . $dokterPoli, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                        <ul class="p-2 text-sm text-body font-medium" role="none">
                            <li>
                                <a href="../auth/logout.php" class="inline-flex items-center w-full p-2 text-red-600 hover:bg-red-50 rounded" role="menuitem">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>