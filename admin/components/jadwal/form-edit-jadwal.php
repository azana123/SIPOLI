<?php
$isEditOpen   = !empty($editJadwal);
$eId          = (int) ($editJadwal['id'] ?? 0);
$ePoliId      = (int) ($editJadwal['poli_id'] ?? 0);
$eDokterId    = (int) ($editJadwal['dokter_id'] ?? 0);
$eTanggal     = $editJadwal['tanggal'] ?? $filterTanggal;
$eJamMulai    = !empty($editJadwal['jam_mulai']) ? substr($editJadwal['jam_mulai'], 0, 5) : '09:00';
$eJamSelesai  = !empty($editJadwal['jam_selesai']) ? substr($editJadwal['jam_selesai'], 0, 5) : '12:00';
$eKuota       = isset($editJadwal['kuota']) && $editJadwal['kuota'] !== null ? (int) $editJadwal['kuota'] : 5;
$eTanpaBatas  = ($editJadwal && $editJadwal['kuota'] === null);
$eStatus      = $editJadwal['status'] ?? 'buka';
$eDipublikasi = !empty($editJadwal['dipublikasi']);
?>
<!-- Modal Edit Jadwal Praktik -->
<div id="modal-edit-jadwal" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isEditOpen ? 'false' : 'true' ?>" class="<?= $isEditOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">
                    Edit Jadwal Praktik
                </h3>
                <a href="jadwal.php?tanggal=<?= urlencode($filterTanggal) ?>" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($editJadwal): ?>
                <!-- Modal Body (Form Update) -->
                <form action="jadwal.php" method="POST" class="p-5 space-y-5">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $eId ?>">
                    <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">

                    <!-- Baris 1: Poli & Dokter -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_poli_id" class="block mb-2 text-sm text-gray-500">Poli *</label>
                            <select id="edit_poli_id" name="poli_id" onchange="syncFormJadwal('edit', false)" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                                <?php foreach ($listPoli as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>"
                                            data-butuh-reservasi="<?= $p['butuh_reservasi'] ? '1' : '0' ?>"
                                            data-kuota-default="<?= (int) ($p['kuota_harian'] ?? 0) ?>"
                                            <?= $ePoliId === (int) $p['id'] ? 'selected' : '' ?>>
                                        <?= e($p['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="edit_dokter_id" class="block mb-2 text-sm text-gray-500">Dokter / Psikolog</label>
                            <select id="edit_dokter_id" name="dokter_id" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3">
                                <option value="">-- Belum ditetapkan --</option>
                                <?php foreach ($listDokter as $d): ?>
                                    <option value="<?= (int) $d['id'] ?>"
                                            data-poli-id="<?= (int) $d['poli_id'] ?>"
                                            <?= $eDokterId === (int) $d['id'] ? 'selected' : '' ?>>
                                        <?= e($d['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Baris 2: Tanggal -->
                    <div>
                        <label for="edit_tanggal" class="block mb-2 text-sm text-gray-500">Tanggal *</label>
                        <input type="date" id="edit_tanggal" name="tanggal" value="<?= e($eTanggal) ?>" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                    </div>

                    <!-- Baris 3: Jam Mulai & Jam Selesai -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_jam_mulai" class="block mb-2 text-sm text-gray-500">Jam mulai *</label>
                            <input type="time" id="edit_jam_mulai" name="jam_mulai" value="<?= e($eJamMulai) ?>" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                        </div>
                        <div>
                            <label for="edit_jam_selesai" class="block mb-2 text-sm text-gray-500">Jam selesai *</label>
                            <input type="time" id="edit_jam_selesai" name="jam_selesai" value="<?= e($eJamSelesai) ?>" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                        </div>
                    </div>

                    <!-- Box Kuota Pasien -->
                    <div class="p-5 border border-primary bg-page rounded">
                        <label class="block mb-4 text-sm font-bold text-primary-700">Kuota pasien per jadwal *</label>
                        
                        <div id="edit_stepper_wrap" class="flex items-center gap-4 mb-4">
                            <button type="button" onclick="ubahKuota('edit', -1)" class="w-12 h-12 flex items-center justify-center bg-white border border-primary text-primary-600 rounded hover:bg-primary-50 transition-colors focus:ring-2 focus:ring-primary-300">
                                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                                </svg>
                            </button>
                            
                            <input type="hidden" id="edit_kuota_input" name="kuota" value="<?= $eKuota ?>">
                            <span id="edit_kuota_display" class="text-2xl font-normal text-gray-900 w-10 text-center"><?= $eKuota ?></span>
                            
                            <button type="button" onclick="ubahKuota('edit', 1)" class="w-12 h-12 flex items-center justify-center bg-primary text-white rounded hover:bg-primary-700 transition-colors focus:ring-2 focus:ring-primary-300">
                                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                                </svg>
                            </button>
                            
                            <span id="edit_kuota_hint" class="text-sm text-gray-500 ml-2">pasien</span>
                        </div>

                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="edit_tanpa_batas" name="tanpa_batas" <?= $eTanpaBatas ? 'checked' : '' ?> onchange="toggleTanpaBatas('edit')" class="w-5 h-5 bg-white border border-primary rounded text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-sm text-gray-600">Tanpa batas kuota (khusus Poli Umum / walk-in)</span>
                        </label>
                    </div>

                    <!-- Status Jadwal & Publikasi -->
                    <div class="grid grid-cols-2 gap-4 items-center">
                        <div>
                            <label for="edit_status" class="block mb-2 text-sm text-gray-500">Status Jadwal *</label>
                            <select id="edit_status" name="status" class="bg-white border border-primary text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-3 py-2.5">
                                <option value="buka" <?= $eStatus === 'buka' ? 'selected' : '' ?>>Buka (Tersedia)</option>
                                <option value="penuh" <?= $eStatus === 'penuh' ? 'selected' : '' ?>>Penuh</option>
                                <option value="tutup" <?= $eStatus === 'tutup' ? 'selected' : '' ?>>Nonaktif / Tutup</option>
                            </select>
                        </div>
                        <div class="pt-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dipublikasi" value="1" <?= $eDipublikasi ? 'checked' : '' ?> class="w-5 h-5 bg-white border border-primary rounded text-primary-600 focus:ring-primary-500">
                                <span class="ml-2.5 text-sm font-medium text-gray-700">Dipublikasikan</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer / Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <?php if ($eStatus !== 'tutup'): ?>
                            <button type="submit" form="form-soft-delete-jadwal" onclick="return confirm('Nonaktifkan (tutup) jadwal ini? Semua reservasi aktif pada jadwal ini akan otomatis dibatalkan dan pasien akan menerima notifikasi.')" class="px-4 py-2.5 text-sm font-bold text-red-600 bg-red-50 rounded hover:bg-red-100 transition-colors">
                                Nonaktifkan Jadwal
                            </button>
                        <?php else: ?>
                            <span class="text-xs text-gray-400 italic">Jadwal berstatus Nonaktif (Tutup)</span>
                        <?php endif; ?>

                        <div class="flex items-center gap-3">
                            <a href="jadwal.php?tanggal=<?= urlencode($filterTanggal) ?>" class="px-6 py-3 text-sm font-bold text-primary bg-white border border-primary rounded hover:bg-primary hover:text-white focus:ring-4 focus:outline-none focus:ring-primary-100 transition-colors">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                                Simpan Jadwal
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Form Terpisah untuk Soft Delete (Tutup Jadwal) -->
                <form id="form-soft-delete-jadwal" action="jadwal.php" method="POST" class="hidden">
                    <input type="hidden" name="action" value="soft_delete">
                    <input type="hidden" name="id" value="<?= $eId ?>">
                    <input type="hidden" name="redirect_tanggal" value="<?= e($filterTanggal) ?>">
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>