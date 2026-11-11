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
                    <div id="form-resep-obat" class="p-5 bg-gray-50/50 border border-gray-200 rounded">
                        <h5 class="text-base font-bold text-gray-900">Resep obat (opsional)</h5>
                        <p class="text-sm text-gray-500 mb-4">Resep yang ditambahkan akan langsung diteruskan ke admin untuk pelayanan obat.</p>
                        <div class="card-obat mb-4">
                            <div class="p-5 bg-page border border-primary-100 rounded space-y-4">
                                <!-- Header Card Obat -->
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-bold text-gray-900">Obat 1</h4>
                                    <span class="text-xs text-gray-500">Bentuk, satuan &amp; stok terisi dari katalog &bull; hanya baca</span>
                                </div>

                                <!-- Baris 1: Nama Obat (Search & Dropdown) + Readonly Stats -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <!-- Select Nama Obat -->
                                    <div class="md:col-span-6">
                                        <label for="nama_obat_1" class="block mb-2 text-sm font-medium text-gray-700">Nama obat *</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 inset-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                                </svg>
                                            </div>
                                            <select id="nama_obat_1" name="nama_obat[]" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 pe-10 appearance-none placeholder-gray-400" required>
                                                <option value="" disabled selected>Cari kode atau nama obat...</option>
                                                <option value="OBT-001">Paracetamol 500 mg (OBT-001)</option>
                                                <option value="OBT-002">Ambroxol 30 mg (OBT-002)</option>
                                                <option value="OBT-003">Cetirizine 10 mg (OBT-003)</option>
                                            </select>
                                            <div class="absolute inset-y-0 inset-e-0 flex items-center pe-3 pointer-events-none text-gray-400">
                                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Readonly: Bentuk -->
                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Bentuk</label>
                                        <div class="p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-500 rounded">
                                            &mdash;
                                        </div>
                                    </div>

                                    <!-- Readonly: Satuan -->
                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Satuan</label>
                                        <div class="p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-500 rounded">
                                            &mdash;
                                        </div>
                                    </div>

                                    <!-- Readonly: Stok tersedia -->
                                    <div class="md:col-span-2">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Stok tersedia</label>
                                        <div class="p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-500 rounded">
                                            &mdash;
                                        </div>
                                    </div>
                                </div>

                                <!-- Baris 2: Jumlah, Dosis, Aturan pakai -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="jumlah_1" class="block mb-2 text-sm font-medium text-gray-700">Jumlah *</label>
                                        <input type="text" id="jumlah_1" name="jumlah[]" placeholder="Masukkan jumlah" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" required>
                                    </div>
                                    <div>
                                        <label for="dosis_1" class="block mb-2 text-sm font-medium text-gray-700">Dosis *</label>
                                        <input type="text" id="dosis_1" name="dosis[]" placeholder="Tuliskan dosis obat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" required>
                                    </div>
                                    <div>
                                        <label for="aturan_1" class="block mb-2 text-sm font-medium text-gray-700">Aturan pakai *</label>
                                        <input type="text" id="aturan_1" name="aturan[]" placeholder="Tuliskan aturan penggunaan obat" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" required>
                                    </div>
                                </div>

                                <!-- Baris 3: Catatan Item -->
                                <div>
                                    <label for="catatan_1" class="block mb-2 text-sm font-medium text-gray-700">Catatan item (opsional)</label>
                                    <input type="text" id="catatan_1" name="catatan[]" placeholder="Tambahkan catatan khusus untuk obat ini..." class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div onclick="addResepObat()" class="border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3.5 py-1.5 text-xs font-semibold transition-colors shadow-sm cursor-pointer inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0f7485" class="mr-1">
                            <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                        </svg>
                        Tambah Resep
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