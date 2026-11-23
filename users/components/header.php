<?php
// Badge lonceng: jumlah notifikasi belum dibaca (hanya untuk pasien yang login)
$notifBelumHeader = 0;
if (isset($_SESSION['user_id'], $_SESSION['role']) && $_SESSION['role'] === 'pasien') {
    try {
        $notifBelumHeader = QueryPasienNotifikasi::belumDibaca((int) $_SESSION['user_id']);
    } catch (PDOException $e) {
        $notifBelumHeader = 0;
    }
}
?>
<!-- App Bar (Mobile) -->
<header class="bg-primary-500 text-white px-5 py-4 flex items-center sticky top-0 z-20 shadow-sm md:hidden">
    <a href="<?= htmlspecialchars($back_url) ?>" class="mr-4 hover:bg-primary-600 p-1 rounded transition-colors">
        <i class="fas fa-chevron-left text-lg"></i>
    </a>
    <h1 class="text-lg font-semibold tracking-wide flex-1 truncate"><?= htmlspecialchars($page_title) ?></h1>
    <a href="<?= htmlspecialchars(url('users/notifikasi.php')) ?>" class="relative inline-flex items-center justify-center w-9 h-9 bg-primary hover:bg-primary-700 rounded-full transition-colors" aria-label="Notifikasi">
        <i class="fas fa-bell"></i>
        <?php if ($notifBelumHeader > 0): ?>
            <span class="absolute -top-1 -end-1 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-bold text-primary bg-white rounded-full">
                <?= (int) $notifBelumHeader ?>
            </span>
        <?php endif; ?>
    </a>
</header>

<!-- Header (Desktop) -->
<header class="hidden md:flex flex-col bg-primary-500 text-white px-8 md:px-20 shadow-sm h-[50vh]">
    <!-- Navbar Atas -->
    <section class="flex justify-between items-center w-full py-4 text-page relative z-20 pointer-events-auto">
        <div class="brand text-xl font-bold tracking-wider">
            SIPOLI
        </div>
        <div class="flex items-center gap-5">
            <?php if ($apakahLogin): ?>
                <a href="<?= htmlspecialchars(url('users/notifikasi.php')) ?>" class="notifikasi relative inline-flex items-center justify-center text-lg px-3 py-1.5 bg-primary hover:bg-primary-700 rounded-full" aria-label="Notifikasi">
                    <i class="fas fa-bell"></i>
                    <?php if ($notifBelumHeader > 0): ?>
                        <span class="absolute -top-1 -end-1 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-bold text-primary bg-white rounded-full">
                            <?= (int) $notifBelumHeader ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="profile relative">
                    <button type="button" data-dropdown-toggle="dropdown-pasien-beranda" class="flex items-center gap-2 bg-primary px-3.5 py-1.5 rounded-base hover:bg-primary-700 transition font-medium text-page cursor-pointer">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span>Halo, <?= e($namaPasien) ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <div id="dropdown-pasien-beranda" class="z-50 hidden absolute end-0 top-12 bg-white text-gray-800 border border-default-medium rounded-base shadow-lg w-48" role="none">
                        <div class="px-4 py-3 border-b border-gray-100" role="none">
                            <p class="text-sm font-bold text-heading truncate" role="none"><?= e($namaPasien) ?></p>
                            <p class="text-xs text-body truncate mt-0.5" role="none">Pasien &bull; SIPOLI</p>
                        </div>
                        <ul class="p-2 text-sm font-medium" role="none">
                            <li>
                                <a href="<?= htmlspecialchars(url('auth/logout.php')) ?>" class="inline-flex items-center w-full p-2 text-red-600 hover:bg-red-50 rounded" role="menuitem">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= htmlspecialchars(url('auth/login.php')) ?>" class="profile flex gap-2 items-center bg-primary px-4 py-1.5 rounded-base hover:bg-primary-700 transition">
                    <i class="fas fa-user-circle text-lg"></i>
                    <span class="font-medium">Login</span>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Wrapper untuk menengahkan konten -->
    <div class="flex-1 flex flex-col justify-center pb-10">

        <!-- Judul & Deskripsi -->
        <div class="title">
            <nav class="flex mb-5" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="./../../index.php" class="inline-flex items-center text-sm font-medium text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-home mr-2"></i>
                            Beranda
                        </a>
                    </li>
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <?php $is_last = ($index === array_key_last($breadcrumbs)); ?>
                        <li <?= $is_last ? 'aria-current="page"' : '' ?>>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-200 text-sm mx-1"></i>
                                <?php if (!$is_last && !empty($crumb['url'])): ?>
                                    <a href="<?= htmlspecialchars($crumb['url']) ?>" class="ml-1 text-sm font-medium text-white hover:text-gray-200 transition-colors md:ml-2">
                                        <?= htmlspecialchars($crumb['label']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="ml-1 text-sm font-medium text-gray-100 md:ml-2">
                                        <?= htmlspecialchars($crumb['label']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>

            <h1 class="text-3xl md:text-4xl font-bold tracking-wide mb-2"><?= htmlspecialchars($page_title) ?></h1>
            <?php if (!empty($page_description)): ?>
                <p class="text-primary-100 text-sm md:text-base"><?= htmlspecialchars($page_description) ?></p>
            <?php endif; ?>
        </div>
    </div>
</header>
