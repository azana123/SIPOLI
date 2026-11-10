<?php
$currentTitle = $page_title ?? 'Beranda';
$adminName    = $_SESSION['username'] ?? 'Admin';
$formattedDate = '';

if (isset($pdo)) {
    try {
        $formattedDate = $pdo->query("
            SELECT TO_CHAR((NOW() AT TIME ZONE 'Asia/Jakarta'), 'Day, DD Month YYYY')
        ")->fetchColumn() ?: date('l, d F Y');
    } catch (PDOException $e) {
        $formattedDate = date('l, d F Y');
    }
} else {
    $formattedDate = date('l, d F Y');
}
?>
<nav class="bg-neutral-primary sticky top-0 z-30 border-b border-default">
    <div class="flex flex-wrap items-center justify-between mx-auto px-4 py-2.5">
        <div class="flex items-center justify-start">
            <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 mr-3 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <span class="sr-only">Buka sidebar</span>
                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                </svg>
            </button>

            <div class="page-title">
                <h5 class="font-bold text-sm text-heading"><?= htmlspecialchars($currentTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                <span class="date text-gray-500 text-sm"><?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <div class="right-nav flex items-center">
            <button id="add-pasien-offline" data-modal-target="modal-tambah-pasien" data-modal-toggle="modal-tambah-pasien" class="hidden md:inline-flex items-center text-white bg-primary hover:bg-primary-700 box-border border border-transparent focus:ring-4 focus:ring-primary-400 shadow-xs font-medium leading-5 rounded text-xs px-2.5 py-1 focus:outline-none mr-3 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#fff" class="mr-1">
                    <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                </svg>
                Tambahkan Pasien Offline
            </button>

            <!-- Greeting User -->
            <div class="flex items-center">
                <div class="flex items-center ms-3">
                    <div>
                        <button type="button" class="flex font-medium text-sm text-gray-700 focus:ring-gray-300 cursor-pointer items-center gap-2" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <span>Hallo, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></span>
                        </button>
                    </div>
                    <div class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44" id="dropdown-user">
                        <div class="px-4 py-3 border-b border-default-medium" role="none">
                            <p class="text-sm font-medium text-heading" role="none">
                                <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-xs text-body truncate" role="none">
                                Pengelola SIPOLI
                            </p>
                        </div>
                        <ul class="p-2 text-sm text-body font-medium" role="none">
                            <li>
                                <a href="<?=  __DIR__ . '/../../auth/logout.php'?>" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded" role="menuitem">Keluar (Sign out)</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
