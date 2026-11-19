<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/services/_load.php';

$jadwalPublik = QueryPasienJadwal::daftar(null, 0);
$dokterPublik = QueryPasienDokter::daftar();

$apakahLogin = isset($_SESSION['user_id'], $_SESSION['role']);
$namaPasien  = null;

if ($apakahLogin) {
    require_once __DIR__ . '/middleware/check-role.php';
    check_role(['admin', 'dokter', 'pasien']);

    $peran = (string) ($_SESSION['role'] ?? '');
    if ($peran !== 'pasien') {
        redirect_by_role($peran);
    }

    try {
        $stmtNama = $pdo->prepare('SELECT nama FROM pasien WHERE user_id = :uid AND is_active = TRUE LIMIT 1');
        $stmtNama->execute([':uid' => (int) $_SESSION['user_id']]);
        $namaPasien = (string) ($stmtNama->fetchColumn() ?: ($_SESSION['username'] ?? 'Pasien'));
    } catch (PDOException $e) {
        $namaPasien = (string) ($_SESSION['username'] ?? 'Pasien');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOLI</title>
    <!-- style.css -->
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="w-full min-h-screen bg-primary-500 relative">

        <!-- Desktop Hero Section (Absolute Background) -->
        <section class="hidden md:block absolute top-0 left-0 w-full h-screen z-0">
            <div id="controls-carousel-desktop" class="relative w-full h-full" data-carousel="static">
                <div class="relative w-full h-full overflow-hidden">
                    <div class="hidden duration-700 ease-in-out transition-transform h-full" data-carousel-item="active">
                        <img src="./assets/img/modal-img.png" class="block w-full h-full object-cover object-center" alt="Konsultasi Mental Health">
                        <div class="absolute inset-0 flex flex-col justify-center items-start pl-16 pr-24 z-10 bg-linear-to-r from-teal-900/60 via-teal-900/20 to-transparent">
                            <div class="inline-flex items-center gap-1.5 bg-[#0e5c68]/90 text-white rounded-full pr-3 pl-1 py-1 text-xs font-semibold mb-3">
                                <span class="w-5 h-5 rounded-full bg-[#1e8e9e] flex items-center justify-center">
                                    <i class="far fa-calendar-alt text-[11px] text-white"></i>
                                </span>
                                <span class="tracking-wide">Konsultasi Online</span>
                            </div>
                            <h2 class="text-white font-extrabold text-3xl md:text-4xl lg:text-5xl leading-tight mb-2">
                                Konsultasi <span class="block text-[#a0f2ff]">Mental Health</span>
                            </h2>
                            <p class="text-white/90 text-sm md:text-base max-w-[50%] leading-snug mb-4">
                                Konsultasi dengan dokter dengan mudah dan cepat.
                            </p>
                            <a href="./users/jadwal-pemeriksaan.php?poli=konseling" class="bg-white text-[#0e5c68] hover:bg-neutral-100 transition font-bold text-xs md:text-sm px-5 py-2.5 rounded-full shadow-md">
                                Konsultasi Sekarang
                            </a>
                        </div>
                    </div>
                    <div class="hidden duration-700 ease-in-out transition-transform h-full" data-carousel-item>
                        <img src="./assets/img/modal-img.png" class="block w-full h-full object-cover object-center" alt="Buat Janji Dengan Dokter">
                        <div class="absolute inset-0 flex flex-col justify-center items-start pl-16 pr-24 z-10 bg-linear-to-r from-teal-900/60 via-teal-900/20 to-transparent">
                            <div class="inline-flex items-center gap-1.5 bg-[#0e5c68]/90 text-white rounded-full pr-3 pl-1 py-1 text-xs font-semibold mb-3">
                                <span class="w-5 h-5 rounded-full bg-[#1e8e9e] flex items-center justify-center">
                                    <i class="fas fa-user-md text-[11px] text-white"></i>
                                </span>
                                <span class="tracking-wide">Mudah & Fleksibel</span>
                            </div>
                            <h2 class="text-white font-extrabold text-3xl md:text-4xl lg:text-5xl leading-tight mb-2">
                                Buat Janji <span class="block text-[#a0f2ff]">Dengan Dokter</span>
                            </h2>
                            <p class="text-white/90 text-sm md:text-base max-w-[50%] leading-snug">
                                Pilih jadwal konsultasi yang sesuai untukmu.
                            </p>
                        </div>
                    </div>
                    <div class="hidden duration-700 ease-in-out transition-transform h-full" data-carousel-item>
                        <img src="./assets/img/modal-img.png" class="block w-full h-full object-cover object-center" alt="Butuh Bantuan Medis">
                        <div class="absolute inset-0 flex flex-col justify-center items-start pl-16 pr-24 z-10 bg-linear-to-r from-teal-900/60 via-teal-900/20 to-transparent">
                            <div class="inline-flex items-center gap-1.5 bg-[#0e5c68]/90 text-white rounded-full pr-3 pl-1 py-1 text-xs font-semibold mb-3">
                                <span class="w-5 h-5 rounded-full bg-[#1e8e9e] flex items-center justify-center">
                                    <i class="fas fa-user-md text-[11px] text-white"></i>
                                </span>
                                <span class="tracking-wide">Dokter Profesional</span>
                            </div>
                            <h2 class="text-white font-extrabold text-3xl md:text-4xl lg:text-5xl leading-tight mb-2">
                                Butuh Bantuan <span class="block text-[#a0f2ff]">Medis?</span>
                            </h2>
                            <p class="text-white/90 text-sm md:text-base max-w-[50%] leading-snug">
                                Temukan dokter yang sesuai dengan kebutuhanmu.
                            </p>
                        </div>
                    </div>
                </div>
                <button type="button" class="absolute top-0 inset-s-0 z-40 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-2 group-focus:ring-white group-focus:outline-none">
                        <svg class="w-5 h-5 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7" />
                        </svg>
                        <span class="sr-only">Previous</span>
                    </span>
                </button>
                <button type="button" class="absolute top-0 inset-e-0 z-50 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-2 group-focus:ring-white group-focus:outline-none">
                        <svg class="w-5 h-5 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                        </svg>
                        <span class="sr-only">Next</span>
                    </span>
                </button>
            </div>
        </section>

        <!-- Main Wrapper (pointer-events-none agar klik tembus ke carousel desktop di belakangnya) -->
        <main class="relative z-10 w-full pointer-events-none">

            <!-- Header / Navbar Overlay -->
            <header class="flex justify-between items-center w-full py-4 px-6 md:px-20 text-page relative z-30 pointer-events-auto">
                <a href="./index.php" class="brand text-xl font-bold tracking-wider">
                    SIPOLI
                </a>

                <div class="flex items-center gap-4">
                    <?php if ($apakahLogin): ?>
                        <!-- Tombol Notifikasi (hanya muncul jika login) -->
                        <a href="./users/notifikasi.php" class="notifikasi text-lg w-10 h-10 flex items-center justify-center bg-primary hover:bg-primary-700 rounded-full transition" aria-label="Notifikasi">
                            <i class="fas fa-bell"></i>
                        </a>

                        <!-- Dropdown Profil Pasien -->
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
                                        <a href="./auth/logout.php" class="inline-flex items-center w-full p-2 text-red-600 hover:bg-red-50 rounded" role="menuitem">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="./auth/login.php" class="profile flex gap-2 items-center bg-primary px-4 py-1.5 rounded-base hover:bg-primary-700 transition">
                            <i class="fas fa-user-circle text-lg"></i>
                            <span class="font-medium">Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Full-Width Content Sheet -->
            <section class="content w-full bg-page rounded-t-2xl py-6 px-6 md:px-20 min-h-screen relative z-10 md:mt-[80vh] shadow-[0_-4px_10px_-2px_rgba(0,0,0,0.1)] pointer-events-auto">

                <!-- Section Mobile Only -->
                <div class="md:hidden top-content flex justify-between items-center mt-2">
                    <h4 class="font-bold text-lg">Jadwal Dokter</h4>
                    <a href="./users/jadwal-pemeriksaan.php" class="action-link text-primary-400 hover:text-primary-700 flex items-center gap-1">
                        <span class="font-medium">Lihat Semua</span>
                        <i class="fas fa-chevron-right text-sm"></i>
                    </a>
                </div>

                <!-- Mobile Hero Section -->
                <section class="hero rounded-2xl my-3 overflow-hidden md:hidden">
                    <div id="controls-carousel" class="relative w-full" data-carousel="static">
                        <div class="relative w-full aspect-[2.1/1] overflow-hidden rounded-2xl">
                            <div class="hidden duration-700 ease-in-out transition-transform" data-carousel-item="active">
                                <img src="./assets/img/modal-img.png" class="block w-full h-full object-cover object-center" alt="Konsultasi Mental Health">
                                <div class="absolute inset-0 flex flex-col justify-center items-start pl-10 pr-16 z-10 bg-linear-to-r from-teal-900/40 via-transparent to-transparent">
                                    <div class="inline-flex items-center gap-1.5 bg-[#0e5c68]/90 text-white rounded-full pr-3 pl-1 py-1 text-[9px] font-semibold mb-1.5">
                                        <span class="w-4 h-4 rounded-full bg-[#1e8e9e] flex items-center justify-center">
                                            <i class="far fa-calendar-alt text-[8px] text-white"></i>
                                        </span>
                                        <span class="tracking-wide">Konsultasi Online</span>
                                    </div>
                                    <h2 class="text-white font-extrabold text-sm sm:text-base leading-tight mb-1">
                                        Konsultasi <span class="block text-[#a0f2ff]">Mental Health</span>
                                    </h2>
                                    <p class="text-white/90 text-[8px] sm:text-2xs max-w-[60%] leading-snug mb-2">
                                        Konsultasi dengan dokter dengan mudah dan cepat.
                                    </p>
                                    <a href="./users/jadwal-pemeriksaan.php" class="bg-white text-[#0e5c68] hover:bg-neutral-100 transition font-bold text-[8px] sm:text-2xs px-3 py-1.5 rounded-full shadow-md">
                                        Konsultasi Sekarang
                                    </a>
                                </div>
                            </div>
                            <div class="hidden duration-700 ease-in-out transition-transform" data-carousel-item>
                                <img src="./assets/img/modal-img.png" class="block w-full h-full object-cover object-center" alt="Buat Janji Dengan Dokter">
                                <div class="absolute inset-0 flex flex-col justify-center items-start pl-10 pr-16 z-10 bg-linear-to-r from-teal-900/40 via-transparent to-transparent">
                                    <div class="inline-flex items-center gap-1.5 bg-[#0e5c68]/90 text-white rounded-full pr-3 pl-1 py-1 text-[9px] font-semibold mb-1.5">
                                        <span class="w-4 h-4 rounded-full bg-[#1e8e9e] flex items-center justify-center">
                                            <i class="fas fa-user-md text-[8px] text-white"></i>
                                        </span>
                                        <span class="tracking-wide">Mudah & Fleksibel</span>
                                    </div>
                                    <h2 class="text-white font-extrabold text-sm sm:text-base leading-tight mb-1">
                                        Buat Janji <span class="block text-[#a0f2ff]">Dengan Dokter</span>
                                    </h2>
                                    <p class="text-white/90 text-[8px] sm:text-2xs max-w-[60%] leading-snug">
                                        Pilih jadwal konsultasi yang sesuai untukmu.
                                    </p>
                                </div>
                            </div>
                            <div class="hidden duration-700 ease-in-out transition-transform" data-carousel-item>
                                <img src="./assets/img/modal-img.png" class="block w-full h-full object-cover object-center" alt="Butuh Bantuan Medis">
                                <div class="absolute inset-0 flex flex-col justify-center items-start pl-10 pr-16 z-10 bg-linear-to-r from-teal-900/40 via-transparent to-transparent">
                                    <div class="inline-flex items-center gap-1.5 bg-[#0e5c68]/90 text-white rounded-full pr-3 pl-1 py-1 text-[9px] font-semibold mb-1.5">
                                        <span class="w-4 h-4 rounded-full bg-[#1e8e9e] flex items-center justify-center">
                                            <i class="fas fa-user-md text-[8px] text-white"></i>
                                        </span>
                                        <span class="tracking-wide">Dokter Profesional</span>
                                    </div>
                                    <h2 class="text-white font-extrabold text-sm sm:text-base leading-tight mb-1">
                                        Butuh Bantuan <span class="block text-[#a0f2ff]">Medis?</span>
                                    </h2>
                                    <p class="text-white/90 text-[8px] sm:text-2xs max-w-[60%] leading-snug">
                                        Temukan dokter yang sesuai dengan kebutuhanmu.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="absolute top-0 inset-s-0 z-40 flex items-center justify-center h-full px-2 cursor-pointer group focus:outline-none" data-carousel-prev>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-2 group-focus:ring-white group-focus:outline-none">
                                <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7" />
                                </svg>
                                <span class="sr-only">Previous</span>
                            </span>
                        </button>
                        <button type="button" class="absolute top-0 inset-e-0 z-40 flex items-center justify-center h-full px-2 cursor-pointer group focus:outline-none" data-carousel-next>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-2 group-focus:ring-white group-focus:outline-none">
                                <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                                </svg>
                                <span class="sr-only">Next</span>
                            </span>
                        </button>
                    </div>
                </section>

                <!-- Kategori Layanan Poliklinik -->
                <section class="category my-5 md:my-6">
                    <h4 class="title font-bold text-lg md:hidden">Kategori</h4>
                    <h4 class="title font-bold text-2xl text-center hidden md:block">Layanan Poliklinik</h4>
                    <div class="grid grid-cols-3 gap-3 md:gap-6 mt-3 md:mt-5">
                        <!-- Card Gigi -->
                        <a href="./users/jadwal-pemeriksaan.php?poli=gigi" class="group flex flex-col items-center justify-center bg-neutral-primary-soft p-4 md:py-6 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-primary transition-all duration-300 ease-in-out">
                            <div class="icon mb-2 md:mb-3">
                                <i class="fas fa-tooth text-3xl md:text-4xl text-primary group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <h5 class="text-base md:text-lg tracking-tight text-heading font-medium group-hover:text-white transition-colors duration-300 text-center">Gigi</h5>
                        </a>

                        <!-- Card Umum -->
                        <a href="./users/jadwal-pemeriksaan.php?poli=umum" class="group flex flex-col items-center justify-center bg-neutral-primary-soft p-4 md:py-6 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-primary transition-all duration-300 ease-in-out">
                            <div class="icon mb-2 md:mb-3">
                                <i class="fas fa-stethoscope text-3xl md:text-4xl text-primary group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <h5 class="text-base md:text-lg tracking-tight text-heading font-medium group-hover:text-white transition-colors duration-300 text-center">Umum</h5>
                        </a>

                        <!-- Card Konseling -->
                        <a href="./users/jadwal-pemeriksaan.php?poli=konseling" class="group flex flex-col items-center justify-center bg-neutral-primary-soft p-4 md:py-6 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-primary transition-all duration-300 ease-in-out">
                            <div class="icon mb-2 md:mb-3">
                                <i class="fas fa-brain text-3xl md:text-4xl text-primary group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <h5 class="text-base md:text-lg tracking-tight text-heading font-medium group-hover:text-white transition-colors duration-300 text-center">Konseling</h5>
                        </a>
                    </div>
                </section>

                <!-- Banner CTA Desktop -->
                <section class="hidden md:flex bg-neutral-primary-soft border border-primary rounded-2xl p-8 mt-5 mb-10 items-center justify-between shadow-md/50 shadow-primary-400 relative overflow-hidden group">
                    <div class="absolute left-1/3 -bottom-10 w-40 h-40 bg-primary-100 rounded-full blur-2xl opacity-60 z-0"></div>
                    <div class="absolute -right-10 -top-10 w-64 h-64 bg-primary-100 rounded-full blur-3xl opacity-50 z-0"></div>
                    <div class="absolute -right-40 -top-10 w-64 h-64 bg-primary-100 rounded-full blur-3xl opacity-50 z-0"></div>

                    <div class="w-3/5 pr-8 relative z-10 flex flex-col items-start">
                        <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-3 py-1.5 rounded-full text-xs font-bold mb-4">
                            <i class="far fa-calendar-check"></i>
                            Reservasi Cepat & Mudah
                        </div>
                        <h3 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-3 leading-snug">
                            Cek & Atur Jadwal <br>
                            <span class="text-primary">Pemeriksaan Dokter</span>
                        </h3>
                        <p class="text-gray-600 text-sm lg:text-base mb-6 leading-relaxed max-w-lg">
                            Dapatkan informasi lengkap tentang jadwal praktik dokter spesialis, poliklinik umum, dan layanan mental health kami. Booking waktu konsultasi Anda sekarang tanpa harus antre!
                        </p>
                        <a href="./users/jadwal-pemeriksaan.php" class="inline-flex items-center gap-2 bg-primary text-white hover:bg-primary-700 transition duration-300 font-bold px-6 py-3 rounded-full shadow-lg hover:shadow-primary-400/50 hover:-translate-y-1">
                            <span>Lihat Jadwal Dokter</span>
                            <i class="fas fa-arrow-right text-sm"></i>
                        </a>
                    </div>

                    <div class="w-2/5 flex justify-center lg:justify-end relative z-10">
                        <div class="relative rounded-2xl overflow-hidden shadow-xl border-4 border-white group-hover:scale-105 transition-transform duration-500 ease-in-out">
                            <img src="./assets/img/modal-img.png" alt="Ilustrasi Jadwal Dokter" class="w-full max-w-[300px] aspect-[4/3] object-cover object-center">
                            <div class="absolute inset-0 bg-linear-to-t from-black/40 to-transparent"></div>
                        </div>
                    </div>
                </section>

                <!-- Jadwal & Reservasi -->
                <section class="schedule my-6">
                    <div class="title flex justify-between items-center">
                        <h4 class="font-bold text-lg">Jadwal & Reservasi</h4>
                        <a href="./users/reservasi/reservasi.php" class="action-link text-primary-400 transition hover:text-primary-700 flex items-center gap-1">
                            <span class="font-medium">Lihat Semua</span>
                            <i class="fas fa-chevron-right text-sm"></i>
                        </a>
                    </div>
                    <div class="content mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php if (!$jadwalPublik): ?>
                            <p class="text-sm text-gray-500">Belum ada jadwal yang dipublikasikan.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($jadwalPublik, 0, 3) as $j): ?>
                                <div class="bg-neutral-primary-soft p-4 md:p-5 border rounded-base border-primary shadow-md/50 shadow-primary-400 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="icon w-12 h-12 shrink-0 rounded-full bg-primary-100 flex justify-center items-center">
                                            <i class="far fa-calendar-alt text-primary text-xl md:text-2xl"></i>
                                        </div>
                                        <div class="desc min-w-0">
                                            <h6 class="font-bold text-lg md:text-xl truncate"><?= e($j['poli']) ?></h6>
                                            <p class="doctor-name text-sm text-gray-700 truncate"><?= e($j['dokter'] ?? 'Dokter akan ditentukan') ?></p>
                                            <p class="date text-xs text-gray-500 mt-0.5"><?= e($j['tanggal_pendek']) ?>. <?= e($j['jam']) ?></p>
                                        </div>
                                    </div>
                                    <div class="status-badge shrink-0">
                                        <span class="<?= $j['penuh'] ? 'bg-danger-soft text-fg-danger' : 'bg-warning-soft text-fg-warning' ?> text-xs md:text-sm font-medium px-2.5 py-1 rounded">
                                            <?= $j['penuh'] ? 'Penuh' : 'Sisa ' . (int) $j['sisa_kuota'] ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Our Doctor -->
                <section class="doctor-list pb-10">
                    <div class="title flex justify-between items-center">
                        <h4 class="font-bold text-lg">Our Doctor</h4>
                        <a href="./users/jadwal-pemeriksaan.php" class="action-link text-primary-400 transition hover:text-primary-700 flex items-center gap-1">
                            <span class="font-medium">Lihat Semua</span>
                            <i class="fas fa-chevron-right text-sm"></i>
                        </a>
                    </div>

                    <div class="content mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php if (!$dokterPublik): ?>
                            <p class="text-sm text-gray-500">Belum ada dokter yang ditampilkan.</p>
                        <?php else: ?>
                            <?php foreach ($dokterPublik as $d): ?>
                                <a href="./users/jadwal-pemeriksaan.php" class="group bg-white p-4 border border-primary rounded-2xl shadow-md/50 shadow-primary-400 flex items-center justify-between gap-3.5 transition-all hover:bg-primary-50/40">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="w-20 h-20 md:w-22 md:h-22 shrink-0 rounded-2xl overflow-hidden bg-primary-100">
                                            <img src="./assets/img/doc-pic.png" alt="<?= e($d['nama']) ?>" class="w-full h-full object-cover object-top">
                                        </div>
                                        <div class="desc flex flex-col min-w-0">
                                            <h6 class="font-bold text-gray-900 text-base md:text-lg leading-snug truncate">
                                                <?= e($d['nama']) ?>
                                            </h6>
                                            <p class="role text-sm text-gray-500 mt-0.5 truncate">
                                                <?= e($d['poli']) ?>
                                            </p>
                                            <div class="flex items-center gap-2 text-xs md:text-sm text-gray-600 mt-2">
                                                <i class="fas fa-history text-primary-600 w-4 text-center shrink-0"></i>
                                                <span class="truncate"><?= e($d['jam']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="icon shrink-0 text-primary-600 text-lg group-hover:translate-x-1 transition-transform">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script src="./../assets/js/app.js"></script>
    <script src="./node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>