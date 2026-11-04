<!-- Modal Detail Pasien -->
<div id="modal-detail-pasien" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    Detail Pasien
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-detail-pasien">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Data Grid) -->
            <div class="p-5">
                <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                    
                    <!-- Baris 1 -->
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">NIM / NIP</span>
                        <p class="text-base font-bold text-gray-900">2341720101</p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</span>
                        <p class="text-base font-bold text-gray-900">Alya Prameswari</p>
                    </div>

                    <!-- Baris 2 -->
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Nomor HP</span>
                        <p class="text-base font-bold text-gray-900">081234560101</p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Tanggal Lahir</span>
                        <p class="text-base font-bold text-gray-900">12 Februari 2005</p>
                    </div>

                    <!-- Baris 3 -->
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Jenis Kelamin</span>
                        <p class="text-base font-bold text-gray-900">Perempuan</p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Status Civitas</span>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 rounded">
                            Mahasiswa
                        </span>
                    </div>

                    <!-- Baris 4 -->
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Golongan Darah</span>
                        <p class="text-base font-bold text-gray-900">O</p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 mb-1">Riwayat Alergi</span>
                        <p class="text-base font-bold text-gray-900">Amoxicillin</p>
                    </div>
                    
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end p-5 border-t border-gray-100">
                <!-- Tombol tunggal dengan warna latar belakang soft / abu-abu terang seperti di desain -->
                <button type="button" data-modal-hide="modal-detail-pasien" class="px-6 py-2.5 text-sm font-bold text-gray-800 bg-gray-50 hover:bg-gray-100 rounded focus:ring-4 focus:outline-none focus:ring-gray-200 transition-colors">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>