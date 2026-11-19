<?php
declare(strict_types=1);

final class QueryPasienBeranda {
    // Semua data yang dibutuhkan Beranda pasien, dikirim sekaligus
    public static function ringkasan(int $pasienId, int $userId): array {
        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap. Hubungi admin.', 403);
        }

        $profil = db()->prepare('SELECT id, nama, nim_nip, status_civitas FROM pasien WHERE id = :id');
        $profil->execute([':id' => $pasienId]);
        $pasien = $profil->fetch();

        $reservasi = db()->prepare("
            SELECT r.id, r.no_reservasi, r.status, r.reschedule_count,
                   po.nama AS poli, COALESCE(d.nama, 'Belum ditentukan') AS dokter,
                   j.tanggal::text AS tanggal, j.jam_mulai::text AS jam_mulai,
                   j.jam_selesai::text AS jam_selesai
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE r.pasien_id = :pid AND r.status NOT IN ('dibatalkan', 'selesai', 'tidak_hadir')
            ORDER BY j.tanggal, j.jam_mulai
            LIMIT 5
        ");
        $reservasi->execute([':pid' => $pasienId]);

        $riwayat = db()->prepare("
            SELECT r.id, r.status, po.nama AS poli, j.tanggal::text AS tanggal
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            WHERE r.pasien_id = :pid AND r.status IN ('selesai', 'dibatalkan', 'tidak_hadir')
            ORDER BY j.tanggal DESC
            LIMIT 5
        ");
        $riwayat->execute([':pid' => $pasienId]);

        $notif = QueryPasienNotifikasi::ringkas($userId, 5);

        $belum = db()->prepare('SELECT COUNT(*) FROM inbox WHERE user_id = :uid AND dibaca_at IS NULL');
        $belum->execute([':uid' => $userId]);

        $jadwal = QueryPasienJadwal::mentah(null, 0, 12);
        $dokter = QueryPasienDokter::mentah(10);

        return [
            'profil'           => $pasien,
            'reservasi_aktif'  => $reservasi->fetchAll(),
            'riwayat'          => $riwayat->fetchAll(),
            'notifikasi'       => $notif,
            'notifikasi_belum' => (int) $belum->fetchColumn(),
            'jadwal_tersedia'  => $jadwal,
            'dokter'           => $dokter,
        ];
    }
}
