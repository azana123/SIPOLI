<div id="modal-ketidakhadiran" tabindex="-1" aria-hidden="true" class="hidden fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-[calc(100%-2rem)] max-w-md bg-neutral-primary-soft border border-primary-100 rounded-base shadow-xs">
    <div class="relative w-full max-w-sm max-h-full flex items-end md:items-center min-h-full md:min-h-0 p-0 md:p-4 pb-0 md:pb-4">
        <div class="relative bg-white rounded shadow-xl border border-gray-100 w-full p-5 pt-3 animate-fade-up md:animate-none">
            <div class="w-10 h-1 bg-gray-300 rounded mx-auto mb-5"></div>

            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-700 text-xl"></i>
            </div>

            <div class="text-center mb-5">
                <h3 class="mb-1.5 text-[16px] font-bold text-gray-900">Tidak bisa hadir?</h3>
                <p class= text-slate-500">Poliklinik akan menjadwalkan ulang.</p>
            </div>

            <form action="#" method="POST">
                <div class="mb-6 text-left">
                    <label for="alasan_reschedule" class="block mb-2 font-medium text-slate-500">Alasan(opsional)</label>
                    <textarea id="alasan_reschedule" name="alasan_reschedule" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 resize-none transition-colors" placeholder=""></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" data-modal-hide="modal-ketidakhadiran" class="w-full px-4 py-2.5 font-bold text-primary-700 bg-white border border-primary-600 rounded hover:bg-primary-50 focus:ring-4 focus:outline-none focus:ring-primary-100 transition-colors">
                        Kembali
                    </button>
                    <button type="submit" class="w-full px-4 py-2.5 font-bold text-white bg-primary-500 border border-primary-500 rounded hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 transition-colors">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>