<!-- Modal Tambah Pasien Offline -->
<div id="modal-tambah-pasien" data-modal-backdrop="true" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tambah Reservasi Pasien Offline
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="modal-tambah-pasien">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form class="p-4 md:p-5" action="#" method="POST">
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="no_rm" class="block mb-2 text-sm font-medium text-gray-900">No. Rekam Medis / NIK</label>
                        <input type="text" name="no_rm" id="no_rm" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan NIK atau No RM" required="">
                    </div>
                    <div class="col-span-2">
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Nama Pasien</label>
                        <input type="text" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan nama lengkap" required="">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="poli" class="block mb-2 text-sm font-medium text-gray-900">Poli Tujuan</label>
                        <select id="poli" name="poli" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <option value="" selected disabled>Pilih Poli</option>
                            <option value="umum">Poli Umum</option>
                            <option value="gigi">Poli Gigi</option>
                            <option value="kia">Poli KIA</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="keluhan" class="block mb-2 text-sm font-medium text-gray-900">Keluhan Awal</label>
                        <input type="text" name="keluhan" id="keluhan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Contoh: Demam" required="">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="text-white inline-flex items-center bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>