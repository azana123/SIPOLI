<!-- Modal Konfirmasi Check-in -->
<div id="modal-konfirmasi-checkin" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100">
            
            <!-- Modal Header (Custom Layout with Icon) -->
            <div class="flex items-start justify-between p-6">
                <div class="flex gap-4">
                    <!-- Icon Box -->
                    <div class="shrink-0 w-12 h-12 flex items-center justify-center bg-page text-primary-700 rounded border border-primary-50">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-6 5h6m-6 4h6M10 3v4h4V3h-4Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">
                            Konfirmasi check-in
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Pastikan pasien sudah hadir dan data reservasi sesuai.
                        </p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-konfirmasi-checkin">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 pb-6 space-y-6">
                
                <!-- Box Data Reservasi Pasien -->
                <div class="border border-gray-200 rounded p-5">
                    
                    <!-- Pasien & Status -->
                    <div class="flex items-start justify-between mb-4 pb-4 border-b border-gray-100">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">Raka Aditya</h4>
                            <p class="text-sm text-gray-500 mt-0.5">NIM &bull; 2341720102</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold text-primary-700 bg-page rounded">
                            Menunggu
                        </span>
                    </div>

                    <!-- Grid Info Reservasi -->
                    <div class="grid grid-cols-2 gap-y-5 gap-x-4 mb-4">
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Poli</span>
                            <p class="text-sm font-bold text-gray-900">Poli Gigi</p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Dokter</span>
                            <p class="text-sm font-bold text-gray-900">drg. Bima Pratama</p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Tanggal kunjungan</span>
                            <p class="text-sm font-bold text-gray-900">24 September 2026</p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Jam layanan</span>
                            <p class="text-sm font-bold text-gray-900">09.00&ndash;12.00 WIB</p>
                        </div>
                    </div>

                    <!-- Keterangan Asal Reservasi -->
                    <p class="text-xs text-gray-500 pt-1">
                        Asal reservasi: Online &bull; Belum check-in
                    </p>
                </div>

                <!-- Alert Info / Warning -->
                <div class="flex items-start p-4 bg-page rounded border border-primary-50">
                    <svg class="shrink-0 w-5 h-5 text-primary-600 mt-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <div class="ml-3 text-sm text-gray-600 leading-relaxed">
                        Setelah dikonfirmasi, kehadiran pasien akan tercatat dan pasien masuk antrean Poli Gigi.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" data-modal-hide="modal-konfirmasi-checkin" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                        Batal
                    </button>
                    <!-- Menggunakan text-white dan bg-primary-600 untuk tombol utama yang solid -->
                    <button type="button" class="px-5 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                        Konfirmasi check-in
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>