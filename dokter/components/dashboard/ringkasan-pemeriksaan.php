<?php
$ringkasanAntreanId     = isset($_GET['ringkasan_antrean_id']) && ctype_digit((string) $_GET['ringkasan_antrean_id']) ? (int)$_GET['ringkasan_antrean_id'] : null;
$ringkasanPemeriksaanId = isset($_GET['ringkasan_pemeriksaan_id']) && ctype_digit((string) $_GET['ringkasan_pemeriksaan_id']) ? (int)$_GET['ringkasan_pemeriksaan_id'] : null;

$dataRingkasan      = null;
$resepRingkasanList = [];
$redirectBackRingkasan = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

$paramId = $ringkasanPemeriksaanId ?? $ringkasanAntreanId;

if (isset($pdo) && $paramId !== null) {
    try {
        $whereRingkasan = $ringkasanPemeriksaanId !== null ? "pm.id = :id" : "pm.antrean_id = :id";

        $stmtRgk = $pdo->prepare("
            SELECT 
                pm.*,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                d.nama AS nama_dokter,
                pol.nama AS nama_poli,
                po.id AS permintaan_obat_id,
                po.status AS status_resep
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            JOIN poli pol  ON pol.id = pm.poli_id
            LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
            WHERE {$whereRingkasan}
            LIMIT 1
        ");
        $stmtRgk->execute([':id' => $paramId]);
        $dataRingkasan = $stmtRgk->fetch();

        if ($dataRingkasan) {
            $stmtObatRgk = $pdo->prepare("
                SELECT ro.jumlah, ro.aturan_pakai, o.nama_obat, o.satuan, o.bentuk
                FROM resep_obat ro
                JOIN obat o ON o.id = ro.obat_id
                WHERE ro.pemeriksaan_id = :pem_id
                ORDER BY ro.id ASC
            ");
            $stmtObatRgk->execute([':pem_id' => (int) $dataRingkasan['id']]);
            $resepRingkasanList = $stmtObatRgk->fetchAll();
        }
    } catch (PDOException $e) {
        $dataRingkasan = null;
    }
}

$isRingkasanOpen = !empty($dataRingkasan);
$hariRgkMap  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];$bulanRgkMap = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>
<!-- Modal Ringkasan Pemeriksaan Pasien -->
<div id="modal-ringkasan-pemeriksaan" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isRingkasanOpen ? 'false' : 'true' ?>" class="<?= $isRingkasanOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 shrink-0 pb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">
                        Ringkasan pemeriksaan pasien
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Detail rekam medis dan status resep pada sesi pemeriksaan.
                    </p>
                </div>
                <a href="<?= htmlspecialchars($redirectBackRingkasan, ENT_QUOTES, 'UTF-8') ?>" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($dataRingkasan): ?>
                <?php
                $tsRgk       = strtotime($dataRingkasan['created_at']);
                $waktuFmtRgk =$hariRgkMap[(int) date('N', $tsRgk)] . ', ' . date('d', $tsRgk) . ' ' . $bulanRgkMap[(int) date('n',$tsRgk)] . ' ' . date('Y', $tsRgk) . ' • ' . date('H.i', $tsRgk) . ' WIB';
                $labelIdRgk  = ($dataRingkasan['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                $statusResepLabel = match ($dataRingkasan['status_resep'] ?? null) {
                    'menunggu'   => 'Menunggu Diproses Admin',
                    'disiapkan'  => 'Sedang Disiapkan Admin',
                    'diserahkan' => 'Obat Telah Diserahkan',
                    'dibatalkan' => 'Resep Dibatalkan',
                    default      => 'Tanpa Resep Obat',
                };
                ?>
                <!-- Modal Body (Scrollable Area) -->
                <div class="px-6 pb-6 overflow-y-auto space-y-6">

                    <!-- Box Profil & Waktu -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between p-4 bg-gray-50 border border-gray-200 rounded gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <h4 class="text-base font-bold text-gray-900"><?= e($dataRingkasan['nama_pasien']) ?></h4>
                                <span class="text-sm text-gray-500">&bull; <?= $labelIdRgk ?> <?= e($dataRingkasan['nim_nip']) ?> &bull; <span class="capitalize"><?= e($dataRingkasan['status_civitas']) ?></span></span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">
                                Waktu periksa: <span class="font-bold text-gray-900"><?= e($waktuFmtRgk) ?></span>
                            </p>
                            <p class="text-sm text-gray-600">
                                Dokter pemeriksa: <span class="font-bold text-gray-900"><?= e($dataRingkasan['nama_dokter']) ?></span> (<?= e($dataRingkasan['nama_poli']) ?>)
                            </p>
                        </div>
                        <div class="shrink-0">
                            <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                        </div>
                    </div>

                    <!-- Section: Catatan Tanda Vital -->
                    <div>
                        <h5 class="text-sm font-bold text-gray-900 mb-3">Catatan tanda vital</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                <span class="block text-xs text-gray-500 mb-1">Tekanan darah</span>
                                <span class="block text-base font-bold text-gray-900"><?= !empty($dataRingkasan['tekanan_darah']) ? e($dataRingkasan['tekanan_darah']) . ' mmHg' : '-' ?></span>
                            </div>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                <span class="block text-xs text-gray-500 mb-1">Suhu tubuh</span>
                                <span class="block text-base font-bold text-gray-900"><?= $dataRingkasan['suhu'] !== null ? e($dataRingkasan['suhu']) . ' °C' : '-' ?></span>
                            </div>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                <span class="block text-xs text-gray-500 mb-1">Berat badan</span>
                                <span class="block text-base font-bold text-gray-900"><?= $dataRingkasan['berat_badan'] !== null ? e($dataRingkasan['berat_badan']) . ' kg' : '-' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Hasil Pemeriksaan & Diagnosis -->
                    <div>
                        <h5 class="text-sm font-bold text-gray-900 mb-3">Hasil pemeriksaan &amp; diagnosis</h5>
                        <div class="border border-gray-200 rounded overflow-hidden">
                            <div class="flex flex-col text-sm">
                                <div class="flex flex-col md:flex-row p-4 border-b border-gray-100">
                                    <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Keluhan awal</div>
                                    <div class="md:w-2/3 font-bold text-gray-900"><?= e($dataRingkasan['keluhan'] ?: '-') ?></div>
                                </div>
                                <div class="flex flex-col md:flex-row p-4 border-b border-gray-100">
                                    <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Anamnesis &amp; pemeriksaan fisik</div>
                                    <div class="md:w-2/3 text-gray-700"><?= e($dataRingkasan['catatan'] ?: '-') ?></div>
                                </div>
                                <div class="flex flex-col md:flex-row p-4 border-b border-gray-100">
                                    <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Diagnosis medis</div>
                                    <div class="md:w-2/3 font-bold text-primary-600"><?= e($dataRingkasan['diagnosa'] ?: '-') ?></div>
                                </div>
                                <div class="flex flex-col md:flex-row p-4">
                                    <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Tindakan / saran medis</div>
                                    <div class="md:w-2/3 text-gray-700 leading-relaxed"><?= e($dataRingkasan['tindak_lanjut'] ?: ($dataRingkasan['tindakan'] ?: 'Istirahat cukup dan konsumsi obat sesuai anjuran.')) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Resep Obat Diberikan -->
                    <div class="p-5 bg-page border border-primary-100 rounded">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-base font-bold text-primary-600">Resep obat yang diberikan</h5>
                            <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">
                                <?= e($statusResepLabel) ?>
                            </span>
                        </div>

                        <?php if (empty($resepRingkasanList)): ?>
                            <p class="text-sm text-gray-500">Tidak ada resep obat pada pemeriksaan ini.</p>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($resepRingkasanList as $idxObat =>$itemObat): ?>
                                    <div class="flex flex-col md:flex-row md:items-center justify-between p-3.5 bg-white border border-gray-100 rounded shadow-sm gap-2">
                                        <span class="text-sm font-bold text-gray-900">
                                            <?= ($idxObat + 1) ?>. <?= e($itemObat['nama_obat']) ?> (<?= (int) $itemObat['jumlah'] ?> <?= e($itemObat['satuan']) ?>)
                                        </span>
                                        <span class="text-sm text-gray-600"><?= e($itemObat['aturan_pakai'] ?: 'Sesuai anjuran dokter') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endif; ?>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end p-6 border-t border-gray-100 shrink-0">
                <a href="<?= htmlspecialchars($redirectBackRingkasan, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors cursor-pointer">
                    Tutup
                </a>
            </div>

        </div>
    </div>
</div>