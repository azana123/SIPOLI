<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/_load.php';
require_once __DIR__ . '/../../middleware/check-role.php';
check_role('pasien');

$page_title       = $page_title ?? "Detail Reservasi";
$page_description = "Lihat rincian jadwal pemeriksaan dan lakukan konfirmasi kehadiran Anda.";
$back_url         = $back_url ?? './reservasi.php';
$breadcrumbs      = $breadcrumbs ?? [
    ['label' => 'Reservasi', 'url' => './reservasi.php'],
    ['label' => $page_title]
];

$reservasiId = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$pasienId    = (int) ($_SESSION['profil_id'] ?? 0);

$detail = null;
try {
    $detail = $reservasiId > 0 ? QueryPasienReservasi::detail($reservasiId, $pasienId) : null;
} catch (ApiException $e) {
    $detail = null;
}

$poliName       = $detail['poli'] ?? '-';
$doctorName     = $detail['dokter'] ?? '-';
$tanggalText    = $detail['tanggal_panjang'] ?? '-';
$jamText        = $detail['jam'] ?? '-';
$keluhanText    = $detail['keluhan'] ?? '-';
$statusText     = $detail['status_label'] ?? '-';
$bisaKonfirmasi = $detail !== null && in_array($detail['status'], ['menunggu', 'dikonfirmasi'], true);

$apakahLogin = isset($_SESSION['user_id'], $_SESSION['role']);
$namaPasien  = null;

