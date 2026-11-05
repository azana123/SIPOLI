<!-- Modal Tambah Jadwal Praktik -->
<div id="modal-tambah-jadwal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">
                    Tambah Jadwal Praktik
                </h3>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-tambah-jadwal">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form action="jadwal.php" method="POST" class="p-5 space-y-5">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">

                <!-- Baris 1: Poli & Dokter -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add_poli_id" class="block mb-2 text-sm text-gray-500">Poli *</label>
                        <select id="add_poli_id" name="poli_id" onchange="syncFormJadwal('add')" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                            <?php foreach ($listPoli as $p): ?>
                                <option value="<?= (int) $p['id'] ?>"
                                        data-butuh-reservasi="<?= $p['butuh_reservasi'] ? '1' : '0' ?>"
                                        data-kuota-default="<?= (int) ($p['kuota_harian'] ?? 0) ?>">
                                    <?= e($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="add_dokter_id" class="block mb-2 text-sm text-gray-500">Dokter / Psikolog</label>
                        <select id="add_dokter_id" name="dokter_id" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3">
                            <option value="">-- Belum ditetapkan --</option>
                            <?php foreach ($listDokter as $d): ?>
                                <option value="<?= (int) $d['id'] ?>" data-poli-id="<?= (int) $d['poli_id'] ?>">
                                    <?= e($d['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Baris 2: Tanggal -->
                <div>
                    <label for="add_tanggal" class="block mb-2 text-sm text-gray-500">Tanggal *</label>
                    <input type="date" id="add_tanggal" name="tanggal" value="<?= e($filterTanggal) ?>" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                </div>

                <!-- Baris 3: Jam Mulai & Jam Selesai -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add_jam_mulai" class="block mb-2 text-sm text-gray-500">Jam mulai *</label>
                        <input type="time" id="add_jam_mulai" name="jam_mulai" value="09:00" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                    </div>
                    <div>
                        <label for="add_jam_selesai" class="block mb-2 text-sm text-gray-500">Jam selesai *</label>
                        <input type="time" id="add_jam_selesai" name="jam_selesai" value="12:00" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                    </div>
                </div>

                <!-- Box Kuota Pasien -->
                <div class="p-5 border border-primary bg-page rounded">
                    <label class="block mb-4 text-sm font-bold text-primary-700">Kuota pasien per jadwal *</label>
                    
                    <!-- Stepper Input Kuota -->
                    <div id="add_stepper_wrap" class="flex items-center gap-4 mb-4">
                        <button type="button" onclick="ubahKuota('add', -1)" class="w-12 h-12 flex items-center justify-center bg-white border border-primary text-primary-600 rounded hover:bg-primary-50 transition-colors focus:ring-2 focus:ring-primary-300">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                            </svg>
                        </button>
                        
                        <input type="hidden" id="add_kuota_input" name="kuota" value="10">
                        <span id="add_kuota_display" class="text-2xl font-normal text-gray-900 w-10 text-center">10</span>
                        
                        <button type="button" onclick="ubahKuota('add', 1)" class="w-12 h-12 flex items-center justify-center bg-primary text-white rounded hover:bg-primary-700 transition-colors focus:ring-2 focus:ring-primary-300">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                            </svg>
                        </button>
                        
                        <span id="add_kuota_hint" class="text-sm text-gray-500 ml-2">pasien</span>
                    </div>

                    <!-- Checkbox Tanpa Batas -->
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="add_tanpa_batas" name="tanpa_batas" onchange="toggleTanpaBatas('add')" class="w-5 h-5 bg-white border border-primary rounded text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-sm text-gray-600">Tanpa batas kuota (khusus Poli Umum / walk-in)</span>
                    </label>
                </div>

                <!-- Publikasikan Jadwal -->
                <div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="dipublikasi" value="1" checked class="w-5 h-5 bg-white border border-primary rounded text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Langsung publikasikan jadwal (agar tampil di halaman reservasi pasien)</span>
                    </label>
                </div>

                <!-- Modal Footer / Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-4">
                    <button type="button" data-modal-hide="modal-tambah-jadwal" class="px-6 py-3 text-sm font-bold text-primary bg-white border border-primary rounded hover:bg-primary hover:text-white focus:ring-4 focus:outline-none focus:ring-primary-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                        Simpan Jadwal
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function syncFormJadwal(prefix, keepCurrentKuota = false) {
    const poliSelect = document.getElementById(prefix + '_poli_id');
    const dokterSelect = document.getElementById(prefix + '_dokter_id');
    if (!poliSelect || !dokterSelect) return;

    const selectedOpt = poliSelect.options[poliSelect.selectedIndex];
    const poliId = poliSelect.value;
    const butuhReservasi = selectedOpt.getAttribute('data-butuh-reservasi') === '1';
    const kuotaDefault = parseInt(selectedOpt.getAttribute('data-kuota-default') || '5', 10);

    // Filter dokter sesuai poli_id
    Array.from(dokterSelect.options).forEach(opt => {
        if (opt.value === '') {
            opt.hidden = false;
            return;
        }
        const match = opt.getAttribute('data-poli-id') === poliId;
        opt.hidden = !match;
        if (!match && opt.selected) {
            dokterSelect.value = '';
        }
    });

    const cbTanpaBatas = document.getElementById(prefix + '_tanpa_batas');
    const kuotaInput = document.getElementById(prefix + '_kuota_input');
    const kuotaDisplay = document.getElementById(prefix + '_kuota_display');
    const kuotaHint = document.getElementById(prefix + '_kuota_hint');

    if (!butuhReservasi) {
        cbTanpaBatas.checked = true;
        cbTanpaBatas.disabled = true;
        kuotaHint.textContent = 'Poli walk-in tidak menggunakan kuota';
    } else {
        cbTanpaBatas.checked = false;
        cbTanpaBatas.disabled = true;
        if (!keepCurrentKuota) {
            kuotaInput.value = kuotaDefault;
            kuotaDisplay.textContent = kuotaDefault;
        }
        kuotaHint.textContent = 'pasien (bawaan ' + kuotaDefault + ')';
    }
    toggleTanpaBatas(prefix);
}

function toggleTanpaBatas(prefix) {
    const cbTanpaBatas = document.getElementById(prefix + '_tanpa_batas');
    const stepperWrap = document.getElementById(prefix + '_stepper_wrap');
    if (!cbTanpaBatas || !stepperWrap) return;

    if (cbTanpaBatas.checked) {
        stepperWrap.classList.add('opacity-40', 'pointer-events-none');
    } else {
        stepperWrap.classList.remove('opacity-40', 'pointer-events-none');
    }
}

function ubahKuota(prefix, delta) {
    const kuotaInput = document.getElementById(prefix + '_kuota_input');
    const kuotaDisplay = document.getElementById(prefix + '_kuota_display');
    let val = parseInt(kuotaInput.value || '1', 10) + delta;
    if (val < 1) val = 1;
    if (val > 100) val = 100;
    kuotaInput.value = val;
    kuotaDisplay.textContent = val;
}

document.addEventListener('DOMContentLoaded', function() {
    syncFormJadwal('add', false);
    if (document.getElementById('edit_poli_id')) {
        syncFormJadwal('edit', true);
    }
});
</script>