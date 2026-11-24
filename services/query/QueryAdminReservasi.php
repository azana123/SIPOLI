<?php
declare(strict_types=1);

final class QueryAdminReservasi {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function listPoli() {
        return db()->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();
    }

    public static function cnt(array $params) {
        $stmt = db()->prepare("
        SELECT
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status <> 'dibatalkan') AS online_aktif,
            (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status <> 'batal') AS offline_aktif,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status = 'selesai') AS online_selesai,
            (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status = 'selesai') AS offline_selesai,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status IN ('menunggu', 'dikonfirmasi'))
              + (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status = 'menunggu') AS total_menunggu,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status = 'dikonfirmasi' AND NOT EXISTS (SELECT 1 FROM antrean a WHERE a.reservasi_id = r.id)) AS belum_checkin,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.reschedule_count > 0) AS sudah_reschedule,
            (SELECT COUNT(*) FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id WHERE j.tanggal = :tgl AND r.status IN ('dibatalkan', 'tidak_hadir'))
              + (SELECT COUNT(*) FROM antrean a WHERE a.tanggal = :tgl AND a.reservasi_id IS NULL AND a.status = 'batal') AS total_batal
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function det(array $params) {
        $stmt = db()->prepare("
                SELECT 
                    'online' AS asal,
                    r.id, r.no_reservasi AS kode_tiket, r.status, r.keluhan, r.reschedule_count,
                    r.konfirmasi_diminta_at, r.dikonfirmasi_at, r.dibatalkan_at, r.alasan_batal, r.created_at,
                    p.nama AS nama_pasien, p.nim_nip, p.status_civitas, p.no_hp,
                    po.nama AS nama_poli, po.butuh_reservasi,
                    d.nama AS nama_dokter,
                    j.tanggal, j.jam_mulai, j.jam_selesai,
                    a.id AS antrean_id, a.no_antrean
                FROM reservasi r
                JOIN pasien p ON p.id = r.pasien_id
                JOIN jadwal j ON j.id = r.jadwal_id
                JOIN poli po  ON po.id = j.poli_id
                LEFT JOIN dokter d  ON d.id = j.dokter_id
                LEFT JOIN antrean a ON a.reservasi_id = r.id
                WHERE r.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function det2(array $params) {
        $stmt = db()->prepare("
                SELECT 
                    'offline' AS asal,
                    a.id, concat('ANT-', lpad(a.no_antrean::text, 3, '0')) AS kode_tiket,
                    a.status, pm.data_pemeriksaan->>'keluhan', 0 AS reschedule_count,
                    NULL AS konfirmasi_diminta_at, NULL AS dikonfirmasi_at, NULL AS dibatalkan_at, NULL AS alasan_batal,
                    a.waktu_daftar AS created_at,
                    p.nama AS nama_pasien, p.nim_nip, p.status_civitas, p.no_hp,
                    po.nama AS nama_poli, po.butuh_reservasi,
                    COALESCE(d.nama, jd.nama) AS nama_dokter,
                    a.tanggal, j.jam_mulai, j.jam_selesai,
                    a.id AS antrean_id, a.no_antrean
                FROM antrean a
                JOIN pasien p ON p.id = a.pasien_id
                JOIN poli po  ON po.id = a.poli_id
                LEFT JOIN dokter d       ON d.id = a.dokter_id
                LEFT JOIN jadwal j       ON j.id = a.jadwal_id
                LEFT JOIN dokter jd      ON jd.id = j.dokter_id
                LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
                WHERE a.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function rsc(array $params) {
        $stmt = db()->prepare("
            SELECT r.id, r.no_reservasi, r.jadwal_id, r.status, r.reschedule_count,
                   p.nama AS nama_pasien, p.nim_nip,
                   j.poli_id, po.nama AS nama_poli, j.tanggal, j.jam_mulai, j.jam_selesai
            FROM reservasi r
            JOIN pasien p ON p.id = r.pasien_id
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po  ON po.id = j.poli_id
            WHERE r.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function jdw(array $params) {
        $stmt = db()->prepare("
                SELECT id, dokter, tanggal, jam_mulai, jam_selesai, sisa_kuota
                FROM v_jadwal_tersedia
                WHERE poli_id = :poli_id AND id <> :jadwal_lama AND sisa_kuota > 0
                ORDER BY tanggal ASC, jam_mulai ASC
            ");
        $stmt->execute($params);
        return $stmt;
    }
}
