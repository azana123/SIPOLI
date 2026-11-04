<!-- Modal Edit Data Pasien -->
<div id="modal-form-pasien" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    Tambah/Edit Data Pasien
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-form-pasien">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form id="form-edit-pasien" action="#" method="POST" class="p-5 space-y-4">
                
                <!-- NIM / NIP (Readonly/Disabled) -->
                <div>
                    <label for="edit_nim" class="block mb-2 text-sm font-medium text-gray-600">NIM / NIP (Tidak dapat diubah)</label>
                    <input type="text" id="edit_nim" name="nim" value="2341720101" disabled class="bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 cursor-not-allowed">
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label for="edit_nama" class="block mb-2 text-sm font-medium text-gray-600">Nama Lengkap</label>
                    <input type="text" id="edit_nama" name="nama" value="Alya Prameswari" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                </div>

                <!-- Grid 2 Kolom untuk field sisanya -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Nomor HP -->
                    <div>
                        <label for="edit_hp" class="block mb-2 text-sm font-medium text-gray-600">Nomor HP</label>
                        <input type="text" id="edit_hp" name="hp" value="081234560101" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="edit_tgl_lahir" class="block mb-2 text-sm font-medium text-gray-600">Tanggal Lahir</label>
                        <input type="date" id="edit_tgl_lahir" name="tgl_lahir" value="2005-02-12" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="edit_jk" class="block mb-2 text-sm font-medium text-gray-600">Jenis Kelamin</label>
                        <select id="edit_jk" name="jk" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan" selected>Perempuan</option>
                        </select>
                    </div>

                    <!-- Status Civitas -->
                    <div>
                        <label for="edit_civitas" class="block mb-2 text-sm font-medium text-gray-600">Status Civitas</label>
                        <select id="edit_civitas" name="civitas" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="Mahasiswa" selected>Mahasiswa</option>
                            <option value="Dosen">Dosen</option>
                            <option value="Tendik">Tendik</option>
                            <option value="Umum">Umum</option>
                        </select>
                    </div>

                    <!-- Golongan Darah -->
                    <div>
                        <label for="edit_goldar" class="block mb-2 text-sm font-medium text-gray-600">Golongan Darah</label>
                        <select id="edit_goldar" name="goldar" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O" selected>O</option>
                        </select>
                    </div>

                    <!-- Riwayat Alergi -->
                    <div>
                        <label for="edit_alergi" class="block mb-2 text-sm font-medium text-gray-600">Riwayat Alergi (Opsional)</label>
                        <input type="text" id="edit_alergi" name="alergi" value="Amoxicillin" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                    </div>

                </div>
            </form>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                <button type="button" data-modal-hide="modal-form-pasien" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                    Batal
                </button>
                <button type="submit" form="form-edit-pasien" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                    Simpan Perubahan
                </button>
            </div>

        </div>
    </div>
</div>