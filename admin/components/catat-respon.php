<!-- Modal Catat Respons Konfirmasi -->
<div id="modal-catat-respons" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">

            <!-- Modal Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-xl font-bold text-primary-700">
                    Catat Respons Konfirmasi
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-catat-respons">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form action="#" method="POST">
                <div class="p-5 space-y-5">

                    <!-- Box Informasi Pasien -->
                    <div class="p-4 bg-[var(--color-page)] border border-gray-100 rounded flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div>
                            <h4 class="text-base font-bold text-gray-900">Intan Permata</h4>
                            <p class="text-sm text-gray-500 mt-0.5">NIM 2341720107 &bull; Mahasiswa</p>
                        </div>
                        <div class="md:text-right">
                            <h4 class="text-sm font-bold text-primary-700">Layanan: Konseling</h4>
                            <p class="text-sm text-gray-500 mt-0.5">25 Sep 2026 &bull; 09.00&ndash;09.30 WIB</p>
                        </div>
                    </div>

                    <!-- Pilihan Hasil Konfirmasi -->
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-900">Hasil Konfirmasi Pasien</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Opsi 1: Konfirmasi Hadir (Selected) -->
                            <label class="flex items-center px-4 py-3 border border-primary-500 bg-primary-50 rounded cursor-pointer transition-colors">
                                <input type="radio" name="hasil_konfirmasi" value="hadir" checked class="w-4 h-4 text-primary-600 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                                <span class="ml-2.5 text-sm font-bold text-primary-700">Konfirmasi Hadir</span>
                            </label>

                            <!-- Opsi 2: Tidak Bisa Hadir -->
                            <label class="flex items-center px-4 py-3 border border-gray-300 bg-white rounded cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="hasil_konfirmasi" value="tidak_hadir" class="w-4 h-4 text-primary-600 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                                <span class="ml-2.5 text-sm font-medium text-gray-600">Tidak Bisa Hadir</span>
                            </label>

                            <!-- Opsi 3: Belum Ada Balasan -->
                            <label class="flex items-center px-4 py-3 border border-gray-300 bg-white rounded cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="hasil_konfirmasi" value="belum_dibalas" class="w-4 h-4 text-primary-600 bg-white border-gray-300 focus:ring-primary-500 focus:ring-2">
                                <span class="ml-2.5 text-sm font-medium text-gray-600">Belum Ada Balasan</span>
                            </label>
                        </div>
                    </div>

                    <!-- Dropdown Media Konfirmasi -->
                    <div>
                        <label for="media_konfirmasi" class="block mb-2 text-sm font-bold text-gray-900">Media Konfirmasi</label>
                        <select id="media_konfirmasi" name="media_konfirmasi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="Notifikasi" selected>Notifikasi</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Panggilan Suara">Panggilan Suara</option>
                        </select>
                    </div>

                    <!-- Textarea Catatan -->
                    <div>
                        <label for="catatan_respons" class="block mb-2 text-sm font-bold text-gray-900">Catatan Respons (Opsional)</label>
                        <textarea id="catatan_respons" name="catatan_respons" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 placeholder-gray-400" placeholder="Contoh: Pasien mengonfirmasi bersedia hadir esok hari tepat waktu..."></textarea>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                    <button type="button" data-modal-hide="modal-catat-respons" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                        Simpan Respons
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>