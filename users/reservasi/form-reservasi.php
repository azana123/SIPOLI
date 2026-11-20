<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/check-role.php';
check_role('pasien');

$page_title       = $page_title ?? "Form Reservasi";
$page_description = "Lengkapi data diri dan keluhan Anda untuk menyelesaikan proses reservasi pemeriksaan.";
$back_url         = $back_url ?? './jadwal-reservasi.php';
$breadcrumbs      = $breadcrumbs ?? [
    ['label' => 'Jadwal Pengobatan', 'url' => './jadwal-reservasi.php'],
    ['label' => $page_title]
];

$jadwalId = isset($_GET['jadwal_id']) && ctype_digit((string) $_GET['jadwal_id']) ? (int) $_GET['jadwal_id'] : 0;

if ($jadwalId <= 0) {
    try {
        $jadwalId = (int) ($pdo->query('SELECT id FROM v_jadwal_tersedia ORDER BY tanggal, jam_mulai LIMIT 1')->fetchColumn() ?: 0);
    } catch (PDOException $e) {
        $jadwalId = 0;
    }
}

// Ambil detail jadwal untuk ditampilkan di halaman form
$doctorName = 'Dokter akan ditentukan';
$doctorSpecialist = '-';
$selectedDate = '-';
$jadwalJam = '-';
if ($jadwalId > 0) {
    try {
        $stmtJadwal = $pdo->prepare("
            SELECT j.tanggal::text AS tanggal, j.jam_mulai::text AS jam_mulai, j.jam_selesai::text AS jam_selesai,
                   po.nama AS poli, COALESCE(d.nama, 'Dokter akan ditentukan') AS dokter
            FROM jadwal j
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE j.id = :id
            LIMIT 1
        ");
        $stmtJadwal->execute([':id' => $jadwalId]);
        $j = $stmtJadwal->fetch();
        if ($j) {
            $doctorName = $j['dokter'];
            $doctorSpecialist = $j['poli'];
            $selectedDate = FormatPasien::tanggalPanjang($j['tanggal']);
            $jadwalJam = FormatPasien::jam($j['jam_mulai']) . ' – ' . FormatPasien::jam($j['jam_selesai']);
        }
    } catch (PDOException $e) {
        // Biarkan nilai default
    }
}
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
    <!-- Kontainer Utama: Diperlebar untuk desktop agar header baru terlihat proporsional -->
    <div class="w-full mx-auto min-h-screen bg-page flex flex-col shadow-xl">

        <!-- Komponen Header Desktop & Mobile -->
        <?php require_once __DIR__ . "/../components/header.php" ?>

        <!-- Main Content -->
        <main class="flex-1 px-5 md:px-20 py-6 overflow-y-auto">
            <!-- Layout 1 kolom di mobile, 2 kolom proporsional di desktop -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6 items-start">

                <!-- Kolom Kiri (Desktop): Info Dokter & Tanggal -->
                <div class="md:col-span-4 flex flex-col gap-4">
                    <!-- Card 1: Profil Dokter -->
                    <section class="bg-primary-100 rounded-xl p-4 flex items-center gap-4 shadow-sm border border-primary-50">
                        <div class="w-14 h-14 rounded-full bg-primary-100 shrink-0 flex justify-center items-center">
                            <i class="fas fa-user-md text-primary text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-900"><?= e($doctorName) ?></h2>
                            <p class="text-xs text-gray-500 mt-0.5"><?= e($doctorSpecialist) ?></p>
                        </div>
                    </section>

                    <!-- Card 2: Tanggal & Jam Dipilih -->
                    <section class="bg-primary-100 rounded-xl p-4 flex items-center gap-4 shadow-sm border border-primary-50">
                        <div class="w-10 h-10 rounded text-primary text-xl flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal</p>
                            <p class="text-sm font-bold text-gray-900"><?= e($selectedDate) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5"><?= e($jadwalJam) ?></p>
                        </div>
                    </section>

                    <!-- Card 3: Form Keluhan -->
                    <section class="bg-primary-100 rounded-xl p-5 flex-1 shadow-sm mb-6 border border-primary-50">
                        <div class="flex items-center gap-3 mb-6 text-primary">
                            <i class="fas fa-user text-lg"></i>
                            <h3 class="font-bold text-base">Data Pasien</h3>
                        </div>

                        <p class="text-sm text-gray-500 mb-4">
                            Data akan dikirim menggunakan akun yang sedang login. Isi hanya keluhan Anda di bawah ini.
                        </p>

                        <!-- Form Reservasi via API -->
                        <form data-api-endpoint="/api/pasien/reservasi.php"
                              data-api-redirect="/users/reservasi/status-reservasi.php?id={id}"
                              class="space-y-5">
                            <input type="hidden" name="jadwal_id" value="<?= $jadwalId ?>">
                            <input type="hidden" name="action" value="create">

                            <!-- Input Keluhan -->
                            <div>
                                <label for="keluhan" class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-500">
                                    <i class="fas fa-comment-medical text-primary w-4 text-center"></i> Keluhan
                                </label>
                                <textarea id="keluhan" name="keluhan" rows="3"
                                          class="bg-page border-0 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 shadow-inner resize-none"
                                          placeholder="Deskripsikan keluhan Anda" required></textarea>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                        class="w-full py-2.5 bg-primary hover:bg-primary-700 text-white text-center font-semibold rounded-full shadow-lg transition-colors focus:ring-4 focus:ring-primary-300 cursor-pointer">
                                    Kirim Reservasi
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
        </main>
    </div>

    <?php require __DIR__ . '/../components/api-script.php'; ?>
    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="./../../assets/js/flowbite-init.js"></script>
</body>

</html>