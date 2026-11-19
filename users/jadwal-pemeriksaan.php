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

$hariIni = $pdo->query('SELECT fn_today()::text')->fetchColumn() ?: date('Y-m-d');
// v_jadwal_tersedia hanya memuat H-1 s.d. H-7, jadi default-nya H+1 agar tidak kosong.
$tanggalPertama = $pdo->query('SELECT (fn_today() + 1)::text')->fetchColumn()
    ?: date('Y-m-d', strtotime($hariIni . ' +1 day'));
$tanggalDipilih = isset($_GET['tanggal']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_GET['tanggal'])
    ? (string) $_GET['tanggal']
    : $tanggalPertama;
$poliId = isset($_GET['poli_id']) && ctype_digit((string) $_GET['poli_id']) ? (int) $_GET['poli_id'] : 0;

$jadwal = QueryPasienJadwal::daftar($tanggalDipilih, $poliId);

$hariMap  = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
$bulanMap = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$labelBulan = $bulanMap[(int) date('n', strtotime($tanggalDipilih))] . ' ' . date('Y', strtotime($tanggalDipilih));
$labelTanggal = $hariMap[(int) date('N', strtotime($tanggalDipilih))] . ', ' . (int) date('j', strtotime($tanggalDipilih)) . ' ' . $bulanMap[(int) date('n', strtotime($tanggalDipilih))];

