<?php
$dokterIdModalJadwal = (int) ($_SESSION['profil_id'] ?? 0);
$namaDokterModal     = $dokterProfil['nama'] ?? ($_SESSION['username'] ?? 'Dokter');

// Proses POST ketika dokter mengirim pengajuan perubahan jadwal ke Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajukan_perubahan_jadwal') {
    $jadwalTerdampakId = (int) ($_POST['jadwal_terdampak'] ?? 0);
    $jenisPerubahan    = trim($_POST['jenis_perubahan'] ?? 'Perubahan jam praktik');
    $usulanTanggal     = trim($_POST['usulan_tanggal'] ?? '');
    $usulanMulai       = trim($_POST['usulan_mulai'] ?? '');
    $usulanSelesai     = trim($_POST['usulan_selesai'] ?? '');
    $alasan            = trim($_POST['alasan'] ?? '');
    $redirectPage      = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

    if ($jadwalTerdampakId <= 0 || $alasan === '') {
        $_SESSION['flash_error'] = "Jadwal yang terdampak dan alasan perubahan wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmtJdw = $pdo->prepare("
                SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, p.nama AS nama_poli
                FROM jadwal j
                JOIN poli p ON p.id = j.poli_id
                WHERE j.id = :id AND j.dokter_id = :dok_id
            ");
            $stmtJdw->execute([
                ':id'     => $jadwalTerdampakId,
                ':dok_id' => $dokterIdModalJadwal,
            ]);
            $infoJdw = $stmtJdw->fetch();

            if (!$infoJdw) {
                throw new Exception("Data jadwal praktik tidak ditemukan.");
            }

            $tglAsliFmt = date('d M Y', strtotime($infoJdw['tanggal']));
            $jamAsliFmt = substr($infoJdw['jam_mulai'], 0, 5) . '–' . substr($infoJdw['jam_selesai'], 0, 5) . ' WIB';

            $judulNotif = "Pengajuan {$jenisPerubahan} - {$namaDokterModal}";
            $pesanNotif = "{$namaDokterModal} mengajukan {$jenisPerubahan} untuk jadwal {$infoJdw['nama_poli']} pada {$tglAsliFmt} ({$jamAsliFmt}). ";

            if ($usulanTanggal !== '' || ($usulanMulai !== '' && $usulanSelesai !== '')) {
                $tglUsulFmt = $usulanTanggal !== '' ? date('d M Y', strtotime($usulanTanggal)) : $tglAsliFmt;
                $jamUsulFmt = ($usulanMulai !== '' && $usulanSelesai !== '') ? "{$usulanMulai}–{$usulanSelesai} WIB" : 'jam tetap';
                $pesanNotif .= "Usulan waktu pengganti: {$tglUsulFmt} ({$jamUsulFmt}). ";
            }
            $pesanNotif .= "Alasan: {$alasan}";

            // Kirim notifikasi ke seluruh akun Admin aktif via fungsi fn_kirim_ke_admin
            $stmtNotif = $pdo->prepare("
                SELECT fn_kirim_ke_admin('info', :judul, :pesan, 'jadwal', :ref_id)
            ");
            $stmtNotif->execute([
                ':judul'  => $judulNotif,
                ':pesan'  => $pesanNotif,
                ':ref_id' => $jadwalTerdampakId,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Informasi perubahan jadwal berhasil dikirim ke Admin untuk ditindaklanjuti.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = "Gagal mengirim pengajuan: " . $e->getMessage();
            error_log('[dokter-perubahan-jadwal] ' . $e->getMessage());
        }
    }

    if (!headers_sent()) {
        header("Location: " . $redirectPage);
        exit;
    }
    echo '<script>window.location.href = ' . json_encode($redirectPage) . ';</script>';
    exit;
}

// Ambil daftar jadwal aktif milik dokter ini (hari ini dan ke depan) untuk pilihan dropdown
$opsiJadwalDokter = [];
$todayModalStr    = date('Y-m-d');
if (isset($pdo) && $dokterIdModalJadwal > 0) {
    try {
        $todayModalStr = $pdo->query("SELECT fn_today()::text")->fetchColumn() ?: date('Y-m-d');
        $stmtOpsi = $pdo->prepare("
            SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, p.nama AS nama_poli
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            WHERE j.dokter_id = :dok_id
              AND j.tanggal >= fn_today()
              AND j.status <> 'tutup'
            ORDER BY j.tanggal ASC, j.jam_mulai ASC
            LIMIT 14
        ");
        $stmtOpsi->execute([':dok_id' => $dokterIdModalJadwal]);
        $opsiJadwalDokter = $stmtOpsi->fetchAll();
    } catch (PDOException $e) {
        $opsiJadwalDokter = [];
    }
}

$hariModalMap  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
$bulanModalMap = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
?>
<!-- Modal Informasikan Perubahan Jadwal -->
<div id="modal-informasi-perubahan" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <!-- Modal Content -->
        <div class="relative bg-white rounded shadow-xl border border-gray-100 flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 border-b border-gray-200 shrink-0">
                <div>
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900">
                        Informasikan perubahan jadwal
                    </h3>
                    <p class="text-base text-gray-500 mt-1.5">
                        Sampaikan perubahan praktik kepada admin poliklinik.
                    </p>
                </div>
                <button type="button" class="text-gray-500 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded text-sm w-8 h-8 inline-flex justify-center items-center transition-colors mt-1" data-modal-hide="modal-informasi-perubahan">
                    <svg class="w-3.5 h-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <!-- Modal Body (Form & Scrollable Area) -->
            <form action="" method="POST" class="overflow-y-auto">
                <input type="hidden" name="action" value="ajukan_perubahan_jadwal">

                <div class="p-6 space-y-6">
                    
                    <!-- Alert Atas -->
                    <div class="p-4 bg-page rounded">
                        <h4 class="text-base font-bold text-primary mb-1">Perubahan perlu ditindaklanjuti admin</h4>
                        <p class="text-sm text-gray-600">Mengirim form ini belum mengubah jadwal secara langsung atau mengirim notifikasi ke pasien.</p>
                    </div>

                    <!-- Jadwal praktik yang terdampak -->
                    <div>
                        <label for="jadwal_terdampak" class="block mb-2 text-sm font-bold text-gray-900">Jadwal praktik yang terdampak *</label>
                        <?php if (empty($opsiJadwalDokter)): ?>
                            <div class="p-3 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded">
                                Belum ada jadwal praktik aktif yang terdaftar untuk akun Anda mulai hari ini.
                            </div>
                        <?php else: ?>
                            <select id="jadwal_terdampak" name="jadwal_terdampak" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-3" required>
                                <?php foreach ($opsiJadwalDokter as $oj): ?>
                                    <?php
                                    $tsOj  = strtotime($oj['tanggal']);
                                    $lblOj = $hariModalMap[(int) date('N', $tsOj)] . ', ' . date('d', $tsOj) . ' ' . $bulanModalMap[(int) date('n', $tsOj)] . ' ' . date('Y', $tsOj)
                                           . ' • ' . $oj['nama_poli'] . ' • '
                                           . str_replace(':', '.', substr($oj['jam_mulai'], 0, 5)) . '–' . str_replace(':', '.', substr($oj['jam_selesai'], 0, 5)) . ' WIB';
                                    ?>
                                    <option value="<?= (int) $oj['id'] ?>"><?= htmlspecialchars($lblOj, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- Grid 2 Kolom: Jenis Perubahan & Dokter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_perubahan" class="block mb-2 text-sm font-bold text-gray-900">Jenis perubahan *</label>
                            <select id="jenis_perubahan" name="jenis_perubahan" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-3" required>
                                <option value="Perubahan jam praktik" selected>Perubahan jam praktik</option>
                                <option value="Batal praktik">Batal praktik</option>
                                <option value="Ganti dokter">Ganti dokter</option>
                            </select>
                        </div>
                        <div>
                            <label for="dokter_info" class="block mb-2 text-sm font-bold text-gray-900">Dokter</label>
                            <input type="text" id="dokter_info" value="<?= htmlspecialchars($namaDokterModal, ENT_QUOTES, 'UTF-8') ?>" readonly class="bg-gray-50 border border-gray-200 text-gray-700 text-base rounded block w-full px-4 py-3 cursor-default">
                        </div>
                    </div>

                    <!-- Box Group: Usulan waktu pengganti -->
                    <div class="p-5 border border-gray-200 bg-gray-50/50 rounded space-y-4">
                        <div>
                            <h5 class="text-base font-bold text-gray-900">Usulan waktu pengganti (opsional)</h5>
                            <p class="text-sm text-gray-500 mt-1">Admin akan memeriksa ketersediaan sebelum memperbarui jadwal.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-6">
                                <label for="usulan_tanggal" class="block mb-2 text-sm text-gray-600">Tanggal</label>
                                <div class="relative">
                                    <input type="date" id="usulan_tanggal" name="usulan_tanggal" value="<?= htmlspecialchars($todayModalStr, ENT_QUOTES, 'UTF-8') ?>" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-2.5">
                                </div>
                            </div>
                            <div class="md:col-span-3">
                                <label for="usulan_mulai" class="block mb-2 text-sm text-gray-600">Mulai</label>
                                <input type="time" id="usulan_mulai" name="usulan_mulai" value="13:00" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-2.5">
                            </div>
                            <div class="md:col-span-3">
                                <label for="usulan_selesai" class="block mb-2 text-sm text-gray-600">Selesai</label>
                                <input type="time" id="usulan_selesai" name="usulan_selesai" value="16:00" class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full px-4 py-2.5">
                            </div>
                        </div>
                    </div>

                    <!-- Textarea Alasan Perubahan -->
                    <div>
                        <label for="alasan" class="block mb-2 text-sm font-bold text-gray-900">Alasan perubahan *</label>
                        <textarea id="alasan" name="alasan" rows="3" placeholder="Tuliskan alasan perubahan jam atau pembatalan praktik serta usulan waktu pengganti..." class="bg-white border border-gray-200 text-gray-900 text-base rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-4" required></textarea>
                    </div>

                    <!-- Alert Bawah: Tindak lanjut oleh admin -->
                    <div class="p-4 bg-page rounded">
                        <h4 class="text-sm font-bold text-primary mb-1">Tindak lanjut oleh admin</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">Admin meninjau perubahan, mengatur ulang pasien yang terdampak, dan menyampaikan informasi jadwal kepada pasien.</p>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between p-6 border-t border-gray-200 shrink-0">
                    <span class="text-sm text-gray-500 font-bold">* Wajib diisi</span>
                    <div class="flex items-center gap-3">
                        <button type="button" data-modal-hide="modal-informasi-perubahan" class="px-4 md:px-6 py-2.5 text-xs md:text-base font-bold text-gray-500 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <?php if (!empty($opsiJadwalDokter)): ?>
                            <button type="submit" class="px-4 md:px-6 py-2.5 text-xs md:text-base font-bold text-white bg-primary hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 rounded transition-colors cursor-pointer">
                                Kirim ke Admin
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>