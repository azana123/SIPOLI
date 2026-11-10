<!-- Modal Ringkasan Pemeriksaan Pasien -->
<div id="modal-ringkasan-pemeriksaan" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header (Tanpa border bawah) -->
            <div class="flex items-start justify-between p-6 shrink-0 pb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">
                        Ringkasan pemeriksaan pasien
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Detail rekam medis dan status resep pada sesi praktik hari ini.
                    </p>
                </div>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-ringkasan-pemeriksaan">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Scrollable Area) -->
            <div class="px-6 pb-6 overflow-y-auto space-y-6">

                <!-- Box Profil & Waktu -->
                <div class="flex flex-col md:flex-row md:items-start justify-between p-4 bg-gray-50 border border-gray-200 rounded gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <h4 class="text-base font-bold text-gray-900">Sinta Maharani</h4>
                            <span class="text-sm text-gray-500">&bull; NIM 2341720011 &bull; Mahasiswa</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-1">
                            Waktu periksa: <span class="font-bold text-gray-900">Kamis, 24 Sep 2026 &bull; 09.00 WIB</span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Dokter pemeriksa: <span class="font-bold text-gray-900">dr. Aditya Pratama</span> (Poli Umum)
                        </p>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Selesai</span>
                    </div>
                </div>

                <!-- Section: Catatan Tanda Vital -->
                <div>
                    <h5 class="text-sm font-bold text-gray-900 mb-3">Catatan tanda vital</h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Tensi -->
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                            <span class="block text-xs text-gray-500 mb-1">Tekanan darah</span>
                            <span class="block text-base font-bold text-gray-900">115/75 mmHg</span>
                        </div>
                        <!-- Suhu -->
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                            <span class="block text-xs text-gray-500 mb-1">Suhu tubuh</span>
                            <span class="block text-base font-bold text-gray-900">36.7 &deg;C</span>
                        </div>
                        <!-- BB -->
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                            <span class="block text-xs text-gray-500 mb-1">Berat badan</span>
                            <span class="block text-base font-bold text-gray-900">50 kg</span>
                        </div>
                    </div>
                </div>

                <!-- Section: Hasil Pemeriksaan & Diagnosis (Tabel-Grid Kustom) -->
                <div>
                    <h5 class="text-sm font-bold text-gray-900 mb-3">Hasil pemeriksaan &amp; diagnosis</h5>
                    <div class="border border-gray-200 rounded overflow-hidden">
                        <div class="flex flex-col text-sm">

                            <!-- Keluhan awal -->
                            <div class="flex flex-col md:flex-row p-4 border-b border-gray-100">
                                <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Keluhan awal</div>
                                <div class="md:w-2/3 font-bold text-gray-900">Batuk dan pilek</div>
                            </div>

                            <!-- Anamnesis fisik -->
                            <div class="flex flex-col md:flex-row p-4 border-b border-gray-100">
                                <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Anamnesis fisik</div>
                                <div class="md:w-2/3 text-gray-700">Batuk berdahak ringan sejak 2 hari lalu, tanpa disertai demam.</div>
                            </div>

                            <!-- Diagnosis medis -->
                            <div class="flex flex-col md:flex-row p-4 border-b border-gray-100">
                                <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Diagnosis medis</div>
                                <div class="md:w-2/3 font-bold text-primary-600">Common Cold (Rhinopharyngitis Akut)</div>
                            </div>

                            <!-- Saran medis -->
                            <div class="flex flex-col md:flex-row p-4">
                                <div class="md:w-1/3 text-gray-500 mb-1 md:mb-0">Saran medis</div>
                                <div class="md:w-2/3 text-gray-700 leading-relaxed">Istirahat cukup, perbanyak minum air hangat, dan konsumsi obat sesuai anjuran.</div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Section: Resep Obat Diberikan -->
                <div class="p-5 bg-page border border-primary-100 rounded">

                    <div class="flex items-center justify-between mb-4">
                        <h5 class="text-base font-bold text-primary-600">Resep obat yang diberikan</h5>
                        <span class="inline-flex items-center px-2 py-1 mb-1 text-fg-success-strong text-xs font-medium rounded bg-success-soft">Diproses Oleh Admin</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Obat 1 -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-3.5 bg-white border border-gray-100 rounded shadow-sm gap-2">
                            <span class="text-sm font-bold text-gray-900">1. Ambroxol 30 mg</span>
                            <span class="text-sm text-gray-600">3x1 tablet sesudah makan</span>
                        </div>

                        <!-- Obat 2 -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-3.5 bg-white border border-gray-100 rounded shadow-sm gap-2">
                            <span class="text-sm font-bold text-gray-900">2. Cetirizine 10 mg</span>
                            <span class="text-sm text-gray-600">1x1 tablet malam hari</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end p-6 border-t border-gray-100 shrink-0">
                <button type="button" data-modal-hide="modal-ringkasan-pemeriksaan" class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>