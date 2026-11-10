<!-- Modal Informasikan Perubahan Jadwal -->
<div id="modal-informasi-perubahan" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 border-b border-gray-200 shrink-0">
                <div>
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900">
                        Informasikan perubahan jadwal
                    </h3>
                    <p class="text-base text-gray-500 mt-1.5">
                        Sampaikan perubahan praktik kepada admin poliklinik.
                    </p>
                </div>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors mt-1" data-modal-hide="modal-informasi-perubahan">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form & Scrollable Area) -->
            <form action="#" method="POST" class="overflow-y-auto">
                <div class="p-6 space-y-6">
                    
                    <!-- Alert Atas: Perubahan perlu ditindaklanjuti admin -->
                    <div class="p-4 bg-page rounded">
                        <h4 class="text-base font-bold text-primary mb-1">Perubahan perlu ditindaklanjuti admin</h4>
                        <p class="text-sm text-gray-600">Mengirim form ini belum mengubah jadwal atau mengirim notifikasi ke pasien.</p>
                    </div>

                    <!-- Jadwal praktik yang terdampak -->
                    <div>
                        <label for="jadwal_terdampak" class="block mb-2 text-sm font-bold text-gray-900">Jadwal praktik yang terdampak *</label>
                        <select id="jadwal_terdampak" name="jadwal_terdampak" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-3" required>
                            <option value="" selected>Kamis, 24 Sep 2026 &bull; Poli Umum &bull; 09.00&ndash;12.00 WIB</option>
                            <!-- Opsi lain bisa ditambahkan di sini -->
                        </select>
                    </div>

                    <!-- Grid 2 Kolom: Jenis Perubahan & Dokter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_perubahan" class="block mb-2 text-sm font-bold text-gray-900">Jenis perubahan *</label>
                            <select id="jenis_perubahan" name="jenis_perubahan" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-3" required>
                                <option value="Perubahan jam praktik" selected>Perubahan jam praktik</option>
                                <option value="Batal praktik">Batal praktik</option>
                                <option value="Ganti dokter">Ganti dokter</option>
                            </select>
                        </div>
                        <div>
                            <label for="dokter" class="block mb-2 text-sm font-bold text-gray-900">Dokter</label>
                            <input type="text" id="dokter" name="dokter" value="dr. Aditya Pratama" readonly class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-3 cursor-default">
                        </div>
                    </div>

                    <!-- Box Group: Usulan waktu pengganti -->
                    <div class="p-5 border border-gray-200 bg-gray-50/50 rounded space-y-4">
                        <div>
                            <h5 class="text-base font-bold text-gray-900">Usulan waktu pengganti (opsional)</h5>
                            <p class="text-sm text-gray-500 mt-1">Admin akan memeriksa ketersediaan sebelum memperbarui jadwal.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Tanggal (Lebar lebih besar) -->
                            <div class="md:col-span-6">
                                <label for="usulan_tanggal" class="block mb-2 text-sm text-gray-600">Tanggal</label>
                                <input type="text" id="usulan_tanggal" name="usulan_tanggal" value="24 September 2026" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-2.5">
                            </div>
                            <!-- Jam Mulai -->
                            <div class="md:col-span-3">
                                <label for="usulan_mulai" class="block mb-2 text-sm text-gray-600">Mulai</label>
                                <input type="time" id="usulan_mulai" name="usulan_mulai" value="13:00" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-2.5">
                            </div>
                            <!-- Jam Selesai -->
                            <div class="md:col-span-3">
                                <label for="usulan_selesai" class="block mb-2 text-sm text-gray-600">Selesai</label>
                                <input type="time" id="usulan_selesai" name="usulan_selesai" value="16:00" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-2.5">
                            </div>
                        </div>
                    </div>

                    <!-- Textarea Alasan Perubahan -->
                    <div>
                        <label for="alasan" class="block mb-2 text-sm font-bold text-gray-900">Alasan perubahan *</label>
                        <textarea id="alasan" name="alasan" rows="3" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4" required>Ada keperluan dinas pada sisa jam praktik pagi. Saya mengusulkan pemindahan jam praktik ke pukul 13.00-16.00 WIB.</textarea>
                    </div>

                    <!-- Alert Bawah: Tindak lanjut oleh admin -->
                    <div class="p-4 bg-page rounded">
                        <h4 class="text-sm font-bold text-primary mb-1">Tindak lanjut oleh admin</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Admin meninjau perubahan, mengatur ulang pasien yang terdampak, dan menyampaikan informasi jadwal kepada pasien.</p>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between p-6 border-t border-gray-200 shrink-0">
                    <span class="text-sm text-gray-500 font-bold">* Wajib diisi</span>
                    <div class="flex items-center gap-3">
                        <button type="button" data-modal-hide="modal-informasi-perubahan" class="px-4 md:px-6 py-2.5 text-xs md:text-base font-bold text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                            Batal
                        </button>
                        <!-- Tombol utama dikirim dengan warna teal/hijau tebal -->
                        <button type="submit" class="px-4 md:px-6 py-2.5 text-xs md:text-base font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors">
                            Kirim ke Admin
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>