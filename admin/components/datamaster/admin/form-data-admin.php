<?php
$isEditAdminOpen = !empty($editAdmin);
$eaId       = (int) ($editAdmin['id'] ?? 0);
$eaNim      = $editAdmin['nim_nip'] ?? '';
$eaNama     = $editAdmin['nama'] ?? '';
$eaUsername = $editAdmin['username'] ?? '';
$eaHp       = $editAdmin['no_hp'] ?? '';
$eaSuper    = !empty($editAdmin['is_super']);
?>
<!-- Modal Tambah / Edit Data Admin -->
<div id="modal-form-admin" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isEditAdminOpen ? 'false' : 'true' ?>" class="<?= $isEditAdminOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    <?= $isEditAdminOpen ? 'Edit Data Admin' : 'Tambah Data Admin' ?>
                </h3>
                <?php if ($isEditAdminOpen): ?>
                    <a href="datamaster.php?tab=admin" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </a>
                <?php else: ?>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-form-admin">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </button>
                <?php endif; ?>
            </div>

            <form id="form-admin" action="datamaster.php" method="POST" class="p-5 space-y-4">
                <input type="hidden" name="action" value="save_admin">
                <input type="hidden" name="tab" value="admin">
                <input type="hidden" name="admin_id" value="<?= $eaId ?>">

                <?php if ($isEditAdminOpen): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-600">NIP / NIM (Tidak dapat diubah)</label>
                            <input type="text" value="<?= e($eaNim) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-600">Nama Lengkap</label>
                            <input type="text" value="<?= e($eaNama) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                        </div>
                    </div>
                <?php else: ?>
                    <div>
                        <label for="adm_nim_nip" class="block mb-2 text-sm font-medium text-gray-600">NIP / NIM Civitas Akademika *</label>
                        <input type="text" id="adm_nim_nip" name="nim_nip" pattern="[0-9]{8,20}" placeholder="Masukkan NIP atau NIM terdaftar..." class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                        <p class="mt-1 text-xs text-gray-500">Nama lengkap dan identitas diambil otomatis dari database civitas akademika.</p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="adm_username" class="block mb-2 text-sm font-medium text-gray-600">Username Login *</label>
                        <input type="text" id="adm_username" name="username" value="<?= e($eaUsername) ?>" placeholder="Contoh: admin.sipoli" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    </div>

                    <div>
                        <label for="adm_password" class="block mb-2 text-sm font-medium text-gray-600">
                            <?= $isEditAdminOpen ? 'Password Baru (Opsional)' : 'Password Login *' ?>
                        </label>
                        <input type="password" id="adm_password" name="password" minlength="8" placeholder="<?= $isEditAdminOpen ? 'Kosongkan jika tetap' : 'Minimal 8 karakter' ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" <?= $isEditAdminOpen ? '' : 'required' ?>>
                    </div>

                    <div>
                        <label for="adm_hp" class="block mb-2 text-sm font-medium text-gray-600">Nomor HP</label>
                        <input type="text" id="adm_hp" name="no_hp" value="<?= e($eaHp) ?>" placeholder="08xxxxxxxxxx" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="adm_peran" class="block mb-2 text-sm font-medium text-gray-600">Peran Admin *</label>
                        <select id="adm_peran" name="is_super" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                            <option value="0" <?= !$eaSuper ? 'selected' : '' ?>>Regular Admin (Operasional Harian)</option>
                            <option value="1" <?= $eaSuper ? 'selected' : '' ?>>Super Admin (Akses Penuh)</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                <?php if ($isEditAdminOpen): ?>
                    <a href="datamaster.php?tab=admin" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="button" data-modal-hide="modal-form-admin" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                <?php endif; ?>
                <button type="submit" form="form-admin" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 rounded transition-colors cursor-pointer">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>
</div>