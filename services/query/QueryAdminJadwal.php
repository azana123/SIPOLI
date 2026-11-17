<?php
declare(strict_types=1);

final class QueryAdminJadwal {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function listPoli() {
        return db()->query("
        SELECT id, nama, butuh_reservasi, kuota_harian, durasi_menit
        FROM poli
        ORDER BY id ASC
    ")->fetchAll();
    }

    public static function listDokter() {
        return db()->query("
        SELECT d.id, d.poli_id, d.nama, d.profesi, p.nama AS nama_poli
        FROM dokter d
        JOIN users u ON d.user_id = u.id
        JOIN poli p  ON d.poli_id = p.id
        WHERE d.is_active = TRUE AND u.is_active = TRUE
        ORDER BY d.poli_id ASC, d.nama ASC
    ")->fetchAll();
    }

    public static function stats(array $params) {
        $stmt = db()->prepare("
        SELECT 
            j.id, j.kuota, j.status, j.dipublikasi, p.nama AS nama_poli, p.butuh_reservasi,
            (SELECT COUNT(*) FROM reservasi r WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS rsv_aktif
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        WHERE j.tanggal = :tanggal AND j.status <> 'tutup'
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function detail(array $params) {
        $stmt = db()->prepare("
            SELECT 
                j.id, j.poli_id, p.nama AS nama_poli, p.butuh_reservasi, p.kuota_harian, p.durasi_menit,
                j.dokter_id, d.nama AS nama_dokter, d.profesi,
                j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, j.status, j.dipublikasi,
                (
                    SELECT COUNT(*) FROM reservasi r
                    WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan'
                ) AS terisi_online,
                (
                    SELECT COUNT(*) FROM antrean a
                    WHERE a.jadwal_id = j.id AND a.reservasi_id IS NULL AND a.status <> 'batal'
                ) AS terisi_offline_jadwal,
                (
                    SELECT COUNT(*) FROM antrean a
                    WHERE a.poli_id = j.poli_id AND a.tanggal = j.tanggal AND a.reservasi_id IS NULL AND a.status <> 'batal'
                ) AS terisi_walkin_poli
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE j.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function pasien(array $params) {
        $stmt = db()->prepare("
                    SELECT 
                        ps.nama AS nama_pasien,
                        ps.nim_nip,
                        r.no_reservasi AS kode_tiket,
                        'Reservasi Online' AS sumber,
                        r.status
                    FROM reservasi r
                    JOIN pasien ps ON ps.id = r.pasien_id
                    WHERE r.jadwal_id = :jadwal_id
                    ORDER BY r.created_at ASC
                ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function pasien2(array $params) {
        $stmt = db()->prepare("
                    SELECT 
                        ps.nama AS nama_pasien,
                        ps.nim_nip,
                        concat('ANT-', lpad(a.no_antrean::text, 3, '0')) AS kode_tiket,
                        CASE WHEN a.reservasi_id IS NOT NULL THEN 'Reservasi Online' ELSE 'Kunjungan Langsung' END AS sumber,
                        a.status
                    FROM antrean a
                    JOIN pasien ps ON ps.id = a.pasien_id
                    WHERE a.poli_id = :poli_id AND a.tanggal = :tanggal
                    ORDER BY a.no_antrean ASC
                ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function edit(array $params) {
        $stmt = db()->prepare("
            SELECT j.*, p.butuh_reservasi, p.kuota_harian
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            WHERE j.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }
}
