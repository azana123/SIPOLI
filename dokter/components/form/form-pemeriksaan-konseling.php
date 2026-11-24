<?php
$dokterIdFormPeriksa = (int) ($_SESSION['profil_id'] ?? 0);
$periksaAntreanId    = isset($_GET['periksa_antrean_id']) && ctype_digit((string) $_GET['periksa_antrean_id']) ? (int) $_GET['periksa_antrean_id'] : null;
$redirectBackPeriksa = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

$pasienPeriksa = null;
$katalogObat   = [];

if (isset($pdo) && $periksaAntreanId !== null && $dokterIdFormPeriksa > 0) {
    try {
        $stmtMulai = QueryDokterFormPeriksa::mulai([
            ':dok_id' => $dokterIdFormPeriksa,
            ':id'     => $periksaAntreanId,
        ]);

        $stmtRsvHadir = QueryDokterFormPeriksa::rsvHadir([':id' => $periksaAntreanId]);

        $stmtDataAnt = QueryDokterFormPeriksa::dataAnt([':id' => $periksaAntreanId]);
        $pasienPeriksa = $stmtDataAnt->fetch();

        $katalogObat = QueryDokterFormPeriksa::katalogObat();

    } catch (PDOException $e) {
        $pasienPeriksa = null;
        error_log('[dokter-load-periksa-konseling] ' . $e->getMessage());
    }
}

