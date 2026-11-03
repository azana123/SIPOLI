<div id="card-daftar-pasien" class="target-card my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
    <h4 class="text-lg font-bold">Daftar Pasien</h4>
    <!-- Filter -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-6">
        <div class="flex flex-col">
            <label for="search" class="text-sm text-primary-600">Pencarian</label>
            <div class="relative">
                <input type="search" id="search" class="block w-full md: p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500" placeholder="Cari data pasien..." required />
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <label for="civitas-filter" class="text-sm text-primary-600">Civitas</label>
            <select id="civitas-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1">
                <option selected>Semua Civitas</option>
                <option value="laptop">Laptop</option>
                <option value="desktop">Desktop</option>
            </select>
        </div>
        <div class="flex flex-col">
            <label for="status-filter" class="text-sm text-primary-600">Status</label>
            <select id="status-filter" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1">
                <option selected>Semua Status</option>
                <option value="laptop">Laptop</option>
                <option value="desktop">Desktop</option>
            </select>
        </div>
        <div class="flex flex-row gap-2">
            <a href="" class="text-primary text-sm font-medium py-2.5 self-end hover:underline">Reset Filter</a>
        </div>
    </div>
    <div class="relative overflow-x-auto overflow-y-auto h-1/2">
        <table class="w-full text-sm text-left rtl:text-right text-body table-auto">
            <thead class="text-xs text-heading uppercase bg-neutral-primary border-b border-primary-400-05">
                <tr>
                    <th scope="col" class="p-3">Identitas pasien</th>
                    <th scope="col" class="p-3">Kontak</th>
                    <th scope="col" class="p-3">Tanggal lahir</th>
                    <th scope="col" class="p-3">Jenis kelamin</th>
                    <th scope="col" class="p-3">Status</th>
                    <th scope="col" class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-primary-600">

                <!-- Baris 2: Raka Aditya -->
                <tr class="bg-neutral-primary border-b border-primary-400-05">
                    <th scope="row" class="px-3 py-4 font-medium text-heading whitespace-nowrap">
                        <div class="text-base font-bold">Raka Aditya</div>
                        <div class="text-xs font-normal text-gray-500 mt-1">NIM · 2341720102</div>
                        <div class="text-xs font-normal text-gray-500">Mahasiswa</div>
                    </th>
                    <td class="px-3 py-4 align-top">
                        <div class="text-sm font-medium text-heading">raka.a@example.com</div>
                        <div class="text-sm text-gray-500 mt-1">08•• •••• 0102</div>
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        08 Jun 2004
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        Laki-laki
                    </td>
                    <td class="px-3 py-4 align-top">
                        <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-bold rounded bg-success-soft">Aktif</span>
                    </td>
                    <td class="px-3 py-4 align-top">
                        <div class="flex items-center gap-3">
                            <a href="#" class="font-bold hover:underline text-primary">Lihat</a>
                            <button type="button" class="inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Baris 3: Nadia Kusuma -->
                <tr class="bg-neutral-primary border-b border-primary-400-05">
                    <th scope="row" class="px-3 py-4 font-medium text-heading whitespace-nowrap">
                        <div class="text-base font-bold">Nadia Kusuma</div>
                        <div class="text-xs font-normal text-gray-500 mt-1">NIM · 2341720103</div>
                        <div class="text-xs font-normal text-gray-500">Mahasiswa</div>
                    </th>
                    <td class="px-3 py-4 align-top">
                        <div class="text-sm font-medium text-heading">nadia.k@example.com</div>
                        <div class="text-sm text-gray-500 mt-1">08•• •••• 0103</div>
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        21 Mar 2005
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        Perempuan
                    </td>
                    <td class="px-3 py-4 align-top">
                        <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-bold rounded bg-success-soft">Aktif</span>
                    </td>
                    <td class="px-3 py-4 align-top">
                        <div class="flex items-center gap-3">
                            <a href="#" class="font-bold hover:underline text-primary">Lihat</a>
                            <button type="button" class="inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Baris 4: Fajar Ramadhan -->
                <tr class="bg-neutral-primary border-b border-primary-400-05">
                    <th scope="row" class="px-3 py-4 font-medium text-heading whitespace-nowrap">
                        <div class="text-5e font-bold">Fajar Ramadhan</div>
                        <div class="font-normal text-gray-500 mt-1">NIM · 2341720104</div>
                        <div class="text font-normal text-gray-500">Mahasiswa</div>
                    </th>
                    <td class="py-6">
                        <div class="text-sm font-medium text-heading">fajar.r@example.com</div>
                        <div class="text-sm text-gray-500 mt-1">08•• •••• 0104</div>
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        17 Nov 2004
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        Laki-laki
                    </td>
                    <td class="px-3 py-4 align-top">
                        <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-bold rounded bg-success-soft">Aktif</span>
                    </td>
                    <td class="px-3 py-4 align-top">
                        <div class="flex items-center gap-3">
                            <a href="#" class="font-bold hover:underline text-primary">Lihat</a>
                            <button type="button" class="inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Baris 5: Dina Safitri -->
                <tr class="bg-neutral-primary border-b border-primary-400-05">
                    <th scope="row" class="px-3 py-4 font-medium text-heading whitespace-nowrap">
                        <div class="text-base font-bold">Dina Safitri</div>
                        <div class="text-xs font-normal text-gray-500 mt-1">NIM · 2341720105</div>
                        <div class="text-xs font-normal text-gray-500">Mahasiswa</div>
                    </th>
                    <td class="px-3 py-4 align-top">
                        <div class="text-sm font-medium text-heading">dina.s@example.com</div>
                        <div class="text-sm text-gray-500 mt-1">08•• •••• 0105</div>
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        03 Jul 2005
                    </td>
                    <td class="px-3 py-4 align-top text-sm">
                        Perempuan
                    </td>
                    <td class="px-3 py-4 align-top">
                        <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-bold rounded bg-success-soft">Aktif</span>
                    </td>
                    <td class="px-3 py-4 align-top">
                        <div class="flex items-center gap-3">
                            <a href="#" class="font-bold hover:underline text-primary">Lihat</a>
                            <button type="button" class="inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>