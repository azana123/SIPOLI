<div id="modal-detail-reservasi" data-modal-backdrop="true" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow">
            <div class="flex items-center justify-between p-5 pb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    Detail reservasi
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="modal-detail-reservasi">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>
            <div class="p-5 pt-0 space-y-6">
                <div class="flex justify-between items-start bg-[#F4F9F9] p-5 rounded-xl">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">Alya Prameswari</h4>
                        <p class="text-sm text-gray-500 mt-1">NIM · 2341720101</p>
                    </div>
                    <div class="text-right">
                        <span class="bg-[#E1F2E8] text-[#147B44] text-xs font-semibold px-3 py-1 rounded-md">Selesai</span>
                        <p class="text-xs text-gray-500 mt-2">Kunjungan selesai</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Poli</p>
                        <p class="text-base font-semibold text-gray-900">Poli Umum</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Dokter</p>
                        <p class="text-base font-semibold text-gray-900">dr. Andini Putri</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Tanggal kunjungan</p>
                        <p class="text-base font-semibold text-gray-900">24 September 2026</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Jadwal kunjungan</p>
                        <p class="text-base font-semibold text-gray-900">09.00–12.00 WIB</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Asal reservasi</p>
                        <p class="text-base font-semibold text-gray-900">Online</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Waktu reservasi</p>
                        <p class="text-base font-semibold text-gray-900">Belum tercatat</p>
                    </div>
                </div>

                <hr class="h-px bg-gray-100 border-0">

                <div class="space-y-4">
                    <div>
                        <h5 class="text-sm font-bold text-gray-900 mb-1">Keluhan awal</h5>
                        <p class="text-sm text-gray-500">Belum tercatat pada data reservasi.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Konfirmasi H-1</p>
                            <p class="text-sm font-semibold text-gray-900">Tidak berlaku · bukan layanan Konseling</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Catatan admin</p>
                            <p class="text-sm font-semibold text-gray-900">Belum ada catatan</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center p-4 text-sm text-[#147B44] rounded-lg bg-[#E6F4EA]" role="alert">
                    <svg class="flex-shrink-0 inline w-5 h-5 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        Kunjungan selesai. Tidak ada tindakan check-in untuk reservasi ini.
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" class="text-white bg-[#157F8F] hover:bg-[#106573] focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-6 py-2.5 text-center" data-modal-toggle="modal-detail-reservasi">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>