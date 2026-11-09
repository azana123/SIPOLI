<?php
$isStokOpen   = !empty($stokObat);
$sObatId      = (int) ($stokObat['id'] ?? 0);
$sNamaObat    = $stokObat['nama_obat'] ?? '-';
$sKodeObat    = $stokObat['kode_obat'] ?? '-';
$sBentuk      = ucfirst($stokObat['bentuk'] ?? '-');
$sSatuan      = ucfirst($stokObat['satuan'] ?? 'Unit');
$sStokSaatIni = (int) ($stokObat['stok'] ?? 0);
?>
<div id="modal-atur-stok" tabindex="-1" aria-hidden="<?= $isStokOpen ? 'false' : 'true' ?>" class="<?= $isStokOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <div class="flex items-start justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Atur stok</h3>
                    <p class="text-sm text-gray-500 mt-1">Sesuaikan jumlah stok obat dan catat alasan perubahan.</p>
                </div>
                <a href="obat.php" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($stokObat): ?>
                <form action="obat.php" method="POST" class="p-5 space-y-5">
                    <input type="hidden" name="action" value="atur_stok">
                    <input type="hidden" name="obat_id" value="<?= $sObatId ?>">

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900"><?= e($sNamaObat) ?></h4>
                            <p class="text-sm text-gray-500 mt-1"><?= e($sKodeObat) ?> &middot; <?= e($sBentuk) ?></p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold <?= $stokObat['is_active'] ? 'text-emerald-700 bg-emerald-100/70' : 'text-gray-700 bg-gray-200' ?> rounded">
                            <?= $stokObat['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </div>

                    <div class="px-4 py-3 bg-[var(--color-page)] rounded flex items-center justify-between">
                        <span class="text-sm text-gray-500">Stok saat ini</span>
                        <span class="text-2xl font-bold text-gray-900"><?= $sStokSaatIni ?> <?= e($sSatuan) ?></span>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-900">Jenis perubahan</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-center px-4 py-2 border border-primary-500 bg-[var(--color-page)] rounded cursor-pointer text-primary-700 font-medium text-sm gap-2.5">
                                <input type="radio" name="jenis_perubahan" value="tambah" checked onchange="hitungPreviewStok()" class="w-4 h-4 text-primary-500 bg-white border-gray-300">
                                <span>Tambah stok</span>
                            </label>
                            <label class="flex items-center justify-center px-4 py-2 border border-gray-300 bg-white rounded cursor-pointer text-gray-700 font-medium text-sm gap-2.5 hover:bg-gray-50">
                                <input type="radio" name="jenis_perubahan" value="kurang" onchange="hitungPreviewStok()" class="w-4 h-4 text-primary-500 bg-white border-gray-300">
                                <span>Kurangi stok</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="input_jumlah_stok" class="block mb-2 text-sm font-bold text-gray-900">Jumlah perubahan</label>
                        <input type="number" min="1" name="jumlah" id="input_jumlah_stok" value="10" oninput="hitungPreviewStok()" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                        <p class="mt-1.5 text-xs text-gray-500">Satuan: <?= e($sSatuan) ?>. Masukkan bilangan bulat lebih dari 0.</p>
                    </div>

                    <div>
                        <label for="catatan" class="block mb-2 text-sm font-bold text-gray-900">Catatan / alasan *</label>
                        <textarea id="catatan" name="catatan" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>Penyesuaian hasil pemeriksaan stok fisik.</textarea>
                    </div>

                    <div class="p-4 bg-[var(--color-page)] border border-primary-100/50 rounded">
                        <div class="flex items-start justify-between mb-1">
                            <span class="block text-xs text-gray-500">Stok akhir setelah disimpan</span>
                        </div>
                        <p id="preview_stok_akhir" class="text-2xl font-bold text-primary-500 mb-1"><?= $sStokSaatIni + 10 ?> <?= e($sSatuan) ?></p>
                        <p id="preview_rumus_stok" class="text-xs text-gray-500"><?= $sStokSaatIni ?> + 10 = <?= $sStokSaatIni + 10 ?> <?= e($sSatuan) ?></p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="obat.php" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded">
                            Simpan perubahan
                        </button>
                    </div>
                </form>

                <script>
                function hitungPreviewStok() {
                    const stokAwal = <?= $sStokSaatIni ?>;
                    const satuan = <?= json_encode($sSatuan) ?>;
                    const jenis = document.querySelector('input[name="jenis_perubahan"]:checked')?.value || 'tambah';
                    const jml = parseInt(document.getElementById('input_jumlah_stok')?.value || '0', 10);
                    const hasil = jenis === 'tambah' ? (stokAwal + jml) : (stokAwal - jml);
                    const op = jenis === 'tambah' ? '+' : '-';

                    const elAkhir = document.getElementById('preview_stok_akhir');
                    const elRumus = document.getElementById('preview_rumus_stok');
                    if (elAkhir && elRumus) {
                        elAkhir.textContent = hasil + ' ' + satuan;
                        elAkhir.className = hasil < 0 ? 'text-2xl font-bold text-red-600 mb-1' : 'text-2xl font-bold text-primary-500 mb-1';
                        elRumus.textContent = stokAwal + ' ' + op + ' ' + jml + ' = ' + hasil + ' ' + satuan;
                    }
                }
                </script>
            <?php endif; ?>

        </div>
    </div>
</div>