<?php
$isCatatOpen = !empty($modalReservasi);
?>
<!-- Modal Catat Respons Konfirmasi -->
<div id="modal-catat-respons" tabindex="-1" aria-hidden="<?= $isCatatOpen ? 'false' : 'true' ?>" class="<?= $isCatatOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">

            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">Catat Respons Konfirmasi</h3>
                <a href="konfirmasi.php" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center">✕</a>
            </div>

            <?php if ($modalReservasi): ?>
                <form action="konfirmasi.php" method="POST">
                    <input type="hidden" name="action" value="catat_respons">
                    <input type="hidden" name="reservasi_id" value="<?= (int) $modalReservasi['reservasi_id'] ?>">

                    <div class="p-5 space-y-5">
                        <div class="p-4 bg-[var(--color-page)] border border-gray-100 rounded flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div>
                                <h4 class="text-base font-bold text-gray-900"><?= e($modalReservasi['nama_pasien']) ?></h4>
                                <p class="text-sm text-gray-500 mt-0.5">NIM/NIP <?= e($modalReservasi['nim_nip']) ?> &bull; Tiket: <?= e($modalReservasi['no_reservasi']) ?></p>
                            </div>
                            <div class="md:text-right">
                                <h4 class="text-sm font-bold text-primary-700"><?= e($modalReservasi['nama_poli']) ?></h4>
                                <p class="text-sm text-gray-500 mt-0.5"><?= e(date('d M Y', strtotime($modalReservasi['tanggal']))) ?> &bull; <?= e(substr($modalReservasi['jam_mulai'], 0, 5)) ?> WIB</p>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-bold text-gray-900">Hasil Konfirmasi Pasien *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="flex items-center px-4 py-3 border border-primary-500 bg-primary-50 rounded cursor-pointer">
                                    <input type="radio" name="hasil_konfirmasi" value="hadir" checked class="w-4 h-4 text-primary-600">
                                    <span class="ml-2.5 text-sm font-bold text-primary-700">Konfirmasi Hadir</span>
                                </label>
                                <label class="flex items-center px-4 py-3 border border-gray-300 bg-white rounded cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="hasil_konfirmasi" value="tidak_hadir" class="w-4 h-4 text-primary-600">
                                    <span class="ml-2.5 text-sm font-medium text-gray-600">Tidak Bisa Hadir (Batalkan)</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="catatan_respons" class="block mb-2 text-sm font-bold text-gray-900">Catatan Respons (Opsional)</label>
                            <textarea id="catatan_respons" name="catatan_respons" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 block w-full p-2.5" placeholder="Contoh: Pasien mengonfirmasi bersedia hadir esok hari tepat waktu..."></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                        <a href="konfirmasi.php" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Batal</a>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded">Simpan Respons</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>