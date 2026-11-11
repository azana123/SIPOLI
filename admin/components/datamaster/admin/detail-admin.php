<?php
$isDetAdminOpen = !empty($detailAdmin);
$daGender = match ($detailAdmin['jenis_kelamin'] ?? null) {
    'L'     => 'Laki-laki',
    'P'     => 'Perempuan',
    default => '-',
};
?>
<!-- Modal Detail Admin -->
<div id="modal-detail-admin" tabindex="-1" aria-hidden="<?= $isDetAdminOpen ? 'false' : 'true' ?>" class="<?= $isDetAdminOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    Detail Pengelola (Admin)
                </h3>
                <a href="datamaster.php?tab=admin" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($detailAdmin): ?>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">NIP / NIM</span>
                            <p class="text-base font-bold text-gray-900"><?= e($detailAdmin['nim_nip'] ?? '-') ?></p>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</span>
                            <p class="text-base font-bold text-gray-900"><?= e($detailAdmin['nama']) ?></p>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Username Akun</span>
                            <p class="text-base font-bold text-gray-900"><?= e($detailAdmin['username']) ?></p>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Nomor HP</span>
                            <p class="text-base font-bold text-gray-900"><?= e($detailAdmin['no_hp'] ?? '-') ?></p>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Peran Admin</span>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold text-primary-700 bg-primary-50 rounded">
                                <?= $detailAdmin['is_super'] ? 'Super Admin' : 'Regular Admin' ?>
                            </span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Jenis Kelamin</span>
                            <p class="text-base font-bold text-gray-900"><?= e($daGender) ?></p>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Unit / Bagian</span>
                            <p class="text-base font-bold text-gray-900"><?= e($detailAdmin['unit'] ?? '-') ?></p>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 mb-1">Status Akun</span>
                            <?php if ($detailAdmin['status_aktif']): ?>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 rounded">Aktif</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-bold text-red-700 bg-red-100 rounded">Non-Aktif</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex items-center justify-end p-5 border-t border-gray-100">
                <a href="datamaster.php?tab=admin" class="px-6 py-2.5 text-sm font-bold text-gray-800 bg-gray-50 hover:bg-gray-100 rounded transition-colors">
                    Tutup
                </a>
            </div>
        </div>
    </div>
</div>