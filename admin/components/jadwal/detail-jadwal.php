<?php
$isDetailOpen = !empty($detailJadwal);
$dNamaDokter  = $detailJadwal['nama_dokter'] ?? 'Belum ditetapkan';
$dProfesi     = !empty($detailJadwal['profesi']) ? ucfirst($detailJadwal['profesi']) : 'Menunggu penugasan';
$dNamaPoli    = $detailJadwal['nama_poli'] ?? '-';
$dTanggalFmt  = !empty($detailJadwal['tanggal']) ? formatTanggalIndo($detailJadwal['tanggal'], true) : '-';
$dJamMulai    = !empty($detailJadwal['jam_mulai']) ? str_replace(':', '.', substr($detailJadwal['jam_mulai'], 0, 5)) : '-';
$dJamSelesai  = !empty($detailJadwal['jam_selesai']) ? str_replace(':', '.', substr($detailJadwal['jam_selesai'], 0, 5)) : '-';

$dButuhRsv    = !empty($detailJadwal['butuh_reservasi']);
$dKuotaMax    = isset($detailJadwal['kuota']) && $detailJadwal['kuota'] !== null ? (int) $detailJadwal['kuota'] : null;
$dOnlineCount = (int) ($detailJadwal['terisi_online'] ?? 0);
$dOfflineCount = $dButuhRsv
    ? (int) ($detailJadwal['terisi_offline_jadwal'] ?? 0)
    : (int) ($detailJadwal['terisi_walkin_poli'] ?? 0);