if ($apakahLogin) {
    require_once __DIR__ . '/../../middleware/check-role.php';
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | SIPOLI</title>
    <!-- Asumsi kompilasi Tailwind CSS / Flowbite -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama: Diperlebar untuk desktop agar header baru terlihat proporsional -->
    <div class="w-full mx-auto min-h-screen bg-page flex flex-col shadow-xl">

        <!-- Komponen Header Desktop & Mobile -->
        <?php require_once __DIR__ . "./../components/header.php" ?>

        <!-- Main Content -->
        <main class="flex-1 px-5 py-6 overflow-y-auto">
            <!-- Layout 1 kolom di mobile, 2 kolom proporsional di desktop -->
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6 items-start">

                <!-- Kolom Kiri (Desktop): Status, Info Poli & Detail Jadwal -->
                <div class="md:col-span-7 flex flex-col gap-4">

                    <!-- Card: Status Konfirmasi -->
                    <?php if ($detail === null): ?>
                        <div class="bg-red-100 border border-red-300 rounded-base p-4 text-sm text-red-800">
                            Reservasi tidak ditemukan atau bukan milik akun Anda.
                        </div>
                    <?php elseif ($detail['status'] === 'menunggu'): ?>
                        <div class="bg-yellow-100 border border-yellow-300 rounded-base p-4 flex items-center gap-3">
                            <i class="fas fa-history text-yellow-800 text-xl shrink-0"></i>
                            <p class="font-semibold text-yellow-900 text-sm leading-tight">
                                Menunggu konfirmasi dari poliklinik
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="bg-primary-50 border border-primary-200 rounded-base p-4 flex items-center gap-3">
                            <i class="fas fa-info-circle text-primary text-xl shrink-0"></i>
                            <p class="font-semibold text-primary-700 text-sm leading-tight">
                                Status reservasi: <?= e($statusText) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Card: Info Poliklinik & Dokter -->
                    <div class="bg-white border-2 border-default rounded-base p-4 flex items-center gap-4">
                        <!-- Icon Lingkaran -->
                        <div class="w-12 h-12 rounded-full bg-primary-50 shrink-0 flex justify-center items-center">
                            <i class="fas fa-stethoscope text-primary-500 text-xl"></i>
                        </div>
                        <div class="flex-1 flex flex-col">
                            <h3 class="font-bold text-gray-900"><?= e($poliName) ?></h3>
                            <p class="text-xs text-gray-500 mt-0.5"><?= e($doctorName) ?></p>
                        </div>
                    </div>

                    <!-- Card: Detail Jadwal -->
                    <div class="bg-white border-2 border-default rounded-base p-5 flex flex-col gap-3">
                        <!-- Row: Tanggal -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 font-medium">Tanggal</span>
                            <span class="font-semibold text-gray-900"><?= e($tanggalText) ?></span>
                        </div>

                        <!-- Divider Dashed -->
                        <hr class="border-t border-dashed border-gray-300 my-0.5">

                        <!-- Row: Waktu -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 font-medium">Waktu</span>
                            <span class="font-semibold text-gray-900"><?= e($jamText) ?></span>
                        </div>

                        <!-- Divider Dashed -->
                        <hr class="border-t border-dashed border-gray-300 my-0.5">

                        <!-- Row: Keluhan -->
                        <div class="flex justify-between items-start gap-4">
                            <span class="text-sm text-gray-500 font-medium shrink-0">Keluhan</span>
                            <span class="font-semibold text-gray-900 text-right"><?= e($keluhanText) ?></span>
                        </div>
                    </div>

                </div>

                <!-- Kolom Kanan (Desktop): Aksi Konfirmasi & Pembatalan -->
                <div class="md:col-span-5 flex flex-col gap-5">

                    <!-- Card: Aksi Konfirmasi Kehadiran -->
                    <div class="bg-white border-2 border-default rounded-base p-5">
                        <h3 class="font-bold text-gray-900 mb-2">Konfirmasi Kehadiran</h3>
                        <p class="text-[13px] text-gray-500 mb-5 leading-relaxed">
                            Konfirmasi kehadiran, reservasi bisa ditandai tidak hadir.
                        </p>

                        <div class="flex flex-col gap-3">
                            <!-- Button: Hadir -->
                            <button type="button" <?= $bisaKonfirmasi ? '' : 'disabled' ?> data-api-click data-api-endpoint="/api/pasien/reservasi.php" data-api-payload='{"action":"konfirmasi","reservasi_id":<?= $reservasiId ?>, "hadir":true}' class="w-full py-2.5 bg-primary text-white cursor-pointer hover:bg-primary-700 transition-colors duration-300 disabled:bg-gray-200 disabled:text-gray-400 text-sm font-semibold rounded-full disabled:cursor-not-allowed">
                                Saya hadir
                            </button>
                            <!-- Button: Tidak Hadir (Outline State) -->
                            <button type="button" <?= $bisaKonfirmasi ? '' : 'disabled' ?> data-modal-target="modal-ketidakhadiran" data-modal-toggle="modal-ketidakhadiran" class="w-full py-2.5 bg-white border border-primary text-primary cursor-pointer disabled:border-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed hover:bg-primary disabled:hover:bg-white hover:text-white text-sm font-semibold rounded-full transition-colors duration-300">
                                Tidak bisa hadir
                            </button>
                            <!-- Button: Ubah Jadwal -->
                            <button type="button" <?= $bisaKonfirmasi ? '' : 'disabled' ?> onclick="window.location.href='./jadwal-pemeriksaan.php?reservasi_id=<?= $reservasiId ?>'" class="w-full py-2.5 bg-white border border-primary text-primary cursor-pointer disabled:border-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed hover:bg-primary disabled:hover:bg-white hover:text-white text-sm font-semibold rounded-full transition-colors duration-300">
                                Ubah Jadwal
                            </button>
                        </div>
                    </div>

                    <!-- Aksi Pembatalan -->
                    <div class="text-center pb-4">
                        <button type="button" <?= $bisaKonfirmasi ? '' : 'disabled' ?> data-modal-target="modal-konfirmasi-pembatalan" data-modal-toggle="modal-konfirmasi-pembatalan" class="text-sm font-bold text-red-700 hover:text-red-800 hover:underline transition-colors cursor-pointer disabled:text-gray-400 disabled:no-underline disabled:cursor-not-allowed">
                            Batalkan reservasi
                        </button>
                    </div>

                </div>

            </div>
        </main>
    </div>

    <?php require_once __DIR__ . "/../components/api-script.php" ?>
    <?php require_once __DIR__ . "/../components/reservasi/konfirmasi-ketidakhadiran.php" ?>
    <?php require_once __DIR__ . "/../components/reservasi/pembatalan-reservasi.php" ?>

    <script>
        // Handle "Tidak bisa hadir" -> kirim konfirmasi hadir=false dengan alasan
        const btnKirimTidakHadir = document.getElementById('btn-kirim-ketidakhadiran');
        if (btnKirimTidakHadir) {
            btnKirimTidakHadir.addEventListener('click', async function() {
                const textarea = document.getElementById('alasan_reschedule');
                const alasan = textarea ? textarea.value.trim() : '';
                const payload = {
                    action: 'konfirmasi',
                    reservasi_id: <?= $reservasiId ?>,
                    hadir: false,
                    alasan: alasan
                };

                try {
                    const tokenRes = await fetch('./../../api/csrf.php', {
                        credentials: 'same-origin'
                    });
                    const tokenJson = await tokenRes.json();
                    const token = tokenJson.data.csrf_token;

                    const res = await fetch('./../../api/pasien/reservasi.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': token
                        },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();

                    if (json.success) {
                        const modal = document.getElementById('modal-ketidakhadiran');
                        if (modal) modal.classList.add('hidden');
                        window.location.reload();
                    } else {
                        alert(json.message || 'Gagal mengirim konfirmasi.');
                    }
                } catch (err) {
                    alert('Terjadi kesalahan: ' + err.message);
                }
            });
        }

        // Handle "Ya, batalkan" -> kirim pembatalan dengan alasan
        const btnYaBatalkan = document.getElementById('btn-ya-batalkan');
        if (btnYaBatalkan) {
            btnYaBatalkan.addEventListener('click', async function() {
                const textarea = document.getElementById('alasan_batal');
                const alasan = textarea ? textarea.value.trim() : '';
                const payload = {
                    action: 'batal',
                    reservasi_id: <?= $reservasiId ?>,
                    alasan: alasan
                };

                try {
                    const tokenRes = await fetch('./../../api/csrf.php', {
                        credentials: 'same-origin'
                    });
                    const tokenJson = await tokenRes.json();
                    const token = tokenJson.data.csrf_token;

                    const res = await fetch('./../../api/pasien/reservasi.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': token
                        },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();

                    if (json.success) {
                        const modal = document.getElementById('modal-konfirmasi-pembatalan');
                        if (modal) modal.classList.add('hidden');
                        window.location.reload();
                    } else {
                        alert(json.message || 'Gagal membatalkan reservasi.');
                    }
                } catch (err) {
                    alert('Terjadi kesalahan: ' + err.message);
                }
            });
        }
    </script>

    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../../assets/js/flowbite-init.js"></script>
</body>

</html>