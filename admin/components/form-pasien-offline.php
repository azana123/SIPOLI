<?php

$poliWalkinList = [];
if (isset($pdo)) {
    try {
        $poliWalkinList = QueryAdminPasienOffline::poliWalkinList();
    } catch (PDOException $e) {
        $poliWalkinList = [];
    }
}
?>
<!-- Modal Tambah Pasien Kunjungan Langsung -->
<div id="modal-tambah-pasien" tabindex="-1" aria-hidden="true" data-modal-backdrop="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        Tambah Pasien Kunjungan Langsung
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftarkan pasien yang datang langsung ke poliklinik</p>
                </div>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="modal-tambah-pasien">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form class="p-5" action="components/form-pasien-offline.php" method="POST" data-api-endpoint="/api/admin/pasien-offline.php" data-api-redirect="/admin/reservasi.php">
                <input type="hidden" name="action" value="daftar_pasien_offline">

                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="walkin_nim_nip" class="block mb-2 text-sm font-semibold text-gray-800">NIM / NIP Civitas Akademika *</label>
                        <input type="text" name="nim_nip" id="walkin_nim_nip" pattern="[0-9]{8,20}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" placeholder="Masukkan NIM atau NIP terdaftar" required>
                        <p class="mt-1.5 text-xs text-gray-500">Nama lengkap, tanggal lahir, dan status civitas diambil otomatis dari data civitas akademika.</p>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="walkin_poli_id" class="block mb-2 text-sm font-semibold text-gray-800">Poli Tujuan *</label>
                        <select id="walkin_poli_id" name="poli_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <?php if (!empty($poliWalkinList)): ?>
                                <?php foreach ($poliWalkinList as $pw): ?>
                                    <option value="<?= (int) $pw['id'] ?>"><?= htmlspecialchars($pw['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">Poli Umum</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="walkin_no_hp" class="block mb-2 text-sm font-semibold text-gray-800">Nomor HP (Opsional)</label>
                        <input type="text" name="no_hp" id="walkin_no_hp" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-base focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-toggle="modal-tambah-pasien" class="px-4 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-base hover:bg-gray-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="text-white inline-flex items-center bg-primary hover:bg-primary-700 font-semibold rounded-base text-xs px-4 py-2 text-center cursor-pointer transition-colors">
                        <i class="fas fa-plus mr-1.5"></i> Buat Antrean Kunjungan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>