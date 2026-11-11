<?php
$isEditPoliOpen = !empty($editPoli);
$plId     = (int) ($editPoli['id'] ?? 0);
$plNama   = $editPoli['nama'] ?? '';
$plButuh  = isset($editPoli['butuh_reservasi']) ? (bool) $editPoli['butuh_reservasi'] : true;
$plKuota  = (int) ($editPoli['kuota_harian'] ?? 10);
$plDurasi = (int) ($editPoli['durasi_menit'] ?? 30);
?>
<!-- Modal Tambah / Edit Data Poli -->
<div id="modal-form-poli" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isEditPoliOpen ? 'false' : 'true' ?>" class="<?= $isEditPoliOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    <?= $isEditPoliOpen ? 'Edit Data Poli' : 'Tambah Data Poli' ?>
                </h3>
                <?php if ($isEditPoliOpen): ?>
                    <a href="datamaster.php?tab=poli" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </a>
                <?php else: ?>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-form-poli">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </button>
                <?php endif; ?>
            </div>

            <form id="form-poli" action="datamaster.php" method="POST" class="p-5 space-y-4">
                <input type="hidden" name="action" value="save_poli">
                <input type="hidden" name="tab" value="poli">
                <input type="hidden" name="poli_id_PK" value="<?= $plId ?>">

                <div>
                    <label for="poli_nama" class="block mb-2 text-sm font-medium text-gray-600">Nama Poli *</label>
                    <input type="text" id="poli_nama" name="nama" value="<?= e($plNama) ?>" placeholder="Contoh: Poli Gigi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                </div>

                <div>
                    <label for="poli_sistem" class="block mb-2 text-sm font-medium text-gray-600">Sistem Layanan *</label>
                    <select id="poli_sistem" name="butuh_reservasi" onchange="toggleKuotaPoli()" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                        <option value="1" <?= $plButuh ? 'selected' : '' ?>>Wajib Reservasi (Kuota Terbatas)</option>
                        <option value="0" <?= !$plButuh ? 'selected' : '' ?>>Walk-in / Tanpa Reservasi (Tanpa Batas Kuota)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="wrap_kuota_poli" class="<?= $plButuh ? '' : 'opacity-40 pointer-events-none' ?>">
                        <label for="poli_kuota" class="block mb-2 text-sm font-medium text-gray-600">Kuota Harian Default</label>
                        <input type="number" min="1" id="poli_kuota" name="kuota_harian" value="<?= $plKuota > 0 ? $plKuota : 5 ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="poli_durasi" class="block mb-2 text-sm font-medium text-gray-600">Estimasi Durasi Sesi (Menit)</label>
                        <input type="number" min="5" id="poli_durasi" name="durasi_menit" value="<?= $plDurasi > 0 ? $plDurasi : 30 ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>
                </div>
            </form>

            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                <?php if ($isEditPoliOpen): ?>
                    <a href="datamaster.php?tab=poli" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="button" data-modal-hide="modal-form-poli" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                <?php endif; ?>
                <button type="submit" form="form-poli" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 rounded transition-colors cursor-pointer">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleKuotaPoli() {
    const sel = document.getElementById('poli_sistem');
    const wrap = document.getElementById('wrap_kuota_poli');
    if (!sel || !wrap) return;
    if (sel.value === '1') {
        wrap.classList.remove('opacity-40', 'pointer-events-none');
    } else {
        wrap.classList.add('opacity-40', 'pointer-events-none');
    }
}
</script>