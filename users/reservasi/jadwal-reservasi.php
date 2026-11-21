<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/_load.php';
require_once __DIR__ . '/../../middleware/check-role.php';
check_role('pasien');

$page_title = $page_title ?? 'Jadwal Dokter';
$page_description = "Cek ketersediaan jadwal praktik dokter dan pilih waktu kunjungan Anda.";
$back_url = $back_url ?? './../index.php';
$breadcrumbs = $breadcrumbs ?? [
    ['label' => $page_title]
];

date_default_timezone_set('Asia/Jakarta');

$today = new DateTime('today');
$maxActiveDate = (clone $today)->modify('+6 days');

$month = isset($_GET['m']) ? max(1, min(12, (int)$_GET['m'])) : (int)$today->format('n');
$year  = isset($_GET['y']) ? (int)$_GET['y'] : (int)$today->format('Y');

$doctorId   = isset($_GET['dokter_id']) && ctype_digit((string) $_GET['dokter_id']) ? (int) $_GET['dokter_id'] : 0;
$scheduleId = isset($_GET['jadwal_id']) && ctype_digit((string) $_GET['jadwal_id']) ? (int) $_GET['jadwal_id'] : 0;

// Daftar tanggal penuh dari kalender (sudah dihitung dari $kalender di bawah)
$fullQuotaDates = [];

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear  = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear  = $month === 12 ? $year + 1 : $year;

$daysInMonth     = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$prevMonthDays   = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);
$firstDayOfMonth = (int)date('w', strtotime(sprintf('%04d-%02d-01', $year, $month)));

$bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$monthName = $bulanIndo[$month - 1];

$selectedDateStr = isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['tanggal'])
    ? (string) $_GET['tanggal']
    : '';

$kalender = QueryPasienJadwal::kalender($month, $year);
$jadwalBulan = QueryPasienJadwal::daftar(sprintf('%04d-%02d-01', $year, $month));

// Tentukan tanggal yang dipilih: dari URL (format lengkap YYYY-MM-DD) atau tanggal pertama yang tersedia.
if ($selectedDateStr === '') {
    $firstKey = array_key_first($kalender);
    $selectedDateStr = $firstKey ? sprintf('%04d-%02d-%02d', $year, $month, (int) $firstKey) : '';
}
$selectedDate = $selectedDateStr ? (int) date('j', strtotime($selectedDateStr)) : 0;

