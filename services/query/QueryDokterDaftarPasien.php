<?php
declare(strict_types=1);

final class QueryDokterDaftarPasien {
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

    public static function jdw(array $params) {
        $stmt = db()->prepare("
        SELECT id, tanggal, jam_mulai, jam_selesai, kuota, status
        FROM jadwal
        WHERE dokter_id = :dok_id AND tanggal = :tgl AND status <> 'tutup'
        ORDER BY jam_mulai ASC
        LIMIT 1
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function stat(array $params) {
        $stmt = db()->prepare("
        SELECT 
            COUNT(*) AS total,
            COUNT(*) FILTER (WHERE a.reservasi_id IS NOT NULL) AS online_cnt,
            COUNT(*) FILTER (WHERE a.reservasi_id IS NULL) AS offline_cnt,
            COUNT(*) FILTER (WHERE a.status = 'menunggu') AS menunggu_cnt,
            COUNT(*) FILTER (WHERE a.status = 'diperiksa') AS diperiksa_cnt,
            COUNT(*) FILTER (WHERE a.status = 'selesai') AS selesai_cnt
        FROM antrean a
        WHERE a.tanggal = :tgl
          AND a.poli_id = :poli_id
          AND (a.dokter_id = :dok_id OR a.dokter_id IS NULL)
          AND a.status <> 'batal'
    ");
        $stmt->execute($params);
        return $stmt;
    }
}
