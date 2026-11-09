<?php
// Menangkap parameter bulan dan tahun dari URL, default disesuaikan dengan referensi gambar (September 2020)
$month = isset($_GET['m']) ? (int)$_GET['m'] : 9;
$year = isset($_GET['y']) ? (int)$_GET['y'] : 2020;
$dayNow = date('d');

// Kalkulasi untuk navigasi Prev dan Next Month
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

// Detail informasi bulan yang sedang aktif
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$prevMonthDays = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);
$firstDayOfMonth = date('w', strtotime("$year-$month-01")); // 0 (Minggu) s/d 6 (Sabtu)

// Array nama bulan bahasa Indonesia
$bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$monthName = $bulanIndo[$month - 1];

$page_title = "Jadwal Pengobatan";

// Simulasi data dari database untuk referensi UI
$selectedDate = ""; // Tanggal yang dipilih (kotak primary)
$fullQuotaDate = [1, 5, 14]; // Tanggal kuota penuh (titik merah)
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | SIPOLI</title>
    <!-- Asumsi style.css berisi layer base Tailwind v4 dan Flowbite -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama Mobile First -->
    <div class="w-full md:max-w-md mx-auto min-h-screen px-6 bg-page relative flex flex-col shadow-xl">

        <!-- Header Navigasi -->
        <header class="py-5">
            <a href="./../jadwal-pemeriksaan.php" class=" text-lg">
                <i class="fas fa-chevron-left text-primary"></i>
            </a>
        </header>

        <!-- Bagian Profil Dokter -->
        <section class="flex gap-4 justify-between items-start">
            <div class="flex-1 pt-1">
                <div class="flex items-center gap-2 text-primary font-medium text-sm mb-2">
                    <i class="far fa-calendar-alt"></i>
                    <span>Jadwal Pengobatan</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 leading-tight">drg. Nadia<br>Putri, Sp.KG</h1>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed pr-2">
                    Spesialis Konservasi Gigi yang berfokus pada perawatan saraf, penambalan estetika, dan kesehatan gigi menyeluruh.
                </p>
            </div>

            <!-- Skeleton Placeholder Gambar (Aspek Rasio 3:4) -->
            <div class="w-2/5 aspect-[3/4] bg-primary-100 rounded overflow-hidden relative">
                <!-- Tambahkan path gambar asli di atribut src di bawah -->
                <img src="" alt="drg. Nadia Putri" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center text-primary-300 text-xs text-center p-2 border border-dashed border-primary-300 rounded">
                    [Gambar Dokter]
                </div>
            </div>
        </section>

        <!-- Card Statistik -->
        <!-- Memakai 'rounded' sesuai aturan spesifikasi elemen dasar -->
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
                // 1. Render tanggal bulan sebelumnya (Teks pudar)
                for ($i = $firstDayOfMonth - 1; $i >= 0; $i--) {
                    $day = $prevMonthDays - $i;
                    echo "<div class='text-sm text-gray-300 font-medium py-2'>$day</div>";
                }

                // 2. Render tanggal bulan ini
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $dayFormatted = sprintf("%02d", $day);

                    // Logika Styling Status
                    $isPast = ($day >= 1 && $day <= $dayNow - 1); // Simulasi hari berlalu (teks pudar)
                    $isH7 = ($day >= $dayNow + 7);

                    $wrapperClass = "flex flex-col items-center justify-center";
                    $textClass = "text-sm font-medium py-1.5 px-2 w-8 h-8 flex items-center justify-center";
                    $dotIndicator = "";

                    if ($day == $selectedDate) {
                        // Tanggal dipilih (Kotak Primary + 'rounded' standar)
                        $wrapperClass .= " cursor-pointer";
                        $textClass .= " bg-primary-600 text-white rounded shadow-md";
                    } elseif ($isPast || $isH7) {
                        // Tanggal terlewat
                        $wrapperClass .= " cursor-not-allowed";
                        $textClass .= " text-gray-300";
                    } else {
                        // Tanggal tersedia normal
                        $wrapperClass .= " cursor-pointer";
                        $textClass .= " text-gray-900 hover:bg-gray-100 rounded";
                    }

                    // Logika Titik Merah (Kuota Penuh)
                    for ($i = 0; $i < count($fullQuotaDate); $i++) {
                        if ($day == $fullQuotaDate[$i]) {
                            $dotIndicator = "<div class='w-1.5 h-1.5 bg-red-600 rounded-full mt-1'></div>";
                            $textClass .= " text-gray-300"; // Disable interaksi visual
                        }
                    }

                    echo "
                    <div class='$wrapperClass'>
                        <span class='$textClass'>$dayFormatted</span>
                        $dotIndicator
                    </div>";
                }

                // 3. Render sisa kotak untuk bulan berikutnya
                $totalCells = $firstDayOfMonth + $daysInMonth;
                $remainingCells = 42 - $totalCells; // Selalu 6 baris agar tinggi UI konsisten
                if ($remainingCells < 7) $remainingCells += 7; // Minimal baris pelengkap

                for ($day = 1; $day <= $remainingCells; $day++) {
                    $dayFormatted = sprintf("%02d", $day);
                    echo "<div class='text-sm text-gray-300 font-medium py-2'>$dayFormatted</div>";
                }
                ?>
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
            <a href="./form-reservasi.php" class="w-full py-2 block bg-primary hover:bg-primary-700 text-white text-center font-semibold rounded-full shadow-lg transition-colors">
                Reservasi
            </a>
        </section>

    </div>

    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>