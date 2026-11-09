<!-- Modal Konfirmasi Pembatalan -->
<div id="modal-konfirmasi-pembatalan" tabindex="-1" aria-hidden="true" class="hidden fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-[calc(100%-2rem)] max-w-md bg-neutral-primary-soft border border-primary-100 rounded-base shadow-xs">
    <div class="relative w-full max-w-sm max-h-full flex items-end md:items-center min-h-full md:min-h-0 p-0 md:p-4 pb-0 md:pb-4">
        <div class="relative rounded w-full p-5 animate-fade-up md:animate-none">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded bg-red-50 mb-4">
                <i class="far fa-trash-alt text-red-700 text-xl"></i>
            </div>

            <div class="text-center mb-5">
                <h3 class="mb-2 text-[16px] font-bold text-gray-900">Batalkan reservasi?</h3>
                <p class= text-sm text-slate-500 mt-1">Psikolog, Jum 18 Sep. 14.00</p>
                <p class= text-sm text-slate-500 mt-0.5">dr. Sarah Amelia, Sp.KJ</p>
            </div>

            <form action="#" method="POST">
                <div class="mb-4 text-left">
                    <label for="alasan_batal" class="block mb-2 text-sm font-medium text-slate-500">Alasan(opsional)</label>
                    <textarea id="alasan_batal" name="alasan_batal" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 resize-none transition-colors" placeholder=""></textarea>
                </div>

                <div class="mb-6 bg-[#fdf6e3] rounded p-3.5 border border-[#fae8b4]">
                    <p class="text-[13px] text-[#8c6b16] leading-relaxed">
                        Kuota yang dilepas akan terbuka untuk pasien lain. Kamu perlu reservasi ulang kalau berubah pikiran.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" data-modal-hide="modal-konfirmasi-pembatalan" class="w-full px-4 py-2.5 text-sm font-bold text-primary-700 bg-white border border-primary-600 rounded hover:bg-primary-50 focus:ring-4 focus:outline-none focus:ring-primary-100 transition-colors">
                        Kembali
                    </button>
                    <button type="submit" class="w-full px-4 py-2.5 text-sm font-bold text-white bg-red-700 border border-red-700 rounded hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-200 transition-colors">
                        Ya, batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>