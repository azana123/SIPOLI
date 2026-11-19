<!-- App Bar (Mobile) -->
<header class="bg-primary-500 text-white px-5 py-4 flex items-center sticky top-0 z-20 shadow-sm md:hidden">
    <a href="<?= htmlspecialchars($back_url) ?>" class="mr-4 hover:bg-primary-600 p-1 rounded transition-colors">
        <i class="fas fa-chevron-left text-lg"></i>
    </a>
    <h1 class="text-lg font-semibold tracking-wide"><?= htmlspecialchars($page_title) ?></h1>
</header>

<!-- Header (Desktop) -->
<header class="hidden md:flex flex-col bg-primary-500 text-white px-8 md:px-20 shadow-sm h-[50vh]">
    <!-- Navbar Atas -->
    <section class="flex justify-between items-center w-full py-4 text-page relative z-20 pointer-events-auto">
        <div class="brand text-xl font-bold tracking-wider">
            SIPOLI
        </div>
        <div class="flex items-center gap-5">
            <a href="./../../auth/login.php" class="profile flex gap-2 items-center bg-primary px-3 py-1.5 rounded-base hover:bg-primary-700 transition">
                <div class="icon-profile text-lg">
                    <i class="fas fa-user-circle"></i>
                </div>
                <span class="font-medium">login</span>
            </a>
            <a href="./../notifikasi.php" class="notifikasi text-lg px-3 py-1.5 bg-primary hover:bg-primary-700 rounded-full">
                <i class="fas fa-bell"></i>
            </a>
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