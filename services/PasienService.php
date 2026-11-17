<?php
declare(strict_types=1);

final class PasienService {
    // Semua data yang dibutuhkan Beranda pasien, dikirim sekaligus
    public static function ringkasan(): array {
        $uid      = (int) ($_SESSION['user_id'] ?? 0);
        $pasienId = profil_id_sesi('pasien');
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

        $notif = db()->prepare("
            SELECT id, tipe, judul, pesan, butuh_aksi, ref_tipe, ref_id, dibaca_at, created_at
            FROM inbox WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5
        ");
        $notif->execute([':uid' => $uid]);

        $belum = db()->prepare('SELECT COUNT(*) FROM inbox WHERE user_id = :uid AND dibaca_at IS NULL');
        $belum->execute([':uid' => $uid]);

        $jadwal = db()->query("
            SELECT id, poli, dokter, tanggal::text AS tanggal,
                   jam_mulai::text AS jam_mulai, jam_selesai::text AS jam_selesai, sisa_kuota
            FROM v_jadwal_tersedia
            ORDER BY tanggal, jam_mulai
            LIMIT 12
        ")->fetchAll();

        $dokter = db()->query("
            SELECT d.id, d.nama, d.profesi, po.nama AS poli,
                   MIN(j.jam_mulai)::text AS jam_mulai,
                   MAX(j.jam_selesai)::text AS jam_selesai
            FROM dokter d
            JOIN poli po ON po.id = d.poli_id
            LEFT JOIN jadwal j ON j.dokter_id = d.id AND j.tanggal >= fn_today() AND j.status <> 'tutup'
            WHERE d.is_active = TRUE
            GROUP BY d.id, d.nama, d.profesi, po.nama
            ORDER BY d.id
            LIMIT 10
        ")->fetchAll();

        return [
            'profil'           => $pasien,
            'reservasi_aktif'  => $reservasi->fetchAll(),
            'riwayat'          => $riwayat->fetchAll(),
            'notifikasi'       => $notif->fetchAll(),
            'notifikasi_belum' => (int) $belum->fetchColumn(),
            'jadwal_tersedia'  => $jadwal,
            'dokter'           => $dokter,
        ];
    }
}
