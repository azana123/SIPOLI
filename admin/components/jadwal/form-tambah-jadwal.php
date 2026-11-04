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
                <!-- Ikon silang (close) yang lebih tipis sesuai desain -->
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-tambah-jadwal">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form action="#" method="POST" class="p-5 space-y-5">
                
                <!-- Baris 1: Poli & Dokter -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="poli" class="block mb-2 text-sm text-gray-500">Poli *</label>
                        <!-- Menggunakan border-primary-600 secara default untuk mencocokkan desain form aktif -->
                        <select id="poli" name="poli" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                            <option value="gigi" selected>Poli Gigi</option>
                            <option value="umum">Poli Umum</option>
                            <option value="kia">Poli KIA</option>
                        </select>
                    </div>
                    <div>
                        <label for="dokter" class="block mb-2 text-sm text-gray-500">Dokter *</label>
                        <select id="dokter" name="dokter" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                            <option value="nadia" selected>drg. Nadia</option>
                            <option value="andini">dr. Andini Putri</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 2: Tanggal -->
                <div>
                    <label for="tanggal" class="block mb-2 text-sm text-gray-500">Tanggal *</label>
                    <input type="text" id="tanggal" name="tanggal" value="24 September 2026" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                </div>

                <!-- Baris 3: Jam Mulai & Jam Selesai -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="jam_mulai" class="block mb-2 text-sm text-gray-500">Jam mulai *</label>
                        <input type="time" id="jam_mulai" name="jam_mulai" value="13:00" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                    </div>
                    <div>
                        <label for="jam_selesai" class="block mb-2 text-sm text-gray-500">Jam selesai *</label>
                        <input type="time" id="jam_selesai" name="jam_selesai" value="16:00" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3" required>
                    </div>
                </div>

                <!-- Box Kuota Pasien -->
                <div class="p-5 border border-primary bg-page rounded">
                    <label class="block mb-4 text-sm font-bold text-primary-700">Kuota pasien per hari *</label>
                    
                    <!-- Stepper Input Kuota -->
                    <div class="flex items-center gap-4 mb-4">
                        <!-- Tombol Kurang -->
                        <button type="button" class="w-12 h-12 flex items-center justify-center bg-white border border-primary text-primary-600 rounded hover:bg-primary-50 transition-colors focus:ring-2 focus:ring-primary-300">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                            </svg>
                        </button>
                        
                        <!-- Nilai Kuota -->
                        <span class="text-2xl font-normal text-gray-900 w-8 text-center">5</span>
                        
                        <!-- Tombol Tambah -->
                        <button type="button" class="w-12 h-12 flex items-center justify-center bg-primary text-white rounded hover:bg-primary-700 transition-colors focus:ring-2 focus:ring-primary-300">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                            </svg>
                        </button>
                        
                        <span class="text-sm text-gray-500 ml-2">pasien (bawaan 5)</span>
                    </div>

                    <!-- Checkbox Tanpa Batas -->
                    <label class="inline-flex items-center cursor-pointer">
                        <!-- Checkbox dengan border primer -->
                        <input type="checkbox" name="tanpa_batas" class="w-5 h-5 bg-white border border-primary rounded text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-sm text-gray-600">Tanpa batas kuota (untuk layanan perawat)</span>
                    </label>
                </div>

                <!-- Keterangan (Opsional) -->
                <div>
                    <!-- Label menggunakan teks reguler abu-abu sesuai desain terbaru -->
                    <label for="keterangan" class="block mb-2 text-sm text-gray-500">Keterangan (opsional)</label>
                    <!-- Input Keterangan dengan border abu-abu standar (belum diisi) -->
                    <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: libur nasional, dokter pengganti" class="bg-white border border-primary text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-600 block w-full px-4 py-3 placeholder-gray-400">
                </div>

                <!-- Modal Footer / Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-4">
                    <!-- Tombol Batal bergaya Outline dengan warna Primer -->
                    <button type="button" data-modal-hide="modal-tambah-jadwal" class="px-6 py-3 text-sm font-bold text-primary bg-white border border-primary rounded hover:bg-primary hover:text-white focus:ring-4 focus:outline-none focus:ring-primary-100 transition-colors>
                        Batal
                    </button>
                    <!-- Tombol Simpan bergaya Solid -->
                    <button type="submit" class="px-6 py-3 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                        Simpan Jadwal
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>