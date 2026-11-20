<?php
$page_title       = $page_title ?? 'Jadwal Dokter';
$page_description = "Cek ketersediaan jadwal praktik dokter dan pilih waktu kunjungan Anda.";
$back_url         = $back_url ?? './../index.php';
$breadcrumbs      = $breadcrumbs ?? [
    ['label' => $page_title]
];

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/_load.php';
require_once __DIR__ . '/../middleware/check-role.php';
check_role('pasien');

$reservasiId = isset($_GET['reservasi_id']) && ctype_digit((string) $_GET['reservasi_id']) ? (int) $_GET['reservasi_id'] : 0;

$hariIni = $pdo->query('SELECT fn_today()::text')->fetchColumn() ?: date('Y-m-d');
$tanggalDipilih = isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['tanggal'])
    ? (string) $_GET['tanggal']
    : $hariIni;
$poliId = isset($_GET['poli_id']) && ctype_digit((string) $_GET['poli_id']) ? (int) $_GET['poli_id'] : 0;

$jadwal = QueryPasienJadwal::daftar($tanggalDipilih, $poliId);

$hariMap  = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
$bulanMap = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$labelBulan = $bulanMap[(int) date('n', strtotime($tanggalDipilih))] . ' ' . date('Y', strtotime($tanggalDipilih));
$labelTanggal = $hariMap[(int) date('N', strtotime($tanggalDipilih))] . ', ' . (int) date('j', strtotime($tanggalDipilih)) . ' ' . $bulanMap[(int) date('n', strtotime($tanggalDipilih))];

// Tampilkan 7 hari ke depan (jendela reservasi H-7 s.d. H-1)
$daftarTanggal = [];
for ($i = 1; $i <= 7; $i++) {
    $t = date('Y-m-d', strtotime($hariIni . ' +' . $i . ' day'));
    $daftarTanggal[] = [
        'tanggal' => $t,
        'hari'    => $hariMap[(int) date('N', strtotime($t))],
        'angka'   => (int) date('j', strtotime($t)),
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | SIPOLI</title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <div class="w-full mx-auto min-h-screen bg-page flex flex-col shadow-xl">

        <?php require_once __DIR__ . "/components/header.php" ?>

        <main class="flex-1 px-5 md:px-20 py-6 overflow-y-auto">
            <section class="content w-full" id="jadwal-app">

                <div class="category">
                    <ul class="flex justify-between md:justify-start md:gap-3">
                        <li>
                            <input type="radio" id="semua" name="hosting" value="" onchange="location.href='?'" <?= $poliId === 0 ? 'checked' : '' ?> class="hidden peer" required />
                            <label for="semua" class="inline-flex items-center bg-primary-500 justify-between text-sm py-3 px-4 rounded-full text-white bg-neutral-primary-soft border-default cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Semua</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="umum" name="hosting" value="1" onchange="location.href='?poli_id=1'" <?= $poliId === 1 ? 'checked' : '' ?> class="hidden peer" required />
                            <label for="umum" class="inline-flex items-center justify-between text-sm py-3 px-4 rounded-full text-body bg-neutral-primary-soft border-1 border-default rounded-base cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Umum</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="gigi" name="hosting" value="2" onchange="location.href='?poli_id=2'" <?= $poliId === 2 ? 'checked' : '' ?> class="hidden peer" required />
                            <label for="gigi" class="inline-flex items-center justify-between text-sm py-3 px-4 rounded-full text-body bg-neutral-primary-soft border-1 border-default rounded-base cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Gigi</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="konseling" name="hosting" value="3" onchange="location.href='?poli_id=3'" <?= $poliId === 3 ? 'checked' : '' ?> class="hidden peer" required />
                            <label for="konseling" class="inline-flex items-center justify-between text-sm py-3 px-4 rounded-full text-body bg-neutral-primary-soft border-1 border-default rounded-base cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Konseling</div>
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
                <h4 class="month my-3 text-xl font-bold"><?= e($labelBulan) ?></h4>
                <div class="date-filter grid grid-cols-5 gap-3">
                    <?php foreach ($daftarTanggal as $t): ?>
                        <a href="?tanggal=<?= e($t['tanggal']) ?><?= $poliId > 0 ? '&poli_id=' . $poliId : '' ?>" class="<?= $t['tanggal'] === $tanggalDipilih ? 'bg-primary-500 text-white' : 'bg-white' ?> rounded flex flex-col items-center justify-center p-1.5 text-sm">
                            <span class="day"><?= e($t['hari']) ?></span>
                            <span class="date"><?= (int) $t['angka'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p class="date text-primary-700 font-medium my-3"><?= e($labelTanggal) ?></p>
                <div class="schedule md:grid md:grid-cols-3 md:gap-3">
                    <?php if (!$jadwal): ?>
                        <div class="bg-neutral-primary-soft w-full p-6 rounded-base text-sm text-gray-500 md:col-span-3">
                            Belum ada jadwal yang tersedia untuk tanggal ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($jadwal as $j): ?>
                            <div class="bg-neutral-primary-soft w-full p-6 rounded-base flex flex-col gap-4 mb-4 md:mb-0">
                                <div class="flex gap-3">
                                    <div class="icon w-12 h-12 rounded-full bg-primary-100 flex justify-center items-center">
                                        <i class="fas fa-calendar-alt text-primary text-2xl"></i>
                                    </div>
                                    <div class="desc flex-1">
                                        <h6 class="font-bold"><?= e($j['poli']) ?></h6>
                                        <p class="doctor-name text-sm"><?= e($j['dokter'] ?? 'Dokter akan ditentukan') ?></p>
                                        <p class="date text-sm"><?= e($j['tanggal_pendek']) ?>. <?= e($j['jam']) ?></p>
                                    </div>
                                    <?php if ($j['penuh']): ?>
                                        <div class="status-badge">
                                            <span class="bg-danger-soft text-fg-danger text-xs font-medium px-2 py-1 rounded">Penuh</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($j['penuh']): ?>
                                    <span class="action-link text-gray-300 bg-neutral-secondary-medium box-border border border-default-medium shadow-xs font-medium text-center rounded-full text-sm px-4 py-2.5">
                                        Kuota Penuh
                                    </span>
                                <?php elseif ($reservasiId > 0): ?>
                                    <button type="button"
                                            data-api-click
                                            data-api-endpoint="/api/pasien/reservasi.php"
                                            data-api-payload='{"action":"reschedule","reservasi_id":<?= $reservasiId ?>,"jadwal_baru_id":<?= (int) $j['id'] ?>}'
                                            data-api-redirect="./reservasi/detail-reservasi.php?id=<?= $reservasiId ?>"
                                            class="action-link text-page text-center rounded-full text-sm px-4 py-2.5 bg-primary-500 hover:bg-primary-700">
                                        <span class="font-medium">Ubah Jadwal</span>
                                    </button>
                                <?php else: ?>
                                    <a href="./reservasi/form-reservasi.php?jadwal_id=<?= (int) $j['id'] ?>" class="action-link text-page text-center rounded-full text-sm px-4 py-2.5 bg-primary-500 hover:bg-primary-700">
                                        <span class="font-medium">Reservasi</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </section>
        </main>
    </div>

    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../assets/js/flowbite-init.js"></script>
</body>

</html>
