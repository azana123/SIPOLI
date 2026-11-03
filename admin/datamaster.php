<?php
require_once __DIR__ . "/../config/config.php";
$page_title = "Datamaster";
$app_name = "SIPOLI";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= $app_name ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Fontawesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <!-- Perubahan: Mengganti 'hidden md:block' menjadi 'fixed md:relative' agar dapat difungsikan sebagai drawer oleh flowbite di mode mobile -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6">
                <!-- datamaster -->
                <div class="title-datamaster mb-2 text-gray-500 font-medium">
                    <p>Kelola data civitas akademika & layanan · Politeknik Negeri Malang</p>
                </div>
                <div class="datamaster grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div class="pasien">
                        <input type="radio" id="pasien" name="datamaster" value="pasien" class="hidden peer" required checked />
                        <label for="pasien" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Pasien</div>
                                <div class="w-full text-sm text-gray-500">Mahasiswa & pegawai</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                128
                            </div>
                        </label>
                    </div>
                    <div class="tenaga-layanan">
                        <input type="radio" id="tenaga-layanan" name="datamaster" value="tenaga-layanan" class="hidden peer" required />
                        <label for="tenaga-layanan" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Dokter / Psikologi</div>
                                <div class="w-full text-sm text-gray-500">Tenaga Layanan</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                128
                            </div>
                        </label>
                    </div>
                    <div class="poli">
                        <input type="radio" id="poli" name="datamaster" value="poli" class="hidden peer" required />
                        <label for="poli" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Poli</div>
                                <div class="w-full text-sm text-gray-500">Layanan medis tersedia</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                3
                            </div>
                        </label>
                    </div>
                    <div class="admin">
                        <input type="radio" id="admin" name="datamaster" value="admin" class="hidden peer" required />
                        <label for="admin" class="inline-flex items-center justify-between gap-2 w-full p-4 bg-neutral-primary-soft border border-default rounded-base focus:border focus:border-primary cursor-pointer hover:bg-primary-50 peer-checked:bg-primary-50 peer-checked:border-[#12616b] transition-colors">
                            <div class="block">
                                <div class="w-full font-bold text-primary">Admin</div>
                                <div class="w-full text-sm text-gray-500">Pengelola SIPOLI</div>
                            </div>
                            <div class="font-bold text-primary self-start text-sm">
                                3
                            </div>
                        </label>
                    </div>
                </div>
                <!-- End of datamaster -->

                <!-- Table Daftar Pasien -->
                <?php require_once __DIR__ . '/components/datamaster/card-data-pasien.php'; ?>
                <?php require_once __DIR__ . '/components/datamaster/card-data-poli.php'; ?>
                <?php require_once __DIR__ . '/components/datamaster/card-data-tenaga-layanan.php'; ?>
                <?php require_once __DIR__ . '/components/datamaster/card-data-admin.php'; ?>
            </main>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/detail-reservasi.php'; ?>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>