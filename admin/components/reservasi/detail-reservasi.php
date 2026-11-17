<?php
$isModalOpen = !empty($detailData);
$dtNama      = $detailData['nama_pasien'] ?? '-';
$dtNimNip    = $detailData['nim_nip'] ?? '-';
$dtLabelId   = (($detailData['status_civitas'] ?? '') === 'mahasiswa') ? 'NIM' : 'NIP';
$dtStatus    = $detailData['status'] ?? '-';
$dtPoli      = $detailData['nama_poli'] ?? '-';
$dtDokter    = $detailData['nama_dokter'] ?? 'Dokter bertugas';
$dtTanggal   = !empty($detailData['tanggal']) ? date('d F Y', strtotime($detailData['tanggal'])) : '-';
$dtJam       = (!empty($detailData['jam_mulai']) && !empty($detailData['jam_selesai']))
    ? str_replace(':', '.', substr($detailData['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($detailData['jam_selesai'], 0, 5)) . ' WIB'
    : 'Sesuai jam operasional Walk-in';
$dtAsal      = (($detailData['asal'] ?? '') === 'online') ? 'Online (Reservasi)' : 'Offline (Walk-in)';
$dtWaktuBuat = !empty($detailData['created_at']) ? date('d M Y, H:i', strtotime($detailData['created_at'])) . ' WIB' : 'Belum tercatat';
$dtKeluhan   = !empty($detailData['keluhan']) ? $detailData['keluhan'] : 'Belum tercatat pada data kunjungan.';
?>
<div id="modal-detail-reservasi" tabindex="-1" aria-hidden="<?= $isModalOpen ? 'false' : 'true' ?>" class="<?= $isModalOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow">
            <div class="flex items-center justify-between p-5 pb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    Detail reservasi & kunjungan
                </h3>
                <a href="reservasi.php?tanggal=<?= urlencode($filterTanggal ?? date('Y-m-d')) ?>" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($detailData): ?>
                <div class="p-5 pt-0 space-y-6">
                    <div class="flex justify-between items-start bg-[#F4F9F9] p-5 rounded-xl">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900"><?= e($dtNama) ?></h4>
                            <p class="text-sm text-gray-500 mt-1"><?= $dtLabelId ?> · <?= e($dtNimNip) ?> · <?= e($detailData['kode_tiket']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="bg-[#E1F2E8] text-[#147B44] text-xs font-semibold px-3 py-1 rounded-md capitalize"><?= e($dtStatus) ?></span>
                            <p class="text-xs text-gray-500 mt-2">
                                <?= !empty($detailData['no_antrean']) ? 'No. Antrean #' . (int) $detailData['no_antrean'] : 'Belum masuk antrean' ?>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Poli</p>
                            <p class="text-base font-semibold text-gray-900"><?= e($dtPoli) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Dokter / Tenaga Layanan</p>
                            <p class="text-base font-semibold text-gray-900"><?= e($dtDokter) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Tanggal kunjungan</p>
                            <p class="text-base font-semibold text-gray-900"><?= e($dtTanggal) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Jadwal kunjungan</p>
                            <p class="text-base font-semibold text-gray-900"><?= e($dtJam) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Asal pendaftaran</p>
                            <p class="text-base font-semibold text-gray-900"><?= e($dtAsal) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Waktu daftar</p>
                            <p class="text-base font-semibold text-gray-900"><?= e($dtWaktuBuat) ?></p>
                        </div>
                    </div>

                    <hr class="h-px bg-gray-100 border-0">

                    <div class="space-y-4">
                        <div>
                            <h5 class="text-sm font-bold text-gray-900 mb-1">Keluhan awal</h5>
                            <p class="text-sm text-gray-600"><?= e($dtKeluhan) ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Konfirmasi H-1</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    <?php if (!empty($detailData['butuh_reservasi'])): ?>
                                        <?= !empty($detailData['dikonfirmasi_at'])
                                            ? 'Dikonfirmasi pada ' . date('d M Y, H:i', strtotime($detailData['dikonfirmasi_at']))
                                            : 'Menunggu konfirmasi kehadiran' ?>
                                    <?php else: ?>
                                        Tidak berlaku · layanan Walk-in
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Status Reschedule / Batal</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    <?php if (!empty($detailData['alasan_batal'])): ?>
                                        Dibatalkan (Alasan: <?= e($detailData['alasan_batal']) ?>)
                                    <?php elseif ((int) ($detailData['reschedule_count'] ?? 0) > 0): ?>
                                        Pernah di-reschedule 1 kali
                                    <?php else: ?>
                                        Jadwal asli (Belum pernah reschedule)
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <a href="reservasi.php?tanggal=<?= urlencode($filterTanggal ?? date('Y-m-d')) ?>" class="text-white bg-[#157F8F] hover:bg-[#106573] focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-6 py-2.5 text-center">
                            Tutup
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>