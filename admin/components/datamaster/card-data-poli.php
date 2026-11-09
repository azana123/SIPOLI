<div id="card-daftar-poli" class="target-card my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
    <div class="flex justify-between items-center mb-4">
        <h4 class="text-lg font-bold">Daftar Poli</h4>

        <button id="add-data-poli" data-modal-target="modal-form-poli" data-modal-toggle="modal-form-poli" class="hidden md:inline-flex items-center text-white bg-primary hover:bg-primary-700 box-border border border-transparent focus:ring-4 focus:ring-primary-400 shadow-xs font-medium leading-5 rounded text-xs px-2.5 py-1 focus:outline-none mr-3 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#fff" class="mr-1">
                <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
            </svg>
            Tambah Data Poli
        </button>
    </div>

    <!-- Filter -->
    <form method="GET" action="datamaster.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 my-6">
        <input type="hidden" name="tab" value="poli" />

        <div class="flex flex-col">
            <label for="search-poli" class="text-sm text-primary-600">Pencarian</label>
            <div class="relative mt-1">
                <input type="search" id="search-poli" name="q" value="<?= e($search) ?>"
                    class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Cari nama poli..." />
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex flex-col">
            <label for="reservasi-filter" class="text-sm text-primary-600">Tipe Layanan</label>
            <select id="reservasi-filter" name="reservasi" onchange="this.form.submit()" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1">
                <option value="">Semua Tipe</option>
                <option value="ya" <?= $reserv === 'ya' ? 'selected' : '' ?>>Wajib Reservasi</option>
                <option value="tidak" <?= $reserv === 'tidak' ? 'selected' : '' ?>>Walk-in (Tanpa Reservasi)</option>
            </select>
        </div>

        <div class="flex flex-row items-end gap-3 md:col-span-2">
            <a href="datamaster.php?tab=poli" class="text-primary text-sm font-medium py-2.5 hover:underline">Reset Filter</a>
        </div>
    </form>

    <div class="relative overflow-x-auto overflow-y-auto h-1/2">
        <table class="w-full text-sm text-left rtl:text-right text-body table-auto">
            <thead class="text-xs text-heading uppercase bg-neutral-primary border-b border-primary-400-05">
                <tr>
                    <th scope="col" class="p-3">Nama Poli</th>
                    <th scope="col" class="p-3">Sistem Layanan</th>
                    <th scope="col" class="p-3">Kuota Harian Default</th>
                    <th scope="col" class="p-3">Durasi Sesi</th>
                    <th scope="col" class="p-3">Tenaga Layanan Aktif</th>
                    <th scope="col" class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-primary-600">
                <?php if (empty($rows)): ?>
                    <tr class="bg-neutral-primary border-b border-primary-400-05">
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">Tidak ada data poli yang sesuai filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr class="bg-neutral-primary border-b border-primary-400-05">
                            <th scope="row" class="px-3 py-4 font-bold text-base text-heading whitespace-nowrap">
                                <?= e($row['nama']) ?>
                            </th>
                            <td class="px-3 py-4 align-top">
                                <?php if ($row['butuh_reservasi']): ?>
                                    <span class="inline-flex items-center px-2 py-1 text-primary text-xs font-bold rounded bg-brand-softer">Reservasi</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 text-gray-700 text-xs font-bold rounded bg-gray-100">Walk-in</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-4 align-top text-sm">
                                <?= $row['kuota_harian'] !== null ? e($row['kuota_harian']) . ' Pasien' : 'Tidak dibatasi' ?>
                            </td>
                            <td class="px-3 py-4 align-top text-sm">
                                <?= $row['durasi_menit'] !== null ? e($row['durasi_menit']) . ' Menit' : '-' ?>
                            </td>
                            <td class="px-3 py-4 align-top text-sm">
                                <?= (int) $row['jumlah_dokter'] ?> Orang
                            </td>
                            <td class="px-3 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <a href="?tab=tenaga-layanan&poli_id=<?= (int) $row['id'] ?>" class="font-bold hover:underline text-primary">Lihat Dokter</a>
                                    <button type="button" class="inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="group inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all ">
                                        <i class="far fa-trash-alt text-primary text-sm group-hover:text-white transition-colors"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>