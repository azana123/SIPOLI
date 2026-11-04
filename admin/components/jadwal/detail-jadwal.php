<!-- Modal Detail Jadwal -->
<div id="modal-detail-jadwal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100 shrink-0">
                <h3 class="text-xl font-bold text-gray-900">
                    Detail jadwal
                </h3>
                <button type="button" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-detail-jadwal">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-5 overflow-y-auto">

                <!-- Profil Dokter & Status -->
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-5">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900">dr. Andini Putri</h4>
                        <p class="text-sm text-gray-500 mt-1">Dokter umum &bull; Poli Umum</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded">
                            Tersedia
                        </span>
                        <span class="text-xs text-gray-500">Dipublikasikan</span>
                    </div>
                </div>

                <!-- Info Waktu -->
                <div class="flex items-center px-4 py-3 bg-[var(--color-page)] rounded mb-5 border border-primary-50">
                    <svg class="w-4 h-4 text-primary-600 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1v3m5-3v3m5-3v3M1 7h18M5 11h10M2 3h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900 mr-4">Kamis, 24 September 2026</span>
                    <div class="w-px h-4 bg-gray-300 mr-4"></div>
                    <span class="text-sm font-medium text-gray-900">09.00&ndash;12.00 WIB</span>
                </div>

                <!-- Grid Kuota (3 Kolom) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Card 1: Kuota terisi -->
                    <div class="p-4 border border-gray-200 rounded bg-white">
                        <span class="block text-xs text-gray-500 mb-1">Kuota terisi</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold text-primary-600">6</span>
                            <span class="text-xl font-bold text-gray-400">/10</span>
                        </div>
                        <span class="block text-xs text-gray-500 mt-1">pasien terdaftar</span>
                    </div>

                    <!-- Card 2: Kuota tersisa -->
                    <div class="p-4 border border-gray-200 rounded bg-white">
                        <span class="block text-xs text-gray-500 mb-1">Kuota tersisa</span>
                        <span class="block text-3xl font-bold text-primary-600">4</span>
                        <span class="block text-xs text-gray-500 mt-1">masih dapat direservasi</span>
                    </div>

                    <!-- Card 3: Sumber reservasi -->
                    <div class="p-4 border border-gray-200 rounded bg-white">
                        <span class="block text-xs text-gray-500 mb-1">Sumber reservasi</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-base font-bold text-primary-700">4 online</span>
                            <span class="text-gray-400">&bull;</span>
                            <span class="text-base font-bold text-primary-700">2 offline</span>
                        </div>
                        <span class="block text-xs text-gray-500 mt-1.5">Total 6 reservasi aktif</span>
                    </div>
                </div>

                <!-- Keterangan Paragraf -->
                <div class="mb-6">
                    <h5 class="text-sm font-bold text-gray-900 mb-1">Keterangan</h5>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Jadwal pelayanan Poli Umum sesi pagi. Kuota maksimal 10 pasien; reservasi online dan pendaftaran offline menggunakan kuota yang sama.
                    </p>
                </div>

                <!-- Bagian Tabel Reservasi -->
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-base font-bold text-gray-900">Daftar reservasi</h5>
                    <span class="text-xs text-gray-500">6 pasien &bull; 6/10 kuota terisi</span>
                </div>

                <div class="overflow-x-auto rounded border border-gray-100">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-500 bg-[var(--color-page)] border-b border-gray-100">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">No.</th>
                                <th scope="col" class="px-4 py-3 font-medium">Pasien</th>
                                <th scope="col" class="px-4 py-3 font-medium">ID reservasi</th>
                                <th scope="col" class="px-4 py-3 font-medium">Sumber</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status reservasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris 1 -->
                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">01</td>
                                <td class="px-4 py-3 font-medium text-gray-900">Aulia Rahma</td>
                                <td class="px-4 py-3 text-gray-500">RSV-260924-001</td>
                                <td class="px-4 py-3 text-gray-500">Online</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 rounded">Dikonfirmasi</span>
                                </td>
                            </tr>
                            <!-- Baris 2 -->
                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">02</td>
                                <td class="px-4 py-3 font-medium text-gray-900">Muhammad Farhan</td>
                                <td class="px-4 py-3 text-gray-500">RSV-260924-002</td>
                                <td class="px-4 py-3 text-gray-500">Online</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 rounded">Dikonfirmasi</span>
                                </td>
                            </tr>
                            <!-- Baris 3 -->
                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">03</td>
                                <td class="px-4 py-3 font-medium text-gray-900">Nadia Putri</td>
                                <td class="px-4 py-3 text-gray-500">RSV-260924-003</td>
                                <td class="px-4 py-3 text-gray-500">Offline</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-blue-700 bg-blue-50 rounded">Terdaftar</span>
                                </td>
                            </tr>
                            <!-- Baris 4 -->
                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">04</td>
                                <td class="px-4 py-3 font-medium text-gray-900">Rizky Maulana</td>
                                <td class="px-4 py-3 text-gray-500">RSV-260924-004</td>
                                <td class="px-4 py-3 text-gray-500">Online</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 rounded">Dikonfirmasi</span>
                                </td>
                            </tr>
                            <!-- Baris 5 -->
                            <tr class="bg-white border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">05</td>
                                <td class="px-4 py-3 font-medium text-gray-900">Salsa Nabila</td>
                                <td class="px-4 py-3 text-gray-500">RSV-260924-005</td>
                                <td class="px-4 py-3 text-gray-500">Online</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-amber-700 bg-amber-50 rounded">Menunggu konfirmasi</span>
                                </td>
                            </tr>
                            <!-- Baris 6 -->
                            <tr class="bg-white hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">06</td>
                                <td class="px-4 py-3 font-medium text-gray-900">Dimas Saputra</td>
                                <td class="px-4 py-3 text-gray-500">RSV-260924-006</td>
                                <td class="px-4 py-3 text-gray-500">Offline</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-blue-700 bg-blue-50 rounded">Terdaftar</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between p-5 border-t border-gray-100 shrink-0">
                <span class="text-xs text-gray-400">Perubahan jadwal dan kuota dikelola oleh Admin.</span>
                <button type="button" data-modal-hide="modal-detail-jadwal" class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>