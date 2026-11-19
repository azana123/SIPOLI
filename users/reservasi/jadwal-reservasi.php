<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/_load.php';

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

$doctorId   = $_GET['dokter_id'] ?? '1';
$scheduleId = $_GET['jadwal_id'] ?? '101';

// Simulasi data kuota penuh dari database (format YYYY-MM-DD)
// Nantinya diganti dengan query PostgreSQL berdasarkan dokter_id/jadwal_id pada bulan & tahun terpilih
$fullQuotaDates = [
    $today->format('Y-m-d'),
    (clone $today)->modify('+3 days')->format('Y-m-d'),
];

$prevMonth = $month === 1 ? 12 : $month - 1;
$prevYear  = $month === 1 ? $year - 1 : $year;
$nextMonth = $month === 12 ? 1 : $month + 1;
$nextYear  = $month === 12 ? $year + 1 : $year;

$daysInMonth     = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$prevMonthDays   = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);
$firstDayOfMonth = (int)date('w', strtotime(sprintf('%04d-%02d-01', $year, $month)));

$bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$monthName = $bulanIndo[$month - 1];

$selectedDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$kalender = QueryPasienJadwal::kalender($month, $year);
$jadwalBulan = QueryPasienJadwal::daftar(sprintf('%04d-%02d-01', $year, $month));

// Tanggal yang dipilih: dari URL atau tanggal pertama yang tersedia.
$selectedDate = isset($_GET['tanggal']) && ctype_digit((string) $_GET['tanggal'])
    ? (int) $_GET['tanggal']
    : (int) (array_key_first($kalender) ?: 0);
