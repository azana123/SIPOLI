<!-- Modal Detail Obat -->
<div id="modal-detail-obat" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-lg border border-gray-100">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Detail obat
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Informasi obat dan ketersediaan stok poliklinik.
                    </p>
                </div>
                <button type="button" class="text-primary-600 bg-primary-50 hover:bg-primary-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors" data-modal-hide="modal-detail-obat">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-5">
                
                <!-- Nama Obat, Kode & Status Badge -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900">Paracetamol</h4>
                        <p class="text-sm text-gray-500 mt-1">OBT-001 · 500 mg</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/70 rounded">
                        Aktif
                    </span>
                </div>

                <!-- Grid Bentuk & Satuan Stok -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-xs font-medium text-gray-500 mb-1">Bentuk</span>
                        <p class="text-sm font-bold text-gray-900">Tablet</p>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-500 mb-1">Satuan stok</span>
                        <p class="text-sm font-bold text-gray-900">Tablet</p>
                    </div>
                </div>

                <!-- Box Card Stok Saat Ini -->
                <div class="p-4 bg-[var(--color-page)] rounded border border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="block text-xs text-gray-500 mb-1">Stok saat ini</span>
                        <p class="text-2xl font-bold text-gray-900">240 Tablet</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100/80 rounded">
                        Tersedia
                    </span>
                </div>

                <!-- Dosis Umum & Keterangan -->
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-medium text-gray-500 mb-1">Dosis umum</span>
                        <p class="text-sm font-bold text-gray-900">Belum diisi</p>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-500 mb-1">Keterangan</span>
                        <p class="text-sm font-bold text-gray-900">Belum diisi</p>
                    </div>
                </div>

                <!-- Catatan / Footnote -->
                <p class="text-xs text-gray-500 leading-relaxed pt-1">
                    Status Aktif terpisah dari ketersediaan stok. Label 500 mg adalah informasi sediaan, bukan aturan pakai.
                </p>

            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100">
                <button type="button" data-modal-hide="modal-detail-obat" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 transition-colors">
                    Tutup
                </button>
                <button type="button" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors" data-modal-hide="modal-detail-obat" data-modal-target="modal-atur-stok" data-modal-toggle="modal-atur-stok">
                    Atur stok
                </button>
            </div>

        </div>
    </div>
</div>