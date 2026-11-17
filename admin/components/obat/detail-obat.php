<?php
$isDetObatOpen = !empty($detailObat);
?>
<div id="modal-detail-obat" tabindex="-1" aria-hidden="<?= $isDetObatOpen ? 'false' : 'true' ?>" class="<?= $isDetObatOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <div class="flex items-start justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Detail obat</h3>
                    <p class="text-sm text-gray-500 mt-1">Informasi obat dan riwayat mutasi stok.</p>
                </div>
                <a href="obat.php" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($detailObat): ?>
                <div class="p-5 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900"><?= e($detailObat['nama_obat']) ?></h4>
                            <p class="text-sm text-gray-500 mt-1"><?= e($detailObat['kode_obat']) ?></p>
                        </div>
                        <?php if ($detailObat['is_active']): ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/70 rounded">Aktif</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded">Nonaktif</span>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Bentuk</span>
                            <p class="text-sm font-bold text-gray-900 capitalize"><?= e($detailObat['bentuk'] ?? '-') ?></p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Satuan stok</span>
                            <p class="text-sm font-bold text-gray-900 capitalize"><?= e($detailObat['satuan']) ?></p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-gray-500 mb-1">Kadaluarsa</span>
                            <p class="text-sm font-bold text-gray-900">
                                <?= !empty($detailObat['tanggal_kadaluarsa']) ? e(date('d M Y', strtotime($detailObat['tanggal_kadaluarsa']))) : '-' ?>
                            </p>
                        </div>
                    </div>

                    <div class="p-4 bg-[var(--color-page)] rounded border border-gray-100 flex items-center justify-between">
                        <div>
                            <span class="block text-xs text-gray-500 mb-1">Stok saat ini (Batas minimum: <?= (int) $detailObat['stok_minimum'] ?>)</span>
                            <p class="text-2xl font-bold text-gray-900"><?= (int) $detailObat['stok'] ?> <?= e(ucfirst($detailObat['satuan'])) ?></p>
                        </div>
                        <?php if ((int) $detailObat['stok'] === 0): ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded">Habis</span>
                        <?php elseif ((int) $detailObat['stok'] <= (int) $detailObat['stok_minimum']): ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-orange-800 bg-orange-100 rounded">Menipis</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/80 rounded">Tersedia</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <span class="block text-xs font-medium text-gray-500 mb-1">Keterangan / Dosis</span>
                        <p class="text-sm font-medium text-gray-900"><?= e($detailObat['keterangan'] ?: 'Belum diisi') ?></p>
                    </div>

                    <!-- Riwayat Log Stok Terakhir -->
                    <div>
                        <span class="block text-xs font-bold text-gray-700 mb-2">5 Riwayat Mutasi Stok Terakhir</span>
                        <?php if (empty($riwayatStok)): ?>
                            <p class="text-xs text-gray-400">Belum ada riwayat perubahan stok.</p>
                        <?php else: ?>
                            <div class="space-y-1.5 max-h-36 overflow-y-auto text-xs border border-gray-100 rounded p-2.5 bg-gray-50">
                                <?php foreach ($riwayatStok as $lg): ?>
                                    <div class="flex justify-between items-center border-b border-gray-100 pb-1">
                                        <div>
                                            <span class="font-bold <?= $lg['jenis'] === 'masuk' ? 'text-emerald-700' : 'text-red-600' ?>">
                                                <?= $lg['jenis'] === 'masuk' ? '+' : '-' ?><?= (int) $lg['jumlah'] ?>
                                            </span>
                                            <span class="text-gray-600 ml-1"><?= e($lg['keterangan'] ?? '-') ?></span>
                                        </div>
                                        <span class="text-gray-400"><?= e(date('d/m H:i', strtotime($lg['created_at']))) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                    <a href="obat.php" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                        Tutup
                    </a>
                    <a href="obat.php?stok_id=<?= (int) $detailObat['id'] ?>" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded">
                        Atur stok
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>