$fullQuotaDate = [];
foreach ($kalender as $hari => $status) {
    if ($status === 'penuh') $fullQuotaDate[] = $hari;
}
$jadwalTerpilih = null;
foreach ($jadwalBulan as $j) {
    if ((int) date('j', strtotime($j['tanggal'])) === $selectedDate) {
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

        <main class="flex-1 px-5 py-6 overflow-y-auto">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 items-start">

                <div class="md:col-span-5 flex flex-col">
                    <section class="flex gap-4 justify-between items-start">
                        <div class="flex-1 pt-1">
                            <div class="flex items-center gap-2 text-primary font-medium text-sm mb-2">
                                <i class="far fa-calendar-alt"></i>
                                <span>Jadwal Pengobatan</span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight">drg. Nadia<br>Putri, Sp.KG</h1>
                            <p class="mt-3 text-sm text-gray-500 leading-relaxed pr-2">
                                Spesialis Konservasi Gigi yang berfokus pada perawatan saraf, penambalan estetika, dan kesehatan gigi menyeluruh.
                            </p>
                        </div>

                        <div class="w-2/5 aspect-[3/4] bg-primary-100 rounded overflow-hidden relative shrink-0">
                            <img src="./../../assets/img/doc-pic-reserv.png" alt="drg. Nadia Putri" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    </section>

                    <section class="mt-6 bg-primary rounded-xl p-4 grid grid-cols-3 gap-2.5 items-center text-white shadow-md">
                        <div class="flex items-center gap-2.5 border-primary-500">
                            <i class="fas fa-history text-xl"></i>
                            <span class="text-xs font-medium">3 Kuota</span>
                        </div>
                        <div class="flex items-center gap-2.5 border-primary-500">
                            <i class="far fa-user-circle text-xl"></i>
                            <span class="text-xs font-medium leading-tight">200+<br>Total Pasien</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-briefcase-medical text-xl"></i>
                            <span class="text-xs font-medium leading-tight">5 Tahun<br>Pengalaman</span>
                        </div>
                    </section>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 leading-tight"><?= e($dokterNama) ?></h1>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed pr-2">
                    Spesialis Konservasi Gigi yang berfokus pada perawatan saraf, penambalan estetika, dan kesehatan gigi menyeluruh.
                </p>
            </div>

            <section class="md:col-span-7 bg-white rounded-3xl mt-2 md:mt-0 mb-8 px-4 md:px-6 pt-5 pb-8 shadow-[0_-4px_15px_rgba(0,0,0,0.02)] border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-bold text-sm md:text-base text-gray-900">Pilih Tanggal</h2>
                    <div class="flex items-center gap-2">
                        <a href="?m=<?= $prevMonth ?>&y=<?= $prevYear ?>&dokter_id=<?= urlencode($doctorId) ?>&jadwal_id=<?= urlencode($scheduleId) ?><?= $selectedDate ? '&tanggal=' . urlencode($selectedDate) : '' ?>"
                            aria-label="Bulan Sebelumnya"
                            class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                        <span class="bg-primary-50 text-primary-700 px-4 py-1.5 rounded text-xs font-semibold min-w-[115px] text-center">
                            <?= $monthName ?> <?= $year ?>
                        </span>
                        <a href="?m=<?= $nextMonth ?>&y=<?= $nextYear ?>&dokter_id=<?= urlencode($doctorId) ?>&jadwal_id=<?= urlencode($scheduleId) ?><?= $selectedDate ? '&tanggal=' . urlencode($selectedDate) : '' ?>"
                            aria-label="Bulan Berikutnya"
                            class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Card Statistik -->
                <!-- Memakai 'rounded' sesuai aturan spesifikasi elemen dasar -->
                <section class="mt-6 bg-primary rounded-xl p-4 grid grid-cols-3 gap-2.5 items-center text-white shadow-md">
                    <div class="flex items-center gap-2.5 border-primary-500">
                        <i class="fas fa-history text-xl"></i>
                        <span class="text-xs font-medium"><?= $kuotaTersedia ?> Kuota</span>
                    </div>
                    <div class="flex items-center gap-2.5 border-primary-500">
                        <i class="far fa-user-circle text-xl"></i>
                        <span class="text-xs font-medium leading-tight">200+<br>Total Pasien</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-briefcase-medical text-xl"></i>
                        <span class="text-xs font-medium leading-tight">5 Tahun<br>Pengalaman</span>
                    </div>
                </section>

                <!-- Bottom Sheet Kalender -->
                <section class="bg-white rounded-3xl my-8 flex-1 px-4 pt-4 pb-8 shadow-[0_-4px_15px_rgba(0,0,0,0.02)]">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="font-bold text-sm text-gray-900">Pilih Tanggal</h2>
                        <span class="bg-primary-50 text-primary-700 px-4 py-1.5 rounded text-xs font-semibold">
                            <?= $monthName ?>
                        </span>
                    </div>

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
                            $status = $kalender[$day] ?? null;

                            $wrapperClass = "flex flex-col items-center justify-center";
                            $textClass = "text-sm font-medium py-1.5 px-2 w-8 h-8 flex items-center justify-center";
                            $dotIndicator = "";

                            if ($day == $selectedDate) {
                                $wrapperClass .= " cursor-pointer";
                                $textClass .= " bg-primary-600 text-white rounded shadow-md";
                            } elseif ($status === null) {
                                $wrapperClass .= " cursor-not-allowed";
                                $textClass .= " text-gray-300";
                            } else {
                                $wrapperClass .= " cursor-pointer";
                                $textClass .= " text-gray-900 hover:bg-gray-100 rounded";
                            }

                            if (in_array($day, $fullQuotaDate, true)) {
                                $dotIndicator = "<div class='w-1.5 h-1.5 bg-red-600 rounded-full mt-1'></div>";
                            }

                            echo "<div class='$wrapperClass'>";
                            if ($status !== null) {
                                echo "<a href='?m=$month&y=$year&tanggal=$day' class='no-underline'>";
                            }
                            echo "<span class='$textClass'>$dayFormatted</span>";
                            if ($status !== null) echo "</a>";
                            echo $dotIndicator;
                            echo "</div>";
                        }

                        $isWithin7Days = ($currentDateObj >= $today && $currentDateObj <= $maxActiveDate);
                        $isFullQuota   = in_array($currentDateStr, $fullQuotaDates, true);
                        $isSelectable  = $isWithin7Days && !$isFullQuota;
                        $isSelected    = ($currentDateStr === $selectedDate && $isSelectable);

                        $dotIndicator = $isFullQuota ? "<div class='w-1.5 h-1.5 bg-red-600 rounded-full mt-1'></div>" : "";

                        if ($isSelectable) {
                            $btnStateClass = $isSelected
                                ? "bg-primary text-white shadow-md"
                                : "text-gray-900 hover:bg-gray-100";

                            echo "
                                <div class='flex flex-col items-center justify-center'>
                                    <button type='button'
                                        data-date='$currentDateStr'
                                        class='date-btn text-sm font-medium w-8 h-8 rounded flex items-center justify-center cursor-pointer transition-colors $btnStateClass'>
                                        $dayFormatted
                                    </button>
                                </div>";
                        } else {
                            echo "
                                <div class='flex flex-col items-center justify-center cursor-not-allowed'>
                                    <span class='text-sm font-medium w-8 h-8 flex items-center justify-center text-gray-300'>$dayFormatted</span>
                                    $dotIndicator
                                </div>";
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
                        <!-- <span id="selected-date-info" class="flex-1 text-xs font-medium text-primary-700">
                            <?= $selectedDate ? "Tanggal: " . htmlspecialchars(date("j F Y", strtotime($selectedDate))) : "Belum memilih tanggal" ?>
                        </span> -->
                    </div>

                    <form id="reservation-form" action="./form-reservasi.php" method="GET">
                        <input type="hidden" name="dokter_id" value="<?= htmlspecialchars($doctorId) ?>">
                        <input type="hidden" name="jadwal_id" value="<?= htmlspecialchars($scheduleId) ?>">
                        <input type="hidden" name="tanggal" id="input-tanggal" value="<?= htmlspecialchars($selectedDate) ?>" required>

                        <button type="submit" id="submit-reservasi-btn"
                            <?= $selectedDate ? '' : 'disabled' ?>
                            class="w-full py-2.5 block bg-primary hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-center font-semibold rounded-full shadow-lg transition-colors cursor-pointer">
                            Reservasi
                        </button>
                    </form>
                </section>

    </div>

    <!-- Legenda -->
    <div class="flex items-center gap-6 mt-8 mb-4">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-red-600 rounded-full"></div>
            <span class="text-xs font-medium text-gray-500">Kouta penuh</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-primary-600 rounded-sm"></div>
            <span class="text-xs font-medium text-gray-500">Dipilih</span>
        </div>
    </div>
    <?php if ($jadwalTerpilih && !$jadwalTerpilih['penuh']): ?>
        <a href="./form-reservasi.php?jadwal_id=<?= (int) $jadwalTerpilih['id'] ?>" class="w-full py-2 block bg-primary hover:bg-primary-700 text-white text-center font-semibold rounded-full shadow-lg transition-colors">
            Reservasi
        </a>
    <?php else: ?>
        <span class="w-full py-2 block bg-gray-200 text-gray-500 text-center font-semibold rounded-full">
            Pilih tanggal yang tersedia
        </span>
    <?php endif; ?>
    </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateButtons = document.querySelectorAll('.date-btn');
            const inputTanggal = document.getElementById('input-tanggal');
            const submitBtn = document.getElementById('submit-reservasi-btn');
            const selectedDateInfo = document.getElementById('selected-date-info');
            const reservationForm = document.getElementById('reservation-form');

            dateButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    dateButtons.forEach(item => {
                        item.classList.remove('bg-primary', 'text-white', 'shadow-md');
                        item.classList.add('text-gray-900', 'hover:bg-gray-100');
                    });

                    btn.classList.remove('text-gray-900', 'hover:bg-gray-100');
                    btn.classList.add('bg-primary', 'text-white', 'shadow-md');

                    const ObjchosenDate = btn.dataset.date;
                    inputTanggal.value = ObjchosenDate;
                    // const dateObj = new Date(ObjchosenDate.split('-'));

                    // Format it to "12 November 2026"
                    // 'en-GB' ensures the day comes before the month
                    // const chosenDate = dateObj.toLocaleDateString('en-GB', {
                    //     day: 'numeric',
                    //     month: 'long',
                    //     year: 'numeric'
                    // });
                    // selectedDateInfo.textContent = `Tanggal: ${chosenDate}`;
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
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>