$isPeriksaOpen = !empty($pasienPeriksa);
$draftData     = $isPeriksaOpen ? ($_SESSION['draft_pemeriksaan'][(int) $pasienPeriksa['antrean_id']] ?? []) : [];
?>
<!-- Modal Mulai Pemeriksaan Pasien (Poli Konseling) -->
<div id="modal-pemeriksaan" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isPeriksaOpen ? 'false' : 'true' ?>" class="<?= $isPeriksaOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 shrink-0 pb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">
                        Pemeriksaan Konseling
                    </h3>
                    <p class="text-base text-gray-500 mt-1">
                        Catat anamnesis, diagnosa kesehatan jiwa, dan saran medis.
                    </p>
                </div>
                <a href="<?= htmlspecialchars($redirectBackPeriksa, ENT_QUOTES, 'UTF-8') ?>" class="text-gray-500 bg-transparent hover:bg-gray-100 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </a>
            </div>

            <?php if ($pasienPeriksa): ?>
                <?php
                $lblCivPeriksa = ($pasienPeriksa['status_civitas'] === 'mahasiswa') ? 'NIM' : 'NIP';
                $jamPeriksaFmt = !empty($pasienPeriksa['jam_mulai'])
                    ? str_replace(':', '.', substr($pasienPeriksa['jam_mulai'], 0, 5)) . ' WIB'
                    : date('H.i', strtotime($pasienPeriksa['waktu_daftar'])) . ' WIB';
                ?>
                <!-- Modal Body (Scrollable Form) -->
                <form action="" method="POST" data-api-endpoint="/api/dokter/pemeriksaan.php" class="overflow-y-auto">
                    <input type="hidden" name="antrean_id" value="<?= (int) $pasienPeriksa['antrean_id'] ?>">

                    <div class="px-6 pb-6 space-y-6">

                        <!-- Box Info Pasien -->
                        <div class="p-4 bg-page border border-primary-100 rounded flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <h4 class="text-base font-bold text-primary"><?= e($pasienPeriksa['nama_pasien']) ?></h4>
                                    <span class="text-sm text-gray-500">&bull; <?= $lblCivPeriksa ?> <?= e($pasienPeriksa['nim_nip']) ?> &bull; <span class="capitalize"><?= e($pasienPeriksa['status_civitas']) ?></span></span>
                                </div>
                                <p class="text-sm text-gray-600 mb-0.5">
                                    Antrean #<?= (int) $pasienPeriksa['no_antrean'] ?>: <span class="font-bold text-gray-900"><?= e($jamPeriksaFmt) ?></span> | <?= e($pasienPeriksa['nama_poli']) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    Keluhan awal: <span class="font-bold text-primary"><?= e($pasienPeriksa['keluhan_awal'] ?: 'Walk-in / Pemeriksaan langsung') ?></span>
                                </p>
                                <?php if (!empty($pasienPeriksa['riwayat_alergi'])): ?>
                                    <p class="text-xs font-bold text-red-700 bg-red-50 px-2.5 py-1 rounded mt-2 inline-block">
                                        Riwayat Alergi: <?= e($pasienPeriksa['riwayat_alergi']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center px-2.5 py-1 text-primary-700 text-xs font-semibold rounded bg-primary-100">
                                    Sedang Diperiksa
                                </span>
                            </div>
                        </div>

                        <!-- Keluhan & Anamnesis -->
                        <div>
                            <h5 class="text-base font-bold text-gray-900 mb-3">Keluhan &amp; Anamnesis</h5>
                            <div>
                                <label for="keluhan_konseling" class="block mb-2 text-sm font-medium text-gray-600">Keluhan Utama</label>
                                <textarea id="keluhan_konseling" name="keluhan_konseling" rows="3" placeholder="Contoh: Merasa cemas, sulit tidur, mudah marah..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400" required><?= e($draftData['keluhan_konseling'] ?? '') ?></textarea>
                            </div>
                            <div class="mt-4">
                                <label for="anamnesis_konseling" class="block mb-2 text-sm font-medium text-gray-600">Anamnesis Psikologis</label>
                                <textarea id="anamnesis_konseling" name="anamnesis_konseling" rows="4" placeholder="Contoh: Gejala muncul 3 bulan lalu setelah kehilangan pekerjaan, sulit berkonsentrasi, sering merasa lelah..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400"><?= e($draftData['anamnesis_konseling'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Diagnosis Medis Kesehatan Jiwa -->
                        <div>
                            <label for="diagnosis_konseling" class="block mb-2 text-base font-bold text-gray-900">Diagnosis Kesehatan Jiwa *</label>
                            <input type="text" id="diagnosis_konseling" name="diagnosis_konseling" value="<?= e($draftData['diagnosis_konseling'] ?? '') ?>" placeholder="Contoh: Gangguan Kecemasan Umum, Depresi Ringan..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400" required>
                        </div>

                        <!-- Saran Medis / Tindak Lanjut -->
                        <div>
                            <label for="saran_medis_konseling" class="block mb-2 text-sm font-bold text-gray-900">Saran Medis / Tindak Lanjut</label>
                            <textarea id="saran_medis_konseling" name="saran_medis_konseling" rows="3" placeholder="Contoh: Terapi kognitif perilaku, Istirahat cukup, Kontrol 2 minggu..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400"><?= e($draftData['saran_medis_konseling'] ?? '') ?></textarea>
                        </div>

                        <!-- Grup Resep Obat -->
                        <div class="p-5 bg-gray-50/50 border border-gray-200 rounded">
                            <h5 class="text-base font-bold text-gray-900">Resep obat (opsional)</h5>
                            <p class="text-sm text-gray-500 mb-4">Pilih obat jika pasien memerlukan resep. Kosongkan pilihan obat jika tidak ada resep.</p>

                            <div id="container-resep-obat" class="space-y-4">
                                <div class="card-obat p-5 bg-page border border-primary-100 rounded space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="judul-item-obat text-base font-bold text-gray-900">Obat 1</h4>
                                        <span class="text-xs text-gray-500">Bentuk, satuan &amp; stok terisi otomatis dari katalog</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-6">
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Nama obat</label>
                                            <select name="obat_id[]" onchange="updateInfoKatalogObat(this)" class="select-obat bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                                <option value="">-- Tanpa obat / Pilih obat --</option>
                                                <?php foreach ($katalogObat as $ob): ?>
                                                    <option value="<?= (int) $ob['id'] ?>"
                                                            data-bentuk="<?= e(ucfirst($ob['bentuk'])) ?>"
                                                            data-satuan="<?= e(ucfirst($ob['satuan'])) ?>"
                                                            data-stok="<?= (int) $ob['stok'] ?>">
                                                        <?= e($ob['nama_obat']) ?> (<?= e($ob['kode_obat']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Bentuk</label>
                                            <div class="info-bentuk p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-600 rounded">&mdash;</div>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Satuan</label>
                                            <div class="info-satuan p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-medium text-gray-600 rounded">&mdash;</div>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Stok tersedia</label>
                                            <div class="info-stok p-2.5 bg-cyan-50/70 border border-cyan-100/60 text-center text-sm font-bold text-primary-700 rounded">&mdash;</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Jumlah</label>
                                            <input type="number" min="1" name="jumlah[]" placeholder="Contoh: 1" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Dosis</label>
                                            <input type="text" name="dosis[]" placeholder="Contoh: 1x1" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-sm font-medium text-gray-700">Aturan pakai</label>
                                            <input type="text" name="aturan[]" placeholder="Contoh: Sesudah makan" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-700">Catatan item (opsional)</label>
                                        <input type="text" name="catatan[]" placeholder="Tambahkan catatan khusus untuk obat ini..." class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                    </div>
                                </div>

                                <button type="button" onclick="tambahBarisObatDokter()" class="mt-4 border border-gray-300 text-primary bg-white hover:bg-gray-50 rounded-base px-3.5 py-1.5 text-xs font-semibold transition-colors shadow-sm cursor-pointer inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#0f7485" class="mr-1">
                                        <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                                    </svg>
                                    Tambah Obat Lain
                                </button>
                            </div>

                            <div class="p-4 bg-page rounded">
                                <h4 class="text-sm font-bold text-primary mb-0.5">Pembaruan status otomatis</h4>
                                <p class="text-sm text-gray-600">Menekan tombol <b>Selesaikan Pemeriksaan</b> akan menyimpan rekam medis, meneruskan resep ke apotek, dan menandai kunjungan pasien selesai.</p>
                            </div>

                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-between p-6 border-t border-gray-100 shrink-0 bg-white">
                            <span class="text-sm text-gray-500 font-bold">* Wajib diisi</span>
                            <div class="flex items-center gap-3">
                                <a href="<?= htmlspecialchars($redirectBackPeriksa, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors cursor-pointer">
                                    Tutup
                                </a>
                                <button type="submit" name="action_pemeriksaan" value="draft" formnovalidate class="px-6 py-2.5 text-sm font-bold text-primary-700 bg-primary-50 border border-primary-600 hover:bg-primary-100 rounded transition-colors cursor-pointer">
                                    Simpan Draf
                                </button>
                                <button type="submit" name="action_pemeriksaan" value="selesai" class="px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-700 rounded transition-colors cursor-pointer">
                                    Selesaikan Pemeriksaan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function updateInfoKatalogObat(selectEl) {
    const card = selectEl.closest('.card-obat');
    if (!card) return;
    const opt = selectEl.options[selectEl.selectedIndex];
    const bentuk = opt.getAttribute('data-bentuk') || '—';
    const satuan = opt.getAttribute('data-satuan') || '—';
    const stok   = opt.getAttribute('data-stok') || '—';

    card.querySelector('.info-bentuk').textContent = bentuk;
    card.querySelector('.info-satuan').textContent = satuan;
    card.querySelector('.info-stok').textContent   = stok;
}

function tambahBarisObatDokter() {
    const container = document.getElementById('container-resep-obat');
    if (!container) return;
    const cards = container.querySelectorAll('.card-obat');
    const clone = cards[0].cloneNode(true);
    const nomorBaru = cards.length + 1;

    clone.querySelector('.judul-item-obat').textContent = 'Obat ' + nomorBaru;
    clone.querySelector('.info-bentuk').textContent = '—';
    clone.querySelector('.info-satuan').textContent = '—';
    clone.querySelector('.info-stok').textContent   = '—';

    clone.querySelectorAll('input').forEach(inp => { inp.value = ''; });
    clone.querySelectorAll('select').forEach(sel => { sel.selectedIndex = 0; });

    const headerFlex = clone.querySelector('.flex.items-center.justify-between');
    if (headerFlex && !clone.querySelector('.btn-hapus-obat')) {
        const btnHapus = document.createElement('button');
        btnHapus.type = 'button';
        btnHapus.className = 'btn-hapus-obat text-xs font-semibold text-red-600 hover:underline cursor-pointer';
        btnHapus.textContent = 'Hapus baris';
        btnHapus.onclick = function() {
            clone.remove();
            urutkanNomorObat();
        };
        headerFlex.appendChild(btnHapus);
    }

    container.appendChild(clone);
}

function urutkanNomorObat() {
    const container = document.getElementById('container-resep-obat');
    if (!container) return;
    container.querySelectorAll('.card-obat').forEach((card, idx) => {
        card.querySelector('.judul-item-obat').textContent = 'Obat ' + (idx + 1);
    });
}
</script>
