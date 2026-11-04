<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-full h-screen transition-transform -translate-x-full md:translate-x-0 md:relative md:h-auto" aria-label="Sidebar">
    <div class="h-full px-3 py-4 bg-primary-500 border-e border-default">
        <a href="#" class="flex items-center ps-2.5 mb-5">
            <h3 class="text-white font-bold text-3xl">SIPOLI</h3>
        </a>
        <ul class="space-y-2 font-medium">
            <li>
                <a href="<?= BASE_URL ?>/dashboard.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 btn-active">
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/jadwal.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Jadwal & Kuota</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/reservasi.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Reservasi</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/datamaster.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Data Master</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/konfirmasi.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Konfirmasi H-1</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/obat.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Obat & Stok</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/resep.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Resep</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/kirim-informasi.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Kirim Informasi</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/notifikasi.php" class="flex items-center px-3 py-2.5 text-white rounded-base hover:bg-primary-400 ">
                    <span class="ms-3">Notifikasi</span>
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