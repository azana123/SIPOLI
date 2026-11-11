<?php
$isEditDokterOpen = !empty($editDokter);
$edId       = (int) ($editDokter['id'] ?? 0);
$edNama     = $editDokter['nama'] ?? '';
$edUsername = $editDokter['username'] ?? '';
$edPoliId   = (int) ($editDokter['poli_id'] ?? 0);
$edProfesi  = $editDokter['profesi'] ?? 'dokter umum';
$edStr      = $editDokter['no_str'] ?? '';
$edHp       = $editDokter['no_hp'] ?? '';
?>
<!-- Modal Tambah / Edit Tenaga Layanan -->
<div id="modal-form-tenaga-layanan" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isEditDokterOpen ? 'false' : 'true' ?>" class="<?= $isEditDokterOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    <?= $isEditDokterOpen ? 'Edit Data Tenaga Layanan' : 'Tambah Data Tenaga Layanan' ?>
                </h3>
                <?php if ($isEditDokterOpen): ?>
                    <a href="datamaster.php?tab=tenaga-layanan" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </a>
                <?php else: ?>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-form-tenaga-layanan">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </button>
                <?php endif; ?>
            </div>

            <form id="form-dokter" action="datamaster.php" method="POST" class="p-5 space-y-4">
                <input type="hidden" name="action" value="save_dokter">
                <input type="hidden" name="tab" value="tenaga-layanan">
                <input type="hidden" name="dokter_id" value="<?= $edId ?>">

                <div>
                    <label for="dok_nama" class="block mb-2 text-sm font-medium text-gray-600">Nama Lengkap & Gelar *</label>
                    <input type="text" id="dok_nama" name="nama" value="<?= e($edNama) ?>" placeholder="Contoh: dr. Andi Pratama" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="dok_poli" class="block mb-2 text-sm font-medium text-gray-600">Poli Layanan *</label>
                        <select id="dok_poli" name="poli_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <?php foreach ($listPoli as $p): ?>
                                <option value="<?= (int) $p['id'] ?>" <?= $edPoliId === (int) $p['id'] ? 'selected' : '' ?>>
                                    <?= e($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="dok_profesi" class="block mb-2 text-sm font-medium text-gray-600">Profesi *</label>
                        <select id="dok_profesi" name="profesi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="dokter umum" <?= $edProfesi === 'dokter umum' ? 'selected' : '' ?>>Dokter Umum</option>
                            <option value="dokter gigi" <?= $edProfesi === 'dokter gigi' ? 'selected' : '' ?>>Dokter Gigi</option>
                            <option value="psikolog" <?= $edProfesi === 'psikolog' ? 'selected' : '' ?>>Psikolog</option>
                        </select>
                    </div>

                    <div>
                        <label for="dok_str" class="block mb-2 text-sm font-medium text-gray-600">Nomor STR</label>
                        <input type="text" id="dok_str" name="no_str" value="<?= e($edStr) ?>" placeholder="Contoh: STR-DU-20250001" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="dok_hp" class="block mb-2 text-sm font-medium text-gray-600">Nomor HP</label>
                        <input type="text" id="dok_hp" name="no_hp" value="<?= e($edHp) ?>" placeholder="08xxxxxxxxxx" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="dok_username" class="block mb-2 text-sm font-medium text-gray-600">Username Login *</label>
                        <input type="text" id="dok_username" name="username" value="<?= e($edUsername) ?>" placeholder="Contoh: dr.andi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    </div>

                    <div>
                        <label for="dok_password" class="block mb-2 text-sm font-medium text-gray-600">
                            <?= $isEditDokterOpen ? 'Password Baru (Opsional)' : 'Password Login *' ?>
                        </label>
                        <input type="password" id="dok_password" name="password" minlength="8" placeholder="<?= $isEditDokterOpen ? 'Kosongkan jika tetap' : 'Minimal 8 karakter' ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" <?= $isEditDokterOpen ? '' : 'required' ?>>
                    </div>
                </div>
            </form>

            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                <?php if ($isEditDokterOpen): ?>
                    <a href="datamaster.php?tab=tenaga-layanan" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="button" data-modal-hide="modal-form-tenaga-layanan" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                <?php endif; ?>
                <button type="submit" form="form-dokter" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 rounded transition-colors cursor-pointer">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>