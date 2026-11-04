<!-- Modal Tambah Obat -->
<div id="modal-tambah-obat" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Tambah Obat
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Lengkapi data obat baru untuk poliklinik.
                    </p>
                </div>
                <button type="button" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-tambah-obat">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form action="#" method="POST" class="p-5">
                
                <!-- Baris 1: Kode Obat & Nama Obat -->
                <div class="grid gap-4 mb-4 grid-cols-1 md:grid-cols-2">
                    <div>
                        <label for="kode_obat" class="block mb-2 text-sm font-bold text-gray-900">Kode obat</label>
                        <input type="text" name="kode_obat" id="kode_obat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" placeholder="OBT-009" required>
                    </div>
                    <div>
                        <label for="nama_obat" class="block mb-2 text-sm font-bold text-gray-900">Nama obat</label>
                        <input type="text" name="nama_obat" id="nama_obat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" placeholder="Vitamin B kompleks" required>
                    </div>
                </div>

                <!-- Baris 2: Bentuk & Satuan -->
                <div class="grid gap-4 mb-4 grid-cols-1 md:grid-cols-2">
                    <div>
                        <label for="bentuk" class="block mb-2 text-sm font-bold text-gray-900">Bentuk</label>
                        <select id="bentuk" name="bentuk" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="Tablet" selected>Tablet</option>
                            <option value="Sirup">Sirup</option>
                            <option value="Kapsul">Kapsul</option>
                            <option value="Salep">Salep</option>
                            <option value="Injeksi">Injeksi</option>
                        </select>
                        <p class="mt-1.5 text-xs text-gray-500">Bentuk sediaan obat.</p>
                    </div>
                    <div>
                        <label for="satuan" class="block mb-2 text-sm font-bold text-gray-900">Satuan</label>
                        <select id="satuan" name="satuan" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="Tablet" selected>Tablet</option>
                            <option value="Botol">Botol</option>
                            <option value="Strip">Strip</option>
                            <option value="Tube">Tube</option>
                            <option value="Ampul">Ampul</option>
                        </select>
                        <p class="mt-1.5 text-xs text-gray-500">Satuan untuk menghitung stok.</p>
                    </div>
                </div>

                <!-- Baris 3: Stok awal -->
                <div class="mb-4">
                    <label for="stok_awal" class="block mb-2 text-sm font-bold text-gray-900">Stok awal</label>
                    <input type="number" min="0" name="stok_awal" id="stok_awal" value="0" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    <p class="mt-1.5 text-xs text-gray-500">Dalam satuan Tablet. Bilangan bulat, minimal 0.</p>
                </div>

                <!-- Baris 4: Dosis umum -->
                <div class="mb-4">
                    <label for="dosis_umum" class="block mb-2 text-sm font-bold text-gray-900">Dosis umum</label>
                    <input type="text" name="dosis_umum" id="dosis_umum" placeholder="Belum diisi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400">
                    <p class="mt-1.5 text-xs text-gray-500">Opsional. Isi hanya sesuai data obat yang terverifikasi.</p>
                </div>

                <!-- Baris 5: Keterangan -->
                <div class="mb-4">
                    <label for="keterangan" class="block mb-2 text-sm font-bold text-gray-900">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="2" placeholder="Belum diisi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400"></textarea>
                </div>

                <!-- Baris 6: Status -->
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-bold text-gray-900">Status</label>
                    <div class="flex items-center gap-3">
                        <!-- Opsi Aktif (Default Selected) -->
                        <label class="flex items-center px-4 py-2 border border-primary-500 bg-primary-50 rounded cursor-pointer text-primary-700 font-medium text-sm gap-2.5 transition-colors">
                            <input type="radio" name="status" value="aktif" checked class="w-4 h-4 text-primary-500 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                            <span>Aktif</span>
                        </label>

                        <!-- Opsi Nonaktif -->
                        <label class="flex items-center px-4 py-2 border border-gray-300 bg-white rounded cursor-pointer text-gray-700 font-medium text-sm gap-2.5 hover:bg-gray-50 transition-colors">
                            <input type="radio" name="status" value="nonaktif" class="w-4 h-4 text-primary-500 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                            <span>Nonaktif</span>
                        </label>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Status aktif terpisah dari ketersediaan stok.</p>
                </div>

                <!-- Modal Footer / Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" data-modal-hide="modal-tambah-obat" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>