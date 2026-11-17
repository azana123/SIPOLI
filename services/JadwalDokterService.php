<?php
declare(strict_types=1);

final class JadwalDokterService {
    public static function ajukanPerubahan(array $in): array {
        $dokterId  = profil_id_sesi('dokter');
        $jadwalId  = api_int($in, 'jadwal_terdampak');
        $jenis     = api_str($in, 'jenis_perubahan', 'Perubahan jam praktik');
        $usulTgl   = api_str($in, 'usulan_tanggal');
        $usulMulai = api_str($in, 'usulan_mulai');
        $usulSelesai = api_str($in, 'usulan_selesai');
        $alasan    = api_str($in, 'alasan');

        if ($dokterId <= 0 || $jadwalId <= 0 || $alasan === '') {
            throw new ApiException('Jadwal yang terdampak dan alasan perubahan wajib diisi.');
        }

        [$judul, $pesan] = api_transaction(static function (PDO $pdo) use (
            $dokterId, $jadwalId, $jenis, $usulTgl, $usulMulai, $usulSelesai, $alasan
        ): array {
            $stmt = $pdo->prepare("
                SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, p.nama AS nama_poli, d.nama AS nama_dokter
                FROM jadwal j
                JOIN poli p ON p.id = j.poli_id
                JOIN dokter d ON d.id = j.dokter_id
                WHERE j.id = :id AND j.dokter_id = :dokter
            ");
            $stmt->execute([':id' => $jadwalId, ':dokter' => $dokterId]);
            $jadwal = $stmt->fetch();
            if (!$jadwal) {
                throw new ApiException('Data jadwal praktik tidak ditemukan.');
            }

            $tglAsli = date('d M Y', strtotime($jadwal['tanggal']));
            $jamAsli = substr((string) $jadwal['jam_mulai'], 0, 5) . '–' . substr((string) $jadwal['jam_selesai'], 0, 5) . ' WIB';
            $nama    = $jadwal['nama_dokter'];

            $judul = "Pengajuan {$jenis} - {$nama}";
            $pesan = "{$nama} mengajukan {$jenis} untuk jadwal {$jadwal['nama_poli']} pada {$tglAsli} ({$jamAsli}). ";
            if ($usulTgl !== '' || ($usulMulai !== '' && $usulSelesai !== '')) {
                $tglUsul = $usulTgl !== '' ? date('d M Y', strtotime($usulTgl)) : $tglAsli;
                $jamUsul = ($usulMulai !== '' && $usulSelesai !== '') ? "{$usulMulai}–{$usulSelesai} WIB" : 'jam tetap';
                $pesan .= "Usulan waktu pengganti: {$tglUsul} ({$jamUsul}). ";
            }
            $pesan .= "Alasan: {$alasan}";

            $pdo->prepare("SELECT fn_kirim_ke_admin('info', :judul, :pesan, 'jadwal', :ref)")
                ->execute([':judul' => $judul, ':pesan' => $pesan, ':ref' => $jadwalId]);

            return [$judul, $pesan];
        });

        return ['jadwal_id' => $jadwalId, 'judul' => $judul];
    }
}
