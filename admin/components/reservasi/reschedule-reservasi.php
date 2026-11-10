<div id="modal-reschedule" tabindex="-1" aria-hidden="false" class="flex bg-gray-900/50 overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-2xl shadow p-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">Reschedule Reservasi</h3>
                <a href="reservasi.php?tanggal=<?= urlencode($filterTanggal) ?>" class="text-gray-400 hover:bg-gray-100 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">✕</a>
            </div>

            <form method="POST" action="reservasi.php" class="mt-4 space-y-4">
                <input type="hidden" name="action" value="reschedule">
                <input type="hidden" name="reservasi_id" value="<?= (int) $rescheduleData['id'] ?>">
                <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">

                <div class="p-4 bg-page rounded-base text-sm">
                    <p class="font-bold text-gray-900"><?= e($rescheduleData['nama_pasien']) ?> (<?= e($rescheduleData['no_reservasi']) ?>)</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Jadwal saat ini: <b><?= e($rescheduleData['nama_poli']) ?></b> &middot; <?= e($rescheduleData['tanggal']) ?> (<?= e(substr($rescheduleData['jam_mulai'], 0, 5)) ?>–<?= e(substr($rescheduleData['jam_selesai'], 0, 5)) ?>)
                    </p>
                </div>

                <div>
                    <label for="jadwal_baru_id" class="block mb-2 text-sm font-medium text-gray-900">Pilih Jadwal Baru (H+1 s.d. H+7 pada <?= e($rescheduleData['nama_poli']) ?>) *</label>
                    <?php if (empty($jadwalPengganti)): ?>
                        <div class="p-3 text-xs text-red-700 bg-red-50 rounded border border-red-200">
                            Tidak ada jadwal <?= e($rescheduleData['nama_poli']) ?> yang terbuka dan memiliki sisa kuota untuk H+1 s.d. H+7. Silakan buat atau publikasikan jadwal terlebih dahulu di menu Jadwal & Kuota.
                        </div>
                    <?php else: ?>
                        <select id="jadwal_baru_id" name="jadwal_baru_id" required class="block w-full p-2.5 text-sm text-gray-900 border border-primary rounded-base bg-white">
                            <?php foreach ($jadwalPengganti as $jp): ?>
                                <option value="<?= (int) $jp['id'] ?>">
                                    <?= e(formatTglSingkatRsv($jp['tanggal'])) ?> (<?= e(substr($jp['jam_mulai'], 0, 5)) ?>–<?= e(substr($jp['jam_selesai'], 0, 5)) ?>) · <?= e($jp['dokter'] ?? 'Dokter bertugas') ?> · Sisa kuota: <?= (int) $jp['sisa_kuota'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                    <a href="reservasi.php?tanggal=<?= urlencode($filterTanggal) ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</a>
                    <?php if (!empty($jadwalPengganti)): ?>
                        <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-700">Simpan Reschedule</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>