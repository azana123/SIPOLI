<!-- Modal Mulai Pemeriksaan Pasien -->
<div id="modal-pemeriksaan" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 shrink-0 pb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">
                        Mulai pemeriksaan pasien
                    </h3>
                    <p class="text-base text-gray-500 mt-1">
                        Catat tanda vital, hasil pemeriksaan, diagnosis, dan resep obat bila diperlukan.
                    </p>
                </div>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-pemeriksaan">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Scrollable Form) -->
            <form action="#" method="POST" class="overflow-y-auto">
                <div class="px-6 pb-6 space-y-6">

                    <!-- Box Info Pasien -->
                    <div class="p-4 bg-page border border-primary-100 rounded flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <h4 class="text-base font-bold text-primary">Ahmad Fauzi</h4>
                                <span class="text-sm text-gray-500">&bull; NIM 2441720057 &bull; Mahasiswa</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-0.5">
                                Jadwal: <span class="font-bold text-gray-900">10.00 WIB</span> | Poli Umum &bull; Ruang pemeriksaan 01
                            </p>
                            <p class="text-sm text-gray-600">
                                Keluhan awal: <span class="font-bold text-primary">Sakit kepala</span>
                            </p>
                        </div>
                        <!-- Badge Menunggu -->
                        <div class="shrink-0">
                            <span class="inline-flex items-center px-2 py-1 mb-1 text-yellow-900 text-xs font-medium rounded bg-yellow-100">Menunggu</span>
                        </div>
                    </div>

                    <!-- Tanda Vital Pasien (3 Kolom) -->
                    <div>
                        <h5 class="text-base font-bold text-gray-900 mb-3">Tanda vital pasien</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="tensi" class="block mb-2 text-sm text-gray-600">Tekanan darah (mmHg)</label>
                                <input type="text" id="tensi" name="tensi" placeholder="Contoh: 120/80" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                            </div>
                            <div>
                                <label for="suhu" class="block mb-2 text-sm text-gray-600">Suhu tubuh (&deg;C)</label>
                                <input type="text" id="suhu" name="suhu" placeholder="Contoh: 36.8" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                            </div>
                            <div>
                                <label for="bb" class="block mb-2 text-sm text-gray-600">Berat badan (kg)</label>
                                <input type="text" id="bb" name="bb" placeholder="Contoh: 60" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <!-- Hasil Pemeriksaan & Anamnesis -->
                    <div>
                        <label for="hasil_pemeriksaan" class="block mb-2 text-base font-bold text-gray-900">Hasil pemeriksaan &amp; anamnesis *</label>
                        <textarea id="hasil_pemeriksaan" name="hasil_pemeriksaan" rows="4" placeholder="Tuliskan detail keluhan pasien, durasi gejala, dan hasil pemeriksaan fisik..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400" required></textarea>
                    </div>

                    <!-- Diagnosis Medis -->
                    <div>
                        <label for="diagnosis" class="block mb-2 text-base font-bold text-gray-900">Diagnosis medis *</label>
                        <input type="text" id="diagnosis" name="diagnosis" placeholder="Masukkan diagnosis utama (contoh: Tension Headache / Cephalgia)" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400" required>
                    </div>

                    <!-- Grup Resep Obat -->
                    <div class="p-5 bg-gray-50/50 border border-gray-200 rounded">
                        <h5 class="text-base font-bold text-gray-900">Resep obat (opsional)</h5>
                        <p class="text-sm text-gray-500 mb-4">Resep yang ditambahkan akan langsung diteruskan ke admin untuk pelayanan obat.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="nama_obat" class="block mb-2 text-sm text-gray-600">Nama obat &amp; kekuatan</label>
                                <input type="text" id="nama_obat" name="nama_obat" placeholder="Contoh: Paracetamol 500 mg" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                            </div>
                            <div>
                                <label for="dosis_aturan" class="block mb-2 text-sm text-gray-600">Dosis &amp; aturan pakai</label>
                                <input type="text" id="dosis_aturan" name="dosis_aturan" placeholder="Contoh: 3&times;1 sehari sesudah makan" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <!-- Box Info Pembaruan Status -->
                    <div class="p-4 bg-page rounded">
                        <h4 class="text-sm font-bold text-primary mb-0.5">Pembaruan status otomatis</h4>
                        <p class="text-sm text-gray-600">Menyimpan draf akan mengubah status pasien menjadi Diperiksa.</p>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between p-6 border-t border-gray-100 shrink-0 bg-white">
                    <span class="text-sm text-gray-500 font-bold">* Wajib diisi</span>
                    <div class="flex items-center gap-3">
                        <!-- Tombol Batal Outline (Abu-abu) -->
                        <button type="button" data-modal-hide="modal-pemeriksaan" class="px-6 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <!-- Tombol Simpan Draf (Latar Terang, Teks Primer, Border Primer) -->
                        <button type="submit" name="action" value="draft" class="px-6 py-2.5 text-sm font-bold text-primary-700 bg-primary-50 border border-primary-600 hover:bg-primary-100 focus:ring-4 focus:outline-none focus:ring-primary-200 rounded transition-colors cursor-pointer">
                            Simpan Draf
                        </button>
                        <!-- Tombol Selesaikan Solid -->
                        <button type="submit" name="action" value="selesai" class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors cursor-pointer">
                            Selesaikan
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>