$fullQuotaDate = [];
foreach ($kalender as $hari => $status) {
    if ($status === 'penuh') $fullQuotaDate[] = (int) $hari;
}
$jadwalTerpilih = null;
foreach ($jadwalBulan as $j) {
    if ($j['tanggal'] === $selectedDateStr) {
        $jadwalTerpilih = $j;
        break;
    }
}
$kuotaTersedia = $jadwalTerpilih ? (int) $jadwalTerpilih['sisa_kuota'] : 0;
$dokterNama = $jadwalTerpilih && $jadwalTerpilih['dokter']
    ? $jadwalTerpilih['dokter']
    : 'Dokter akan ditentukan';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | SIPOLI</title>
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <div class="w-full mx-auto min-h-screen bg-page flex flex-col shadow-xl">

        <?php require_once __DIR__ . "/../components/header.php" ?>

        <main class="px-5 md:px-20 py-6 overflow-y-auto">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 items-start">

                <!-- Profile doctor -->
                <div class="md:col-span-5 flex flex-col">
                    <section class="flex gap-4 justify-between items-start">
                        <div class="flex-1 pt-1">
                            <div class="flex items-center gap-2 text-primary font-medium text-sm mb-2">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Jadwal Pengobatan</span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight"><?= e($dokterNama) ?></h1>
                            <p class="mt-3 text-sm text-gray-500 leading-relaxed pr-2">
                                Dokter praktik di <?= e($jadwalTerpilih['poli'] ?? 'Poli') ?>. Jadwal praktik lihat kalender di bawah.
                            </p>
                        </div>

                        <div class="w-2/5 aspect-[3/4] bg-primary-100 rounded-xl overflow-hidden relative shrink-0">
                            <img src="./../../assets/img/doc-pic-reserv.png" alt="drg. Nadia Putri" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    </section>
                </div>

                <!-- Date -->
                <section class="md:col-span-7 bg-white rounded-3xl px-4 md:px-6 pt-5 pb-8 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="font-bold text-sm md:text-base text-gray-900">Pilih Tanggal</h2>
                        <div class="flex items-center gap-2">
                            <?php $navDateParam = $selectedDateStr ? '&tanggal=' . urlencode($selectedDateStr) : ''; ?>
                            <a href="?m=<?= $prevMonth ?>&y=<?= $prevYear ?>&dokter_id=<?= urlencode($doctorId) ?>&jadwal_id=<?= urlencode($scheduleId) ?><?= $navDateParam ?>"
                                aria-label="Bulan Sebelumnya"
                                class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center transition-colors">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                            <span class="bg-primary-50 text-primary-700 px-4 py-1.5 rounded text-xs font-semibold min-w-[115px] text-center">
                                <?= $monthName ?> <?= $year ?>
                            </span>
                            <a href="?m=<?= $nextMonth ?>&y=<?= $nextYear ?>&dokter_id=<?= urlencode($doctorId) ?>&jadwal_id=<?= urlencode($scheduleId) ?><?= $navDateParam ?>"
                                aria-label="Bulan Berikutnya"
                                class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center transition-colors">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Kalender -->
                    <div class="pt-2">
                        <!-- Grid Header Hari -->
                        <div class="grid grid-cols-7 mb-4 text-center text-sm font-medium text-gray-500">
                            <div>Min</div>
                            <div>Sen</div>
                            <div>Sel</div>
                            <div>Rab</div>
                            <div>Kam</div>
                            <div>Jum</div>
                            <div>Sab</div>
                        </div>

                        <!-- Grid Tanggal -->
                        <div class="grid grid-cols-7 gap-y-4 text-center">
                            <?php
                            for ($i = $firstDayOfMonth - 1; $i >= 0; $i--) {
                                $day = $prevMonthDays - $i;
                                echo "<div class='text-sm text-gray-300 font-medium py-2'>$day</div>";
                            }

                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $dayFormatted = sprintf("%02d", $day);
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $status = $kalender[$day] ?? null;
                                $isSelected = ($dateStr === $selectedDateStr);
                                $isFullQuota = in_array($day, $fullQuotaDate, true);

                                $wrapperClass = "flex flex-col items-center justify-center";
                                $textClass = "text-sm font-medium py-1.5 px-2 w-8 h-8 flex items-center justify-center transition-colors";
                                $dotIndicator = "";

                                if ($status === null) {
                                    $wrapperClass .= " cursor-not-allowed";
                                    $textClass .= " text-gray-300";
                                } else {
                                    if ($isSelected) {
                                        $textClass .= " bg-primary-600 text-white rounded shadow-md";
                                    } elseif ($isFullQuota) {
                                        $textClass .= " text-gray-400 bg-gray-100 rounded line-through";
                                    } else {
                                        $textClass .= " text-gray-900 hover:bg-gray-100 rounded cursor-pointer";
                                    }
                                }

                                if ($isFullQuota) {
                                    $dotIndicator = "<div class='w-1.5 h-1.5 bg-red-600 rounded-full mt-1'></div>";
                                }

                                echo "<div class='$wrapperClass'>";
                                if ($status !== null && !$isFullQuota) {
                                    echo "<button type='button' class='date-btn $textClass' data-date='$dateStr'>$dayFormatted</button>";
                                } else {
                                    echo "<span class='$textClass'>$dayFormatted</span>";
                                }
                                echo $dotIndicator;
                                echo "</div>";
                            }

                            $totalCells = $firstDayOfMonth + $daysInMonth;
                            $remainingCells = (7 - ($totalCells % 7)) % 7;

                            for ($day = 1; $day <= $remainingCells; $day++) {
                                $dayFormatted = sprintf("%02d", $day);
                                echo "<div class='flex flex-col items-center justify-center'><span class='text-sm text-gray-300 font-medium w-8 h-8 flex items-center justify-center'>$dayFormatted</span></div>";
                            }
                            ?>
                        </div>

                        <div class="flex items-center justify-center mt-8 mb-5">
                            <div class="flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-red-600 rounded-full"></div>
                                    <span class="text-xs font-medium text-gray-500">Kuota penuh</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-primary rounded-sm"></div>
                                    <span class="text-xs font-medium text-gray-500">Dipilih</span>
                                </div>
                            </div>
                        </div>

                        <form id="reservation-form" action="./form-reservasi.php" method="GET">
                            <input type="hidden" name="dokter_id" value="<?= htmlspecialchars($doctorId) ?>">
                            <input type="hidden" name="jadwal_id" value="<?= htmlspecialchars($scheduleId) ?>">
                            <input type="hidden" name="tanggal" id="input-tanggal" value="<?= htmlspecialchars($selectedDateStr) ?>" required>

                            <button type="submit" id="submit-reservasi-btn"
                                <?= $selectedDateStr ? '' : 'disabled' ?>
                                class="w-full py-2.5 block bg-primary hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-center font-semibold rounded-full shadow-lg transition-colors cursor-pointer">
                                Reservasi
                            </button>
                        </form>
                    </div>
                </section>

            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateButtons = document.querySelectorAll('.date-btn');
            const inputTanggal = document.getElementById('input-tanggal');
            const submitBtn = document.getElementById('submit-reservasi-btn');
            const reservationForm = document.getElementById('reservation-form');

            // Set tombol aktif jika sudah ada tanggal terpilih dari URL
            dateButtons.forEach(btn => {
                if (btn.dataset.date === inputTanggal.value) {
                    btn.classList.add('bg-primary', 'text-white', 'shadow-md');
                    btn.classList.remove('text-gray-900', 'hover:bg-gray-100');
                }
            });

            dateButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    dateButtons.forEach(item => {
                        item.classList.remove('bg-primary', 'text-white', 'shadow-md');
                        item.classList.add('text-gray-900', 'hover:bg-gray-100');
                    });

                    btn.classList.remove('text-gray-900', 'hover:bg-gray-100');
                    btn.classList.add('bg-primary', 'text-white', 'shadow-md');

                    const chosenDate = btn.dataset.date;
                    inputTanggal.value = chosenDate;
                    submitBtn.disabled = false;

                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('tanggal', chosenDate);
                    window.history.replaceState({}, '', currentUrl);
                });
            });

            reservationForm.addEventListener('submit', (e) => {
                if (!inputTanggal.value) {
                    e.preventDefault();
                    alert('Silakan pilih tanggal pemeriksaan terlebih dahulu.');
                }
            });
        });
    </script>
    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../../assets/js/flowbite-init.js"></script>
</body>

</html>