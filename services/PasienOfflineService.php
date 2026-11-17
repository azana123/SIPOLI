<?php
declare(strict_types=1);

final class PasienOfflineService {
    public static function daftar(array $in): array {
        $nimNip  = api_str($in, 'nim_nip');
        $noHp    = api_nullable_str($in, 'no_hp');
        $poliId  = api_int($in, 'poli_id', 1);
        $adminId = profil_id_sesi('admin') ?: null;

        if ($nimNip === '' || $poliId <= 0) {
            throw new ApiException('NIM/NIP dan poli kunjungan wajib diisi.');
        }

        return api_transaction(static function (PDO $pdo) use ($nimNip, $noHp, $poliId, $adminId): array {
            $stmtJadwal = $pdo->prepare("
                SELECT j.id AS jadwal_id, j.dokter_id, p.nama AS nama_poli
                FROM jadwal j
                JOIN poli p ON p.id = j.poli_id
                WHERE j.poli_id = :poli AND j.tanggal = fn_today() AND j.status <> 'tutup'
                ORDER BY (
                    (NOW() AT TIME ZONE 'Asia/Jakarta')::time BETWEEN j.jam_mulai AND j.jam_selesai
                ) DESC, j.jam_mulai ASC
                LIMIT 1
            ");
            $stmtJadwal->execute([':poli' => $poliId]);
            $jadwal = $stmtJadwal->fetch();

            if (!$jadwal) {
                $info = $pdo->prepare('SELECT nama FROM poli WHERE id = :id');
                $info->execute([':id' => $poliId]);
                $baru = $pdo->prepare("
                    INSERT INTO jadwal (poli_id, dokter_id, tanggal, jam_mulai, jam_selesai, kuota, status, dipublikasi)
                    VALUES (:poli, NULL, fn_today(), '08:00', '16:00', NULL, 'buka', TRUE)
                    ON CONFLICT (poli_id, tanggal, jam_mulai) DO UPDATE SET status = 'buka'
                    RETURNING id AS jadwal_id, dokter_id
                ");
                $baru->execute([':poli' => $poliId]);
                $jadwalBaru = $baru->fetch();
                $jadwal = [
                    'jadwal_id' => $jadwalBaru['jadwal_id'],
                    'dokter_id' => $jadwalBaru['dokter_id'],
                    'nama_poli' => $info->fetchColumn() ?: 'Poli Umum',
                ];
            }

            $stmtPs = $pdo->prepare("
                INSERT INTO pasien (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, no_hp)
                VALUES (:nim, '-', 'L', CURRENT_DATE, 'mahasiswa', NULLIF(:hp, ''))
                ON CONFLICT (nim_nip) DO UPDATE
                   SET no_hp = COALESCE(NULLIF(EXCLUDED.no_hp, ''), pasien.no_hp),
                       is_active = TRUE
                RETURNING id, nama
            ");
            $stmtPs->execute([':nim' => $nimNip, ':hp' => $noHp ?? '']);
            $pasien = $stmtPs->fetch();

            $stmtAntrean = $pdo->prepare("
                INSERT INTO antrean (no_antrean, pasien_id, poli_id, jadwal_id, dokter_id, admin_id)
                VALUES (0, :pasien, :poli, :jadwal, :dokter, :admin)
                RETURNING no_antrean
            ");
            $stmtAntrean->bindValue(':pasien', (int) $pasien['id'], PDO::PARAM_INT);
            $stmtAntrean->bindValue(':poli', $poliId, PDO::PARAM_INT);
            $stmtAntrean->bindValue(':jadwal', (int) $jadwal['jadwal_id'], PDO::PARAM_INT);
            $stmtAntrean->bindValue(':dokter', $jadwal['dokter_id'] === null ? null : (int) $jadwal['dokter_id'],
                $jadwal['dokter_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtAntrean->bindValue(':admin', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmtAntrean->execute();

            return [
                'no_antrean' => (int) $stmtAntrean->fetchColumn(),
                'pasien'     => $pasien['nama'],
                'poli'       => $jadwal['nama_poli'],
            ];
        });
    }
}