$daftarTanggal = [];
for ($i = 1; $i <= 5; $i++) {
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
            <section class="content w-full" id="jadwal-app"
                data-endpoint="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>"
                data-initial-date="<?= htmlspecialchars($default_date) ?>"
                data-initial-poli="<?= htmlspecialchars($default_poli) ?>">

                <div class="category">
                    <ul class="flex justify-between md:justify-start md:gap-3">
                        <li>
                            <input type="radio" id="semua" name="hosting" value="" onchange="location.href='?poli_id='" <?= $poliId === 0 ? 'checked' : '' ?> class="hidden peer" required />
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
                                        <i class="far fa-calendar-alt text-primary text-2xl"></i>
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
                                <?php else: ?>
                                    <a href="./reservasi/form-reservasi.php?jadwal_id=<?= (int) $j['id'] ?>" class="action-link text-page text-center rounded-full text-sm px-4 py-2.5 bg-primary-500 hover:bg-primary-700">
                                        <span class="font-medium">Reservasi</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <p id="selected-date-label" class="date text-primary-700 font-semibold mt-5 mb-3"></p>

                <div id="schedule-container" class="schedule grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const appEl = document.getElementById('jadwal-app');
            const endpoint = appEl.dataset.endpoint;
            const initialDateStr = appEl.dataset.initialDate;

            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const shortDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const fullDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            function parseISODate(isoStr) {
                const [y, m, d] = isoStr.split('-').map(Number);
                return new Date(y, m - 1, d);
            }

            function formatISODate(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            let selectedDate = parseISODate(initialDateStr);
            let windowStartDate = new Date(selectedDate);
            let selectedPoli = appEl.dataset.initialPoli || 'semua';

            const monthLabel = document.getElementById('current-month-label');
            const selectedDateLabel = document.getElementById('selected-date-label');
            const dateContainer = document.getElementById('date-filter-container');
            const scheduleContainer = document.getElementById('schedule-container');
            const prevMonthBtn = document.getElementById('prev-month-btn');
            const nextMonthBtn = document.getElementById('next-month-btn');
            const prevDaysBtn = document.getElementById('prev-days-btn');
            const nextDaysBtn = document.getElementById('next-days-btn');
            const poliRadios = document.querySelectorAll('input[name="poli_filter"]');

            function getVisibleDaysCount() {
                return window.innerWidth >= 768 ? 7 : 5;
            }

            function isSameDate(d1, d2) {
                return d1.getFullYear() === d2.getFullYear() &&
                    d1.getMonth() === d2.getMonth() &&
                    d1.getDate() === d2.getDate();
            }

            function formatSelectedDate(date) {
                return `${fullDays[date.getDay()]}, ${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
            }

            function syncURLState() {
                const url = new URL(window.location.href);
                url.searchParams.set('tanggal', formatISODate(selectedDate));
                url.searchParams.set('poli', selectedPoli);
                window.history.replaceState({}, '', url);
            }

            function renderScheduleCards(schedules) {
                if (!schedules || schedules.length === 0) {
                    scheduleContainer.innerHTML = `
                        <div class="col-span-full bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-500">
                            <i class="far fa-calendar-times text-3xl text-gray-400 mb-2"></i>
                            <p class="font-medium text-gray-700">Tidak ada jadwal praktik tersedia</p>
                            <p class="text-xs mt-1">Silakan pilih tanggal atau kategori poli lainnya.</p>
                        </div>
                    `;
                    return;
                }

                scheduleContainer.innerHTML = schedules.map(item => {
                    const isFull = Number(item.terisi) >= Number(item.kuota);
                    const badgeHtml = isFull
                        ? `<div class="status-badge shrink-0"><span class="bg-danger-soft text-fg-danger text-xs font-semibold px-2.5 py-1 rounded">Penuh ${item.terisi}/${item.kuota}</span></div>`
                        : `<div class="status-badge shrink-0"><span class="bg-primary-50 text-primary-700 text-xs font-semibold px-2.5 py-1 rounded">Kuota ${item.terisi}/${item.kuota}</span></div>`;

                    const actionHtml = isFull
                        ? `<span class="action-link text-gray-400 bg-neutral-secondary-medium border border-default-medium font-medium text-center rounded-full text-sm px-4 py-2.5 cursor-not-allowed select-none">Kuota Penuh</span>`
                        : `<a href="${item.url_booking}" class="action-link text-white text-center rounded-full text-sm px-4 py-2.5 bg-primary-500 hover:bg-primary-700 transition-colors font-medium">Reservasi</a>`;

                    return `
                        <div class="bg-white border border-gray-200 shadow-xs w-full p-5 rounded-xl flex flex-col justify-between gap-4">
                            <div class="flex gap-3.5 items-start">
                                <div class="icon w-12 h-12 shrink-0 rounded-full bg-primary-100 flex justify-center items-center">
                                    <i class="far fa-calendar-alt text-primary-600 text-xl"></i>
                                </div>
                                <div class="desc flex-1">
                                    <h6 class="font-bold text-gray-900">${item.nama_poli}</h6>
                                    <p class="doctor-name text-sm text-gray-600">${item.nama_dokter}</p>
                                    <p class="date text-xs text-gray-500 mt-1">${item.waktu_label}</p>
                                </div>
                                ${badgeHtml}
                            </div>
                            ${actionHtml}
                        </div>
                    `;
                }).join('');
            }

            async function fetchSchedules() {
                const tanggalISO = formatISODate(selectedDate);
                syncURLState();

                scheduleContainer.innerHTML = `
                    <div class="col-span-full py-10 text-center text-sm text-gray-500">
                        Memuat jadwal dokter...
                    </div>
                `;

                try {
                    const params = new URLSearchParams({
                        ajax: '1',
                        tanggal: tanggalISO,
                        poli: selectedPoli
                    });
                    const response = await fetch(`${endpoint}?${params.toString()}`);
                    const result = await response.json();

                    if (result.status === 'success') {
                        renderScheduleCards(result.data);
                    }
                } catch (error) {
                    scheduleContainer.innerHTML = `
                        <div class="col-span-full bg-white border border-red-200 rounded-xl p-6 text-center text-red-600 text-sm">
                            Gagal memuat data jadwal. Silakan coba lagi.
                        </div>
                    `;
                }
            }

            function renderDates() {
                const count = getVisibleDaysCount();
                dateContainer.innerHTML = '';

                monthLabel.textContent = `${monthNames[windowStartDate.getMonth()]} ${windowStartDate.getFullYear()}`;
                selectedDateLabel.textContent = formatSelectedDate(selectedDate);

                for (let i = 0; i < count; i++) {
                    const current = new Date(windowStartDate);
                    current.setDate(windowStartDate.getDate() + i);

                    const isSelected = isSameDate(current, selectedDate);
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.dataset.date = formatISODate(current);
                    btn.className = isSelected
                        ? 'bg-primary-500 text-white rounded-lg flex flex-col items-center justify-center p-2 md:py-2.5 text-sm shadow-xs cursor-pointer transition-colors'
                        : 'bg-white hover:bg-primary-50 text-gray-700 rounded-lg flex flex-col items-center justify-center p-2 md:py-2.5 text-sm border border-gray-200 transition-colors cursor-pointer';

                    btn.innerHTML = `
                        <span class="day text-xs md:text-sm">${shortDays[current.getDay()]}</span>
                        <span class="date font-bold text-sm md:text-base">${current.getDate()}</span>
                    `;

                    btn.addEventListener('click', () => {
                        selectedDate = new Date(current);
                        renderDates();
                        fetchSchedules();
                    });

                    dateContainer.appendChild(btn);
                }
            }

            poliRadios.forEach(radio => {
                radio.addEventListener('change', (e) => {
                    selectedPoli = e.target.value;
                    fetchSchedules();
                });
            });

            prevMonthBtn.addEventListener('click', () => {
                windowStartDate = new Date(windowStartDate.getFullYear(), windowStartDate.getMonth() - 1, 1);
                selectedDate = new Date(windowStartDate);
                renderDates();
                fetchSchedules();
            });

            nextMonthBtn.addEventListener('click', () => {
                windowStartDate = new Date(windowStartDate.getFullYear(), windowStartDate.getMonth() + 1, 1);
                selectedDate = new Date(windowStartDate);
                renderDates();
                fetchSchedules();
            });

            prevDaysBtn.addEventListener('click', () => {
                const step = getVisibleDaysCount();
                windowStartDate.setDate(windowStartDate.getDate() - step);
                renderDates();
            });

            nextDaysBtn.addEventListener('click', () => {
                const step = getVisibleDaysCount();
                windowStartDate.setDate(windowStartDate.getDate() + step);
                renderDates();
            });

            let lastIsDesktop = window.innerWidth >= 768;
            window.addEventListener('resize', () => {
                const isDesktop = window.innerWidth >= 768;
                if (isDesktop !== lastIsDesktop) {
                    lastIsDesktop = isDesktop;
                    renderDates();
                }
            });

            renderDates();
            fetchSchedules();
        });
    </script>
    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>
