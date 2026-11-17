<?php
declare(strict_types=1);

final class QueryAdminResep {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function stat(array $params) {
        $stmt = db()->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE po.status <> 'dibatalkan') AS total_hari_ini,
            COUNT(*) FILTER (WHERE po.status = 'menunggu')    AS total_menunggu,
            COUNT(*) FILTER (WHERE po.status = 'disiapkan')   AS total_diproses,
            COUNT(*) FILTER (WHERE po.status = 'diserahkan')  AS total_selesai
        FROM permintaan_obat po
        JOIN pemeriksaan pm ON pm.id = po.pemeriksaan_id
        WHERE pm.tanggal = :tanggal
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function det(array $params) {
        $stmt = db()->prepare("
            SELECT 
                po.id AS permintaan_id,
                concat('RSP-', lpad(po.id::text, 3, '0')) AS kode_resep,
                po.status,
                po.catatan AS catatan_apotek,
                po.created_at,
                po.waktu_diserahkan,
                adm.nama AS nama_admin,
                pm.id AS pemeriksaan_id,
                pm.tanggal,
                pm.diagnosa,
                pm.catatan AS catatan_dokter,
                ps.id AS pasien_id,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.riwayat_alergi,
                d.nama AS nama_dokter,
                pol.nama AS nama_poli
            FROM permintaan_obat po
            JOIN pemeriksaan pm ON pm.id  = po.pemeriksaan_id
            JOIN pasien ps      ON ps.id  = pm.pasien_id
            JOIN dokter d       ON d.id   = pm.dokter_id
            JOIN poli pol       ON pol.id = pm.poli_id
            LEFT JOIN admin adm ON adm.id = po.diproses_oleh
            WHERE po.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function items(array $params) {
        $stmt = db()->prepare("
                SELECT 
                    ro.id AS resep_obat_id,
                    ro.jumlah,
                    ro.aturan_pakai,
                    o.id AS obat_id,
                    o.kode_obat,
                    o.nama_obat,
                    o.bentuk,
                    o.satuan,
                    o.stok AS stok_saat_ini,
                    lso.stok_sebelum,
                    lso.stok_sesudah
                FROM resep_obat ro
                JOIN obat o ON o.id = ro.obat_id
                LEFT JOIN LATERAL (
                    SELECT stok_sebelum, stok_sesudah
                    FROM log_stok_obat l
                    WHERE l.resep_obat_id = ro.id
                    ORDER BY l.id DESC
                    LIMIT 1
                ) lso ON TRUE
                WHERE ro.pemeriksaan_id = :pem_id
                ORDER BY ro.id ASC
            ");
        $stmt->execute($params);
        return $stmt;
    }
}
