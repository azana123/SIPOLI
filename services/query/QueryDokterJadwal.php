<?php
declare(strict_types=1);

final class QueryDokterJadwal {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function dok(array $params) {
        $stmt = db()->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id,
                   p.nama AS nama_poli, p.butuh_reservasi, p.kuota_harian
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function today(array $params) {
        $stmt = db()->prepare("
        SELECT id, tanggal, jam_mulai, jam_selesai, kuota, status
        FROM jadwal
        WHERE dokter_id = :dok_id AND tanggal = :today AND status <> 'tutup'
        ORDER BY jam_mulai ASC
        LIMIT 1
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function next(array $params) {
        $stmt = db()->prepare("
        SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, p.nama AS nama_poli,
               (SELECT COUNT(*) FROM reservasi r WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS terisi_rsv,
               (SELECT COUNT(*) FROM antrean a WHERE a.jadwal_id = j.id AND a.status <> 'batal') AS terisi_ant
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        WHERE j.dokter_id = :dok_id AND j.tanggal > :today AND j.status <> 'tutup'
        ORDER BY j.tanggal ASC, j.jam_mulai ASC
        LIMIT 1
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function list(array $params) {
        $stmt = db()->prepare("
        SELECT 
            j.id, j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, j.status,
            p.nama AS nama_poli, p.butuh_reservasi,
            EXTRACT(EPOCH FROM (j.jam_selesai - j.jam_mulai)) / 3600.0 AS durasi_jam,
            (SELECT COUNT(*) FROM reservasi r WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS terisi_rsv,
            (SELECT COUNT(*) FROM antrean a WHERE (a.jadwal_id = j.id OR (a.jadwal_id IS NULL AND a.poli_id = j.poli_id AND a.tanggal = j.tanggal)) AND a.status <> 'batal') AS terisi_ant
        FROM jadwal j
        JOIN poli p ON p.id = j.poli_id
        WHERE j.dokter_id = :dok_id
          AND j.tanggal BETWEEN :tgl_mulai AND :tgl_selesai
          AND j.status <> 'tutup'
        ORDER BY j.tanggal ASC, j.jam_mulai ASC
    ");
        $stmt->execute($params);
        return $stmt;
    }
}
