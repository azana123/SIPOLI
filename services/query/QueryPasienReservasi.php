<?php
declare(strict_types=1);

final class QueryPasienReservasi {
    // Daftar reservasi aktif dan riwayat milik pasien yang login
    public static function daftar(int $pasienId): array
    {
        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap.', 403);
        }

        $sql = "
            SELECT r.id, r.no_reservasi, r.status, r.reschedule_count,
                   r.keluhan, j.tanggal::text AS tanggal,
                   j.jam_mulai::text AS jam_mulai, j.jam_selesai::text AS jam_selesai,
                   po.nama AS poli, COALESCE(d.nama, 'Dokter akan ditentukan') AS dokter,
                   ja.tanggal::text AS tanggal_awal
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            LEFT JOIN jadwal ja ON ja.id = r.jadwal_awal_id
            WHERE r.pasien_id = :pid
        ";

        $aktif = db()->prepare($sql . "
            AND r.status NOT IN ('dibatalkan', 'selesai', 'tidak_hadir')
            ORDER BY j.tanggal ASC, j.jam_mulai ASC LIMIT 20
        ");
        $aktif->execute([':pid' => $pasienId]);

        $riwayat = db()->prepare($sql . "
            AND r.status IN ('dibatalkan', 'selesai', 'tidak_hadir')
            ORDER BY j.tanggal DESC, j.jam_mulai DESC LIMIT 20
        ");
        $riwayat->execute([':pid' => $pasienId]);

        return [
            'akan_datang' => self::rapikan($aktif->fetchAll()),
            'riwayat'     => self::rapikan($riwayat->fetchAll()),
        ];
    }

    // Detail satu reservasi, dengan pengecekan kepemilikan
    public static function detail(int $id, int $pasienId): array
    {
        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap.', 403);
        }
        if ($id <= 0) {
            throw new ApiException('Reservasi tidak ditemukan.', 404);
        }

        $stmt = db()->prepare("
            SELECT r.id, r.no_reservasi, r.status, r.reschedule_count, r.keluhan,
                   j.tanggal::text AS tanggal,
                   j.jam_mulai::text AS jam_mulai, j.jam_selesai::text AS jam_selesai,
                   po.nama AS poli, COALESCE(d.nama, 'Dokter akan ditentukan') AS dokter
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE r.id = :id AND r.pasien_id = :pid
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':pid' => $pasienId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new ApiException('Reservasi tidak ditemukan.', 404);
        }
        $row['status_label'] = FormatPasien::labelStatus($row['status'], (int) $row['reschedule_count']);
        $row['tanggal_panjang'] = FormatPasien::tanggalPanjang($row['tanggal']);
        $row['jam'] = FormatPasien::jam($row['jam_mulai']);
        return $row;
    }

    private static function rapikan(array $baris): array
    {
        foreach ($baris as &$row) {
            $row['status_label'] = FormatPasien::labelStatus($row['status'], (int) $row['reschedule_count']);
            $row['badge_color'] = FormatPasien::warnaStatus($row['status'], (int) $row['reschedule_count']);
            $row['jadwal'] = FormatPasien::tanggalPendek($row['tanggal']) . ', ' . FormatPasien::jam($row['jam_mulai']);
            $row['note'] = (int) $row['reschedule_count'] > 0 && $row['tanggal_awal'] !== null
                ? 'Dipindah dari ' . FormatPasien::tanggalPendek($row['tanggal_awal'])
                : null;
        }
        return $baris;
    }
}
