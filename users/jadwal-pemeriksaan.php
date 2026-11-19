<?php
$page_title       = $page_title ?? 'Jadwal Dokter';
$page_description = "Cek ketersediaan jadwal praktik dokter dan pilih waktu kunjungan Anda.";
$back_url         = $back_url ?? './../index.php';
$breadcrumbs      = $breadcrumbs ?? [
    ['label' => $page_title]
];

$default_date = $_GET['tanggal'] ?? date('Y-m-d');
$default_poli = $_GET['poli'] ?? 'semua';

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
    $poli    = $_GET['poli'] ?? 'semua';

    // TODO: Ganti dummy array di bawah ini dengan query PostgreSQL (PDO) sesuai tanggal & poli
    $dummy_schedules = [
        [
            'id_jadwal'   => 101,
            'poli'        => 'konseling',
            'nama_poli'   => 'Konseling',
            'nama_dokter' => 'Dr. Sarah Amelia',
            'tanggal'     => $tanggal,
            'waktu_label' => '14.00 - 16.00 WIB',
            'terisi'      => 2,
            'kuota'       => 5,
            'url_booking' => './reservasi/jadwal-reservasi.php?id=101'
        ],
        [
            'id_jadwal'   => 102,
            'poli'        => 'umum',
            'nama_poli'   => 'Poli Umum',
            'nama_dokter' => 'Dr. Budi Santoso',
            'tanggal'     => $tanggal,
            'waktu_label' => '08.00 - 11.30 WIB',
            'terisi'      => 10,
            'kuota'       => 10,
            'url_booking' => '#'
        ]
    ];

    $filtered = array_values(array_filter($dummy_schedules, function ($item) use ($poli) {
        return $poli === 'semua' || strtolower($item['poli']) === strtolower($poli);
    }));

    echo json_encode([
        'status' => 'success',
        'data'   => $filtered
    ]);
    exit;
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
                    <ul class="flex items-center justify-between md:justify-start gap-2 md:gap-3 overflow-x-auto pb-1 no-scrollbar">
                        <?php
                        $daftar_poli = [
                            ['id' => 'poli-semua', 'value' => 'semua', 'label' => 'Semua'],
                            ['id' => 'poli-umum', 'value' => 'umum', 'label' => 'Umum'],
                            ['id' => 'poli-gigi', 'value' => 'gigi', 'label' => 'Gigi'],
                            ['id' => 'poli-konseling', 'value' => 'konseling', 'label' => 'Konseling'],
                        ];
                        foreach ($daftar_poli as $item):
                            $checked = ($default_poli === $item['value']) ? 'checked' : '';
                        ?>
                            <li class="shrink-0">
                                <input type="radio" id="<?= $item['id'] ?>" name="poli_filter" value="<?= $item['value'] ?>" class="hidden peer" <?= $checked ?> />
                                <label for="<?= $item['id'] ?>" class="inline-flex items-center justify-center text-sm py-2 px-4 md:py-2.5 md:px-6 rounded-full text-body bg-neutral-primary-soft border border-default cursor-pointer peer-checked:bg-primary-500 peer-checked:text-white peer-checked:border-primary-500 hover:bg-neutral-secondary-medium transition-colors">
                                    <span class="font-semibold"><?= $item['label'] ?></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 id="current-month-label" class="month text-xl font-bold text-gray-900"></h4>
                        <div class="flex items-center gap-2">
                            <button type="button" id="prev-month-btn" aria-label="Bulan Sebelumnya" class="w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-300 flex items-center justify-center transition-colors shadow-2xs cursor-pointer">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                            <button type="button" id="next-month-btn" aria-label="Bulan Berikutnya" class="w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 hover:border-primary-300 flex items-center justify-center transition-colors shadow-2xs cursor-pointer">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 md:gap-3">
                        <button type="button" id="prev-days-btn" aria-label="Geser Tanggal Sebelumnya" class="w-8 h-14 md:w-10 md:h-16 shrink-0 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center transition-colors cursor-pointer">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>

                        <div id="date-filter-container" class="date-filter flex-1 grid grid-cols-5 md:grid-cols-7 gap-2 md:gap-3"></div>

                        <button type="button" id="next-days-btn" aria-label="Geser Tanggal Berikutnya" class="w-8 h-14 md:w-10 md:h-16 shrink-0 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-primary-50 hover:text-primary-600 flex items-center justify-center transition-colors cursor-pointer">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </div>
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