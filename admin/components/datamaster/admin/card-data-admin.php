<div id="card-daftar-admin" class="target-card my-3 bg-neutral-primary-soft block p-6 border border-primary-100 rounded-base shadow-xs">
    <div class="flex justify-between items-center mb-4">
        <h4 class="text-lg font-bold">Daftar Admin</h4>

        <?php if (!empty($isSuperAdmin)): ?>
            <button id="add-data-admin" data-modal-target="modal-form-admin" data-modal-toggle="modal-form-admin" class="hidden md:inline-flex items-center text-white bg-primary hover:bg-primary-700 box-border border border-transparent focus:ring-4 focus:ring-primary-400 shadow-xs font-medium leading-5 rounded text-xs px-2.5 py-1 focus:outline-none mr-3 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#fff" class="mr-1">
                    <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                </svg>
                Tambah Data Admin
            </button>
        <?php endif; ?>
    </div>

    <!-- Filter -->
    <form method="GET" action="datamaster.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 my-6">
        <input type="hidden" name="tab" value="admin" />

        <div class="flex flex-col">
            <label for="search-admin" class="text-sm text-primary-600">Pencarian</label>
            <div class="relative mt-1">
                <input type="search" id="search-admin" name="q" value="<?= e($search) ?>"
                    class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Cari nama admin, NIM/NIP, username..." />
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex flex-col">
            <label for="tipe-admin-filter" class="text-sm text-primary-600">Peran Admin</label>
            <select id="tipe-admin-filter" name="tipe_admin" onchange="this.form.submit()" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1 bg-white">
                <option value="">Semua Admin</option>
                <option value="super" <?= $tipeAdm === 'super' ? 'selected' : '' ?>>Super Admin</option>
                <option value="regular" <?= $tipeAdm === 'regular' ? 'selected' : '' ?>>Regular Admin</option>
            </select>
        </div>

        <div class="flex flex-col">
            <label for="status-admin-filter" class="text-sm text-primary-600">Status</label>
            <select id="status-admin-filter" name="status" onchange="this.form.submit()" class="border border-primary-400-05 text-heading text-sm rounded-base flex-1 mt-1 bg-white">
                <option value="">Semua Status</option>
                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="non-aktif" <?= $status === 'non-aktif' ? 'selected' : '' ?>>Non-Aktif</option>
            </select>
        </div>

        <div class="flex flex-row items-end gap-3">
            <a href="datamaster.php?tab=admin" class="text-primary text-sm font-medium py-2.5 hover:underline">Reset Filter</a>
        </div>
    </form>

    <div class="relative overflow-x-auto overflow-y-auto h-1/2">
        <table class="w-full text-sm text-left rtl:text-right text-body table-auto">
            <thead class="text-xs text-heading uppercase bg-neutral-primary border-b border-primary-400-05">
                <tr>
                    <th scope="col" class="p-3">Identitas Admin</th>
                    <th scope="col" class="p-3">Kontak / Akun</th>
                    <th scope="col" class="p-3">Peran</th>
                    <th scope="col" class="p-3">Jenis Kelamin</th>
                    <th scope="col" class="p-3">Status</th>
                    <th scope="col" class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-primary-600">
                <?php if (empty($rows)): ?>
                    <tr class="bg-neutral-primary border-b border-primary-400-05">
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">Tidak ada data admin yang sesuai filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $gender = match ($row['jenis_kelamin'] ?? null) {
                            'L'     => 'Laki-laki',
                            'P'     => 'Perempuan',
                            default => '-',
                        };
                        $baseQ = http_build_query([
                            'tab'        => 'admin',
                            'q'          => $search,
                            'tipe_admin' => $tipeAdm,
                            'status'     => $status,
                        ]);
                        ?>
                        <tr class="<?= $row['status_aktif'] ? 'bg-neutral-primary' : 'bg-gray-50 opacity-75' ?> border-b border-primary-400-05">
                            <th scope="row" class="px-3 py-4 font-medium text-heading whitespace-nowrap">
                                <div class="text-base font-bold"><?= e($row['nama']) ?></div>
                                <div class="text-xs font-normal text-gray-500 mt-1">NIP/NIM · <?= e($row['nim_nip'] ?? '-') ?></div>
                            </th>
                            <td class="px-3 py-4 align-top">
                                <div class="text-sm font-medium text-heading"><?= e($row['username']) ?></div>
                                <div class="text-sm text-gray-500 mt-1"><?= e($row['no_hp'] ?? '-') ?></div>
                            </td>
                            <td class="px-3 py-4 align-top text-sm">
                                <?= $row['is_super'] ? 'Super Admin' : 'Regular Admin' ?>
                            </td>
                            <td class="px-3 py-4 align-top text-sm">
                                <?= e($gender) ?>
                            </td>
                            <td class="px-3 py-4 align-top">
                                <?php if ($row['status_aktif']): ?>
                                    <span class="inline-flex items-center px-2 py-1 text-fg-success-strong text-xs font-bold rounded bg-success-soft">Aktif</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 text-red-700 text-xs font-bold rounded bg-red-100">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <a href="datamaster.php?<?= $baseQ ?>&detail_id=<?= (int) $row['id'] ?>" class="font-bold hover:underline text-primary">Lihat</a>
                                    <?php if (!empty($isSuperAdmin)): ?>
                                        <a href="datamaster.php?<?= $baseQ ?>&edit_id=<?= (int) $row['id'] ?>" title="Edit Admin" class="inline-flex items-center p-1.5 text-primary rounded bg-brand-softer hover:bg-primary hover:text-white transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </a>
                                        <?php if ((int) $row['id'] !== $currentAdminId): ?>
                                            <form method="POST" action="datamaster.php" class="inline" onsubmit="return confirm('<?= $row['status_aktif'] ? 'Nonaktifkan akun admin ini?' : 'Aktifkan kembali akun admin ini?' ?>')">
                                                <input type="hidden" name="action" value="toggle_active">
                                                <input type="hidden" name="tab" value="admin">
                                                <input type="hidden" name="entity" value="admin">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <input type="hidden" name="new_state" value="<?= $row['status_aktif'] ? '0' : '1' ?>">
                                                <button type="submit" title="<?= $row['status_aktif'] ? 'Nonaktifkan Admin' : 'Aktifkan Admin' ?>" class="group inline-flex items-center p-1.5 rounded transition-all cursor-pointer <?= $row['status_aktif'] ? 'text-primary bg-brand-softer hover:bg-red-600 hover:text-white' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white' ?>">
                                                    <i class="far <?= $row['status_aktif'] ? 'fa-trash-alt' : 'fa-check-circle' ?> text-sm transition-colors"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>