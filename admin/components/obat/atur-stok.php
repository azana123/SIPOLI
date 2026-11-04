<!-- Modal Atur Stok -->
<div id="modal-atur-stok" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Atur stok
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Sesuaikan jumlah stok obat dan catat alasan perubahan.
                    </p>
                </div>
                <button type="button" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-atur-stok">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form action="#" method="POST" class="p-5 space-y-5">
                
                <!-- Info Obat -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">Paracetamol</h4>
                        <p class="text-sm text-gray-500 mt-1">OBT-001 · 500 mg · Tablet</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/70 rounded">
                        Aktif
                    </span>
                </div>

                <!-- Box Stok Saat Ini -->
                <div class="px-4 py-3 bg-[var(--color-page)] rounded flex items-center justify-between">
                    <span class="text-sm text-gray-500">Stok saat ini</span>
                    <span class="text-2xl font-bold text-gray-900">240 Tablet</span>
                </div>

                <!-- Jenis Perubahan (Radio Buttons) -->
                <div>
                    <label class="block mb-2 text-sm font-bold text-gray-900">Jenis perubahan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Opsi Tambah Stok (Selected) -->
                        <label class="flex items-center justify-center px-4 py-2 border border-primary-500 bg-[var(--color-page)] rounded cursor-pointer text-primary-700 font-medium text-sm gap-2.5 transition-colors">
                            <input type="radio" name="jenis_perubahan" value="tambah" checked class="w-4 h-4 text-primary-500 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                            <span>Tambah stok</span>
                        </label>

                        <!-- Opsi Kurangi Stok -->
                        <label class="flex items-center justify-center px-4 py-2 border border-gray-300 bg-white rounded cursor-pointer text-gray-700 font-medium text-sm gap-2.5 hover:bg-gray-50 transition-colors">
                            <input type="radio" name="jenis_perubahan" value="kurang" class="w-4 h-4 text-primary-500 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                            <span>Kurangi stok</span>
                        </label>
                    </div>
                </div>

                <!-- Jumlah Perubahan -->
                <div>
                    <label for="jumlah" class="block mb-2 text-sm font-bold text-gray-900">Jumlah perubahan</label>
                    <input type="number" min="1" name="jumlah" id="jumlah" value="40" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    <p class="mt-1.5 text-xs text-gray-500">Satuan: Tablet. Masukkan bilangan bulat lebih dari 0.</p>
                </div>

                <!-- Catatan / Alasan -->
                <div>
                    <label for="catatan" class="block mb-2 text-sm font-bold text-gray-900">Catatan / alasan</label>
                    <textarea id="catatan" name="catatan" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>Penyesuaian hasil pemeriksaan stok fisik.</textarea>
                </div>

                <!-- Box Preview Stok Akhir -->
                <div class="p-4 bg-[var(--color-page)] border border-primary-100/50 rounded">
                    <div class="flex items-start justify-between mb-1">
                        <span class="block text-xs text-gray-500">Stok akhir setelah disimpan</span>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/80 rounded">Tersedia</span>
                    </div>
                    <p class="text-2xl font-bold text-primary-500 mb-1">280 Tablet</p>
                    <p class="text-xs text-gray-500 mb-1">240 + 40 = 280 Tablet</p>
                    <p class="text-xs text-gray-500">Stok akhir tidak boleh negatif. Status obat tetap Aktif.</p>
                </div>

                <!-- Modal Footer / Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" data-modal-hide="modal-atur-stok" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                        Simpan perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>