$dTotalTerisi = $dButuhRsv ? $dOnlineCount : ($dOnlineCount + $dOfflineCount);
$dSisaKuota   = $dKuotaMax !== null ? max(0, $dKuotaMax - $dTotalTerisi) : null;
?>
<!-- Modal Detail Jadwal -->
<div id="modal-detail-jadwal" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isDetailOpen ? 'false' : 'true' ?>" class="<?= $isDetailOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100 shrink-0">
                <h3 class="text-xl font-bold text-gray-900">
                    Detail jadwal
                </h3>
                <a href="jadwal.php?tanggal=<?= urlencode($filterTanggal) ?>" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-5 overflow-y-auto">
                <?php if ($detailJadwal): ?>
                    <!-- Profil Dokter & Status -->
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-5">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900"><?= e($dNamaDokter) ?></h4>
                            <p class="text-sm text-gray-500 mt-1"><?= e($dProfesi) ?> &bull; <?= e($dNamaPoli) ?></p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <?php if ($detailJadwal['status'] === 'buka'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded">
                                    Tersedia
                                </span>
                            <?php elseif ($detailJadwal['status'] === 'penuh'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded">
                                    Penuh
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-gray-800 bg-gray-200 rounded">
                                    Tutup
                                </span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-500">
                                <?= $detailJadwal['dipublikasi'] ? 'Dipublikasikan' : 'Draf (Belum dipublikasi)' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Info Waktu -->
                    <div class="flex items-center px-4 py-3 bg-[var(--color-page)] rounded mb-5 border border-primary-50">
                        <svg class="w-4 h-4 text-primary-600 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1v3m5-3v3m5-3v3M1 7h18M5 11h10M2 3h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900 mr-4"><?= e($dTanggalFmt) ?></span>
                        <div class="w-px h-4 bg-gray-300 mr-4"></div>
                        <span class="text-sm font-medium text-gray-900"><?= e($dJamMulai) ?>&ndash;<?= e($dJamSelesai) ?> WIB</span>
                    </div>

                    <!-- Grid Kuota (3 Kolom) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Card 1: Kuota terisi -->
                        <div class="p-4 border border-gray-200 rounded bg-white">
                            <span class="block text-xs text-gray-500 mb-1">Kuota terisi</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-bold text-primary-600"><?= $dTotalTerisi ?></span>
                                <?php if ($dKuotaMax !== null): ?>
                                    <span class="text-xl font-bold text-gray-400">/<?= $dKuotaMax ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="block text-xs text-gray-500 mt-1">pasien terdaftar</span>
                        </div>

                        <!-- Card 2: Kuota tersisa -->
                        <div class="p-4 border border-gray-200 rounded bg-white">
                            <span class="block text-xs text-gray-500 mb-1">Kuota tersisa</span>
                            <span class="block text-3xl font-bold text-primary-600">
                                <?= $dSisaKuota !== null ? $dSisaKuota : '&infin;' ?>
                            </span>
                            <span class="block text-xs text-gray-500 mt-1">
                                <?= $dSisaKuota !== null ? 'masih dapat direservasi' : 'layanan walk-in tanpa batas' ?>
                            </span>
                        </div>

                        <!-- Card 3: Sumber pendaftaran -->
                        <div class="p-4 border border-gray-200 rounded bg-white">
                            <span class="block text-xs text-gray-500 mb-1">Sumber pendaftaran</span>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-base font-bold text-primary-700"><?= $dOnlineCount ?> online</span>
                                <span class="text-gray-400">&bull;</span>
                                <span class="text-base font-bold text-primary-700"><?= $dOfflineCount ?> offline</span>
                            </div>
                            <span class="block text-xs text-gray-500 mt-1.5">Total <?= $dTotalTerisi ?> pasien aktif</span>
                        </div>
                    </div>

                    <!-- Keterangan Dinamis Berdasarkan Tipe Poli -->
                    <div class="mb-6">
                        <h5 class="text-sm font-bold text-gray-900 mb-1">Keterangan Layanan</h5>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            <?php if ($dButuhRsv): ?>
                                Jadwal pelayanan <?= e($dNamaPoli) ?> menggunakan sistem reservasi (H-7 s.d. H-1) dengan kuota maksimal <?= (int) $dKuotaMax ?> pasien.
                                <?= $detailJadwal['durasi_menit'] ? 'Estimasi durasi pemeriksaan ' . (int) $detailJadwal['durasi_menit'] . ' menit per pasien.' : '' ?>
                            <?php else: ?>
                                Jadwal pelayanan <?= e($dNamaPoli) ?> melayani pendaftaran langsung (walk-in) tanpa batas kuota harian.
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Bagian Tabel Reservasi / Antrean -->
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-base font-bold text-gray-900">
                            <?= $dButuhRsv ? 'Daftar reservasi' : 'Daftar antrean walk-in' ?>
                        </h5>
                        <span class="text-xs text-gray-500">
                            <?= count($detailPasienList) ?> pasien
                            <?= $dKuotaMax !== null ? '&bull; ' . $dTotalTerisi . '/' . $dKuotaMax . ' kuota terisi' : '' ?>
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-500 bg-[var(--color-page)] border-b border-gray-100">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium">No.</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Pasien</th>
                                    <th scope="col" class="px-4 py-3 font-medium">ID Reservasi / Antrean</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Sumber</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detailPasienList)): ?>
                                    <tr class="bg-white">
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada pasien terdaftar pada jadwal ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($detailPasienList as $idx => $ps): ?>
                                        <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-400"><?= str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) ?></td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900"><?= e($ps['nama_pasien']) ?></div>
                                                <div class="text-xs text-gray-400"><?= e($ps['nim_nip']) ?></div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-500"><?= e($ps['kode_tiket']) ?></td>
                                            <td class="px-4 py-3 text-gray-500"><?= e($ps['sumber']) ?></td>
                                            <td class="px-4 py-3">
                                                <?php
                                                $st = $ps['status'];
                                                $badgeClass = match ($st) {
                                                    'dikonfirmasi', 'hadir', 'selesai' => 'text-emerald-700 bg-emerald-50',
                                                    'diperiksa'                        => 'text-blue-700 bg-blue-50',
                                                    'menunggu'                         => 'text-amber-700 bg-amber-50',
                                                    default                            => 'text-red-700 bg-red-50',
                                                };
                                                $labelSt = match ($st) {
                                                    'menunggu'     => 'Menunggu',
                                                    'dikonfirmasi' => 'Dikonfirmasi',
                                                    'hadir'        => 'Hadir',
                                                    'diperiksa'    => 'Sedang diperiksa',
                                                    'selesai'      => 'Selesai',
                                                    'tidak_hadir'  => 'Tidak hadir',
                                                    default        => ucfirst($st),
                                                };
                                                ?>
                                                <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold rounded <?= $badgeClass ?>">
                                                    <?= e($labelSt) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between p-5 border-t border-gray-100 shrink-0">
                <span class="text-xs text-gray-400">Perubahan jadwal dan kuota dikelola oleh Admin.</span>
                <a href="jadwal.php?tanggal=<?= urlencode($filterTanggal) ?>" class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                    Tutup
                </a>
            </div>

        </div>
    </div>
</div>