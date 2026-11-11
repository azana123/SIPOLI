<?php
$dokterIdFormPeriksa = (int) ($_SESSION['profil_id'] ?? 0);
$periksaAntreanId    = isset($_GET['periksa_antrean_id']) && ctype_digit((string) $_GET['periksa_antrean_id']) ? (int) $_GET['periksa_antrean_id'] : null;
$redirectBackPeriksa = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

// =====================================================================
// PROSES POST: SIMPAN DRAF ATAU SELESAIKAN PEMERIKSAAN & RESEP OBAT
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action_pemeriksaan'] ?? '', ['draft', 'selesai'], true)) {
    $aksiPeriksa     = $_POST['action_pemeriksaan'];
    $antreanIdPost   = (int) ($_POST['antrean_id'] ?? 0);
    $tensi           = trim($_POST['tensi'] ?? '');
    $suhuRaw         = str_replace(',', '.', trim($_POST['suhu'] ?? ''));
    $bbRaw           = str_replace(',', '.', trim($_POST['bb'] ?? ''));
    $hasilAnamnesis  = trim($_POST['hasil_pemeriksaan'] ?? '');
    $diagnosis       = trim($_POST['diagnosis'] ?? '');
    $saranTindakan   = trim($_POST['saran_medis'] ?? '');

    $suhuVal = ($suhuRaw !== '' && is_numeric($suhuRaw)) ? (float) $suhuRaw : null;
    $bbVal   = ($bbRaw !== '' && is_numeric($bbRaw)) ? (float) $bbRaw : null;

    if ($aksiPeriksa === 'draft') {
        // Simpan sementara di session agar dokter dapat melanjutkan nanti
        $_SESSION['draft_pemeriksaan'][$antreanIdPost] = [
            'tensi'             => $tensi,
            'suhu'              => $suhuRaw,
            'bb'                => $bbRaw,
            'hasil_pemeriksaan' => $hasilAnamnesis,
            'diagnosis'         => $diagnosis,
            'saran_medis'       => $saranTindakan,
        ];
        $_SESSION['flash_success'] = "Draf pemeriksaan berhasil disimpan sementara. Status pasien saat ini: Diperiksa.";
    } else {
        // Aksi Selesaikan Pemeriksaan -> Insert ke tabel pemeriksaan, resep_obat, dan permintaan_obat
        try {
            $pdo->beginTransaction();

            if (!empty($_SESSION['user_id'])) {
                $stmtCfg = $pdo->prepare("SELECT set_config('app.user_id', :uid, true)");
                $stmtCfg->execute([':uid' => (string) $_SESSION['user_id']]);
            }

            $stmtAnt = $pdo->prepare("
                SELECT a.id, a.pasien_id, a.poli_id, a.tanggal, a.status, r.keluhan AS keluhan_rsv
                FROM antrean a
                LEFT JOIN reservasi r ON r.id = a.reservasi_id
                WHERE a.id = :id
                FOR UPDATE OF a
            ");
            $stmtAnt->execute([':id' => $antreanIdPost]);
            $antRow = $stmtAnt->fetch();

            if (!$antRow) {
                throw new Exception("Data antrean pasien tidak ditemukan.");
            }
            if ($antRow['status'] === 'selesai') {
                throw new Exception("Pemeriksaan untuk antrean ini sudah diselesaikan sebelumnya.");
            }

            // Pastikan status antrean 'diperiksa' dan dokter_id tercatat sebelum insert pemeriksaan
            $stmtSetDok = $pdo->prepare("
                UPDATE antrean
                   SET status      = 'diperiksa',
                       dokter_id   = COALESCE(dokter_id, :dok_id),
                       waktu_mulai = COALESCE(waktu_mulai, NOW())
                 WHERE id = :id
            ");
            $stmtSetDok->execute([
                ':dok_id' => $dokterIdFormPeriksa,
                ':id'     => $antreanIdPost,
            ]);

            $keluhanAwal = $antRow['keluhan_rsv'] ?: $hasilAnamnesis;

            // 1. Insert ke tabel pemeriksaan (trigger trg_selesaikan_antrean akan otomatis mengubah antrean & reservasi menjadi 'selesai')
            $stmtInsPem = $pdo->prepare("
                INSERT INTO pemeriksaan (
                    antrean_id, pasien_id, dokter_id, poli_id, tanggal,
                    keluhan, tekanan_darah, suhu, berat_badan, diagnosa, catatan, tindak_lanjut
                ) VALUES (
                    :antrean_id, :pasien_id, :dokter_id, :poli_id, :tanggal,
                    :keluhan, :tensi, :suhu, :bb, :diagnosa, :catatan, :tindak_lanjut
                )
                RETURNING id
            ");
            $stmtInsPem->bindValue(':antrean_id', $antreanIdPost, PDO::PARAM_INT);
            $stmtInsPem->bindValue(':pasien_id', (int) $antRow['pasien_id'], PDO::PARAM_INT);
            $stmtInsPem->bindValue(':dokter_id', $dokterIdFormPeriksa, PDO::PARAM_INT);
            $stmtInsPem->bindValue(':poli_id', (int) $antRow['poli_id'], PDO::PARAM_INT);
            $stmtInsPem->bindValue(':tanggal', $antRow['tanggal'], PDO::PARAM_STR);
            $stmtInsPem->bindValue(':keluhan', $keluhanAwal !== '' ? $keluhanAwal : null, $keluhanAwal !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsPem->bindValue(':tensi', $tensi !== '' ? $tensi : null, $tensi !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsPem->bindValue(':suhu', $suhuVal, $suhuVal !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsPem->bindValue(':bb', $bbVal, $bbVal !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsPem->bindValue(':diagnosa', $diagnosis, PDO::PARAM_STR);
            $stmtInsPem->bindValue(':catatan', $hasilAnamnesis, PDO::PARAM_STR);
            $stmtInsPem->bindValue(':tindak_lanjut', $saranTindakan !== '' ? $saranTindakan : null, $saranTindakan !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtInsPem->execute();
            $pemeriksaanBaruRow = $stmtInsPem->fetch();
            $pemeriksaanBaruId  = (int) $pemeriksaanBaruRow['id'];

            // 2. Proses Resep Obat (jika ada obat yang dipilih)
            $obatIds  = $_POST['obat_id'] ?? [];
            $jumlahs  = $_POST['jumlah'] ?? [];
            $dosiss   = $_POST['dosis'] ?? [];
            $aturans  = $_POST['aturan'] ?? [];
            $catatans = $_POST['catatan'] ?? [];

            $adaResep = false;
            $stmtInsResep = $pdo->prepare("
                INSERT INTO resep_obat (pemeriksaan_id, obat_id, jumlah, aturan_pakai)
                VALUES (:pem_id, :obat_id, :jumlah, :aturan)
            ");

            if (is_array($obatIds)) {
                foreach ($obatIds as $idx => $obIdRaw) {
                    $obId = (int) $obIdRaw;
                    $jml  = (int) ($jumlahs[$idx] ?? 0);
                    if ($obId <= 0 || $jml <= 0) {
                        continue;
                    }

                    $dosisTxt   = trim($dosiss[$idx] ?? '');
                    $aturanTxt  = trim($aturans[$idx] ?? '');
                    $catatanTxt = trim($catatans[$idx] ?? '');

                    // Gabungkan dosis, aturan pakai, dan catatan item ke kolom aturan_pakai (maks 100 karakter)
                    $gabungAturan = trim($dosisTxt . ($aturanTxt !== '' ? ' - ' . $aturanTxt : '') . ($catatanTxt !== '' ? ' (' . $catatanTxt . ')' : ''));
                    $gabungAturan = mb_substr($gabungAturan, 0, 100);

                    $stmtInsResep->execute([
                        ':pem_id'  => $pemeriksaanBaruId,
                        ':obat_id' => $obId,
                        ':jumlah'  => $jml,
                        ':aturan'  => $gabungAturan !== '' ? $gabungAturan : null,
                    ]);
                    $adaResep = true;
                }
            }

            // 3. Jika ada resep obat, buat permintaan_obat untuk Admin Apotek (trigger trg_notif_permintaan_obat otomatis mengirim inbox ke Admin)
            if ($adaResep) {
                $stmtPermObat = $pdo->prepare("
                    INSERT INTO permintaan_obat (pemeriksaan_id, status, catatan)
                    VALUES (:pem_id, 'menunggu', :cat)
                ");
                $stmtPermObat->execute([
                    ':pem_id' => $pemeriksaanBaruId,
                    ':cat'    => 'Resep dari pemeriksaan #' . $pemeriksaanBaruId,
                ]);
            }

            unset($_SESSION['draft_pemeriksaan'][$antreanIdPost]);
            $pdo->commit();

            $_SESSION['flash_success'] = $adaResep
                ? "Pemeriksaan selesai disimpan dan resep obat telah diteruskan ke Admin."
                : "Pemeriksaan pasien berhasil diselesaikan.";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $rawMsg = $e->getMessage();
            if (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $rawMsg, $matches)) {
                $_SESSION['flash_error'] = trim($matches[1]);
            } else {
                $_SESSION['flash_error'] = "Gagal menyimpan pemeriksaan: " . $rawMsg;
            }
            error_log('[dokter-form-pemeriksaan] ' . $rawMsg);
        }
    }

    if (!headers_sent()) {
        header("Location: " . $redirectBackPeriksa);
        exit;
    }
    echo '<script>window.location.href = ' . json_encode($redirectBackPeriksa) . ';</script>';
    exit;
}

// =====================================================================
// PROSES GET: AMBIL DATA ANTREAN YANG AKAN DIPERIKSA & KATALOG OBAT AKTIF
// =====================================================================
$pasienPeriksa = null;
$katalogObat   = [];

if (isset($pdo) && $periksaAntreanId !== null && $dokterIdFormPeriksa > 0) {
    try {
        // Jika status antrean masih 'menunggu', ubah menjadi 'diperiksa' saat dokter membuka form pemeriksaan
        $stmtMulai = $pdo->prepare("
            UPDATE antrean
               SET status      = 'diperiksa',
                   dokter_id   = COALESCE(dokter_id, :dok_id),
                   waktu_mulai = COALESCE(waktu_mulai, NOW())
             WHERE id = :id AND status = 'menunggu'
        ");
        $stmtMulai->execute([
            ':dok_id' => $dokterIdFormPeriksa,
            ':id'     => $periksaAntreanId,
        ]);

        // Sinkronkan juga status reservasi menjadi 'hadir' jika ada
        $stmtRsvHadir = $pdo->prepare("
            UPDATE reservasi
               SET status = 'hadir'
             WHERE id = (SELECT reservasi_id FROM antrean WHERE id = :id)
               AND status = 'dikonfirmasi'
        ");
        $stmtRsvHadir->execute([':id' => $periksaAntreanId]);

        $stmtDataAnt = $pdo->prepare("
            SELECT 
                a.id AS antrean_id,
                a.no_antrean,
                a.status,
                a.waktu_daftar,
                ps.id AS pasien_id,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                ps.riwayat_alergi,
                pol.nama AS nama_poli,
                j.jam_mulai,
                r.keluhan AS keluhan_awal
            FROM antrean a
            JOIN pasien ps ON ps.id = a.pasien_id
            JOIN poli pol  ON pol.id = a.poli_id
            LEFT JOIN jadwal j    ON j.id = a.jadwal_id
            LEFT JOIN reservasi r ON r.id = a.reservasi_id
            WHERE a.id = :id AND a.status IN ('menunggu', 'diperiksa')
        ");
        $stmtDataAnt->execute([':id' => $periksaAntreanId]);
        $pasienPeriksa = $stmtDataAnt->fetch();

        $katalogObat = $pdo->query("
            SELECT id, kode_obat, nama_obat, COALESCE(bentuk, '-') AS bentuk, satuan, stok
            FROM obat
            WHERE is_active = TRUE AND stok > 0
            ORDER BY nama_obat ASC
        ")->fetchAll();

    } catch (PDOException $e) {
        $pasienPeriksa = null;
        error_log('[dokter-load-periksa] ' . $e->getMessage());
    }
}

$isPeriksaOpen = !empty($pasienPeriksa);
$draftData     = $isPeriksaOpen ? ($_SESSION['draft_pemeriksaan'][(int) $pasienPeriksa['antrean_id']] ?? []) : [];
?>
<!-- Modal Mulai Pemeriksaan Pasien -->
<div id="modal-pemeriksaan" data-modal-backdrop="static" tabindex="-1" aria-hidden="<?= $isPeriksaOpen ? 'false' : 'true' ?>" class="<?= $isPeriksaOpen ? 'flex bg-gray-900/50' : 'hidden' ?> overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 shrink-0 pb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">
                        Catat pemeriksaan pasien
                    </h3>
                    <p class="text-base text-gray-500 mt-1">
                        Catat tanda vital, hasil pemeriksaan, diagnosis, dan resep obat bila diperlukan.
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
                <form action="" method="POST" class="overflow-y-auto">
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

                        <!-- Tanda Vital Pasien (3 Kolom) -->
                        <div>
                            <h5 class="text-base font-bold text-gray-900 mb-3">Tanda vital pasien</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="tensi" class="block mb-2 text-sm text-gray-600">Tekanan darah (mmHg)</label>
                                    <input type="text" id="tensi" name="tensi" value="<?= e($draftData['tensi'] ?? '') ?>" placeholder="Contoh: 120/80" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                                </div>
                                <div>
                                    <label for="suhu" class="block mb-2 text-sm text-gray-600">Suhu tubuh (&deg;C)</label>
                                    <input type="text" id="suhu" name="suhu" value="<?= e($draftData['suhu'] ?? '') ?>" placeholder="Contoh: 36.8" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                                </div>
                                <div>
                                    <label for="bb" class="block mb-2 text-sm text-gray-600">Berat badan (kg)</label>
                                    <input type="text" id="bb" name="bb" value="<?= e($draftData['bb'] ?? '') ?>" placeholder="Contoh: 60" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
                                </div>
                            </div>
                        </div>

                        <!-- Hasil Pemeriksaan & Anamnesis -->
                        <div>
                            <label for="hasil_pemeriksaan" class="block mb-2 text-base font-bold text-gray-900">Hasil pemeriksaan &amp; anamnesis *</label>
                            <textarea id="hasil_pemeriksaan" name="hasil_pemeriksaan" rows="3" placeholder="Tuliskan detail keluhan pasien, durasi gejala, dan hasil pemeriksaan fisik..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400" required><?= e($draftData['hasil_pemeriksaan'] ?? '') ?></textarea>
                        </div>

                        <!-- Diagnosis Medis -->
                        <div>
                            <label for="diagnosis" class="block mb-2 text-base font-bold text-gray-900">Diagnosis medis *</label>
                            <input type="text" id="diagnosis" name="diagnosis" value="<?= e($draftData['diagnosis'] ?? '') ?>" placeholder="Masukkan diagnosis utama (contoh: ISPA / Cephalgia / Dispepsia)" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4 placeholder-gray-400" required>
                        </div>

                        <!-- Saran Medis / Tindak Lanjut -->
                        <div>
                            <label for="saran_medis" class="block mb-2 text-sm font-bold text-gray-900">Saran medis / tindak lanjut (opsional)</label>
                            <input type="text" id="saran_medis" name="saran_medis" value="<?= e($draftData['saran_medis'] ?? '') ?>" placeholder="Contoh: Istirahat cukup, perbanyak air hangat, kontrol kembali bila keluhan berlanjut" class="bg-white border border-gray-200 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 placeholder-gray-400">
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
                                            <input type="text" name="dosis[]" placeholder="Contoh: 3x1" class="bg-white border border-gray-300 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
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
                            <p class="text-sm text-gray-600">Menekan tombol <b>Selesaikan</b> akan menyimpan rekam medis, meneruskan resep ke apotek, dan menandai kunjungan pasien selesai.</p>
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

    container.appendChild(clone);
}
</script>