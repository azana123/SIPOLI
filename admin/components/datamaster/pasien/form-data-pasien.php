<?php
$isEditPasienOpen = !empty($editPasien);
$epId      = (int) ($editPasien['id'] ?? 0);
$epNim     = $editPasien['nim_nip'] ?? '';
$epNama    = $editPasien['nama'] ?? '';
$epHp      = $editPasien['no_hp'] ?? '';
$epTgl     = $editPasien['tanggal_lahir'] ?? '';
$epJk      = ($editPasien['jenis_kelamin'] ?? '') === 'P' ? 'Perempuan' : 'Laki-laki';
$epCivitas = ucfirst($editPasien['status_civitas'] ?? '');
$epGoldar  = $editPasien['golongan_darah'] ?? '';
$epAlergi  = $editPasien['riwayat_alergi'] ?? '';
?>
<!-- Modal Tambah / Edit Data Pasien -->
<div id="modal-form-pasien" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isEditPasienOpen ? 'false' : 'true' ?>" class="<?= $isEditPasienOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    <?= $isEditPasienOpen ? 'Edit Data Pasien' : 'Tambah Data Pasien' ?>
                </h3>
                <?php if ($isEditPasienOpen): ?>
                    <a href="datamaster.php?tab=pasien" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </a>
                <?php else: ?>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-form-pasien">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Tutup modal</span>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Modal Body (Form) -->
            <form id="form-edit-pasien" action="datamaster.php" method="POST" class="p-5 space-y-4">
                <input type="hidden" name="action" value="save_pasien">
                <input type="hidden" name="tab" value="pasien">
                <input type="hidden" name="pasien_id" value="<?= $epId ?>">

                <?php if ($isEditPasienOpen): ?>
                    <!-- Mode Edit: Identitas dari data_civitas ditampilkan readonly/disabled -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">NIM / NIP (Tidak dapat diubah)</label>
                        <input type="text" value="<?= e($epNim) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">Nama Lengkap (Sesuai Data Civitas)</label>
                        <input type="text" value="<?= e($epNama) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-600">Tanggal Lahir</label>
                            <input type="date" value="<?= e($epTgl) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-600">Jenis Kelamin</label>
                            <input type="text" value="<?= e($epJk) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-600">Status Civitas</label>
                            <input type="text" value="<?= e($epCivitas) ?>" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded block w-full p-2.5 cursor-not-allowed">
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Mode Tambah: Cukup masukkan NIM/NIP civitas akademika -->
                    <div>
                        <label for="add_nim_nip" class="block mb-2 text-sm font-medium text-gray-600">NIM / NIP Civitas Akademika *</label>
                        <input type="text" id="add_nim_nip" name="nim_nip" pattern="[0-9]{8,20}" placeholder="Masukkan NIM atau NIP aktif..." class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    </div>
                <?php endif; ?>

                <!-- Field yang dapat diisi/diubah oleh Admin -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_hp" class="block mb-2 text-sm font-medium text-gray-600">Nomor HP</label>
                        <input type="text" id="edit_hp" name="no_hp" value="<?= e($epHp) ?>" placeholder="08xxxxxxxxxx" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="edit_goldar" class="block mb-2 text-sm font-medium text-gray-600">Golongan Darah</label>
                        <select id="edit_goldar" name="golongan_darah" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                            <option value="">-- Belum diketahui --</option>
                            <?php foreach (['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gd): ?>
                                <option value="<?= $gd ?>" <?= $epGoldar === $gd ? 'selected' : '' ?>><?= $gd ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="edit_alergi" class="block mb-2 text-sm font-medium text-gray-600">Riwayat Alergi (Opsional)</label>
                    <input type="text" id="edit_alergi" name="riwayat_alergi" value="<?= e($epAlergi) ?>" placeholder="Contoh: Amoxicillin, Debu, Udang" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                <?php if ($isEditPasienOpen): ?>
                    <a href="datamaster.php?tab=pasien" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                <?php else: ?>
                    <button type="button" data-modal-hide="modal-form-pasien" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                <?php endif; ?>
                <button type="submit" form="form-edit-pasien" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors cursor-pointer">
                    Simpan Data
                </button>
            </div>

        </div>
    </div>
</div>