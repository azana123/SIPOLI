<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'daftar_pasien_offline') {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../middleware/check-role.php';
    check_role('admin');
    require_once __DIR__ . '/../../config/database.php';

    $nimNip  = trim($_POST['nim_nip'] ?? '');
    $noHp    = trim($_POST['no_hp'] ?? '');
    $poliId  = (int) ($_POST['poli_id'] ?? 1);
    $adminId = isset($_SESSION['profil_id']) ? (int) $_SESSION['profil_id'] : null;

    try {
        $pdo->beginTransaction();

        if (!empty($_SESSION['user_id'])) {
            $stmtCfg = $pdo->prepare("SELECT set_config('app.user_id', :uid, true)");
            $stmtCfg->execute([':uid' => (string) $_SESSION['user_id']]);
        }

        if ($adminId === null or $adminId <= 0) {
            $admFirst = $pdo->query("SELECT id FROM admin ORDER BY id ASC LIMIT 1")->fetch();
            $adminId  = (int) ($admFirst['id'] ?? 1);
        }

        $stmtJadwal = $pdo->prepare("
            SELECT j.id AS jadwal_id, j.dokter_id, p.nama AS nama_poli
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            WHERE j.poli_id = :poli_id
              AND j.tanggal = fn_today()
              AND j.status <> 'tutup'
            ORDER BY 
                ( (NOW() AT TIME ZONE 'Asia/Jakarta')::time BETWEEN j.jam_mulai AND j.jam_selesai ) DESC,
                j.jam_mulai ASC
            LIMIT 1
        ");
        $stmtJadwal->execute([':poli_id' => $poliId]);
        $jadwalAktif = $stmtJadwal->fetch();

        if (!$jadwalAktif) {
            $stmtDokPoli = $pdo->prepare("
                SELECT d.id AS dokter_id, p.nama AS nama_poli
                FROM poli p
                LEFT JOIN dokter d ON d.poli_id = p.id AND d.is_active = TRUE
                WHERE p.id = :poli_id
                ORDER BY d.id ASC
                LIMIT 1
            ");
            $stmtDokPoli->execute([':poli_id' => $poliId]);
            $dokPoli = $stmtDokPoli->fetch();

            $dokIdAuto = !empty($dokPoli['dokter_id']) ? (int) $dokPoli['dokter_id'] : null;

            $stmtBuatJadwal = $pdo->prepare("
                INSERT INTO jadwal (poli_id, dokter_id, tanggal, jam_mulai, jam_selesai, kuota, status, dipublikasi)
                VALUES (:poli_id, :dokter_id, fn_today(), '08:00', '16:00', NULL, 'buka', TRUE)
                ON CONFLICT (poli_id, tanggal, jam_mulai) DO UPDATE
                    SET status = 'buka'
                RETURNING id AS jadwal_id, dokter_id
            ");
            $stmtBuatJadwal->bindValue(':poli_id', $poliId, PDO::PARAM_INT);
            $stmtBuatJadwal->bindValue(':dokter_id', $dokIdAuto, $dokIdAuto === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtBuatJadwal->execute();
            $jadwalBaru = $stmtBuatJadwal->fetch();

            $jadwalAktif = [
                'jadwal_id' => $jadwalBaru['jadwal_id'],
                'dokter_id' => $jadwalBaru['dokter_id'],
                'nama_poli' => $dokPoli['nama_poli'] ?? 'Poli Umum',
            ];
        }

        $jadwalIdAktif = (int) $jadwalAktif['jadwal_id'];
        $dokterIdAktif = $jadwalAktif['dokter_id'] !== null ? (int) $jadwalAktif['dokter_id'] : null;

        $stmtPasien = $pdo->prepare("
            INSERT INTO pasien (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, no_hp)
            VALUES (:nim_nip, '-', 'L', CURRENT_DATE, 'mahasiswa', NULLIF(:no_hp, ''))
            ON CONFLICT (nim_nip) DO UPDATE
                SET no_hp     = COALESCE(NULLIF(EXCLUDED.no_hp, ''), pasien.no_hp),
                    is_active = TRUE
            RETURNING id, nama
        ");
        $stmtPasien->execute([
            ':nim_nip' => $nimNip,
            ':no_hp'   => $noHp,
        ]);
        $pasienRow = $stmtPasien->fetch();

        $stmtAntrean = $pdo->prepare("
            INSERT INTO antrean (no_antrean, pasien_id, poli_id, jadwal_id, dokter_id, admin_id)
            VALUES (0, :pasien_id, :poli_id, :jadwal_id, :dokter_id, :admin_id)
            RETURNING no_antrean
        ");
        $stmtAntrean->bindValue(':pasien_id', (int) $pasienRow['id'], PDO::PARAM_INT);
        $stmtAntrean->bindValue(':poli_id', $poliId, PDO::PARAM_INT);
        $stmtAntrean->bindValue(':jadwal_id', $jadwalIdAktif, PDO::PARAM_INT);
        $stmtAntrean->bindValue(':dokter_id', $dokterIdAktif, $dokterIdAktif === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmtAntrean->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
        $stmtAntrean->execute();
        $antRowBaru    = $stmtAntrean->fetch();
        $noAntreanBaru = (int) ($antRowBaru['no_antrean'] ?? 0);

        $pdo->commit();
        $_SESSION['flash_success'] = "Pasien walk-in {$pasienRow['nama']} ({$nimNip}) berhasil didaftarkan ke {$jadwalAktif['nama_poli']} dengan Nomor Antrean #{$noAntreanBaru}.";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $rawMsg = $e->getMessage();
        if (str_contains($rawMsg, 'uq_antrean_pasien_aktif')) {
            $_SESSION['flash_error'] = "Pasien dengan NIM/NIP {$nimNip} sudah memiliki antrean aktif di poli ini hari ini.";
        } elseif (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $rawMsg, $matches)) {
            $_SESSION['flash_error'] = trim($matches[1]);
        } else {
            $_SESSION['flash_error'] = "Gagal mendaftarkan pasien walk-in: " . $rawMsg;
        }
        error_log('[walkin-offline] ' . $rawMsg);
    }

    header("Location: ../reservasi.php");
    exit;
}

$poliWalkinList = [];
if (isset($pdo)) {
    try {
        $poliWalkinList = $pdo->query("SELECT id, nama FROM poli WHERE butuh_reservasi = FALSE ORDER BY id ASC")->fetchAll();
    } catch (PDOException $e) {
        $poliWalkinList = [];
    }
}
?>
<!-- Modal Tambah Pasien Offline (Walk-in) -->
<div id="modal-tambah-pasien" tabindex="-1" aria-hidden="true" data-modal-backdrop="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tambah Pasien Offline (Walk-in)
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="modal-tambah-pasien">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>
            <!-- Modal body: arahkan action langsung ke components/form-pasien-offline.php -->
            <form class="p-4 md:p-5" action="components/form-pasien-offline.php" method="POST">
                <input type="hidden" name="action" value="daftar_pasien_offline">

                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="walkin_nim_nip" class="block mb-2 text-sm font-medium text-gray-900">NIM / NIP Civitas Akademika *</label>
                        <input type="text" name="nim_nip" id="walkin_nim_nip" pattern="[0-9]{8,20}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="Masukkan NIM atau NIP terdaftar" required>
                        <p class="mt-1 text-xs text-gray-500">Nama, tanggal lahir, dan status civitas diambil otomatis dari database civitas.</p>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="walkin_poli_id" class="block mb-2 text-sm font-medium text-gray-900">Poli Tujuan (Walk-in) *</label>
                        <select id="walkin_poli_id" name="poli_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                            <?php if (!empty($poliWalkinList)): ?>
                                <?php foreach ($poliWalkinList as $pw): ?>
                                    <option value="<?= (int) $pw['id'] ?>"><?= htmlspecialchars($pw['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">Poli Umum</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="walkin_no_hp" class="block mb-2 text-sm font-medium text-gray-900">No. HP (Opsional)</label>
                        <input type="text" name="no_hp" id="walkin_no_hp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="text-white inline-flex items-center bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer">
                        <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        Buat Antrean Walk-in
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>