<?php
declare(strict_types=1);

final class QueryDokterDetailResep {
    public static function dok(array $params) {
        $stmt = db()->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id,
                   p.nama AS nama_poli, p.butuh_reservasi
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function listRsp(array $params) {
        $stmt = db()->prepare("
        SELECT pm.id AS pemeriksaan_id, pm.tanggal, pm.created_at,
               ps.nama AS nama_pasien, ps.nim_nip,
               po.id AS permintaan_id, po.status AS status_resep
        FROM pemeriksaan pm
        JOIN pasien ps ON ps.id = pm.pasien_id
        LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
        WHERE (pm.dokter_id = :dok_id OR pm.poli_id = :poli_id)
          AND EXISTS (SELECT 1 FROM resep_obat ro WHERE ro.pemeriksaan_id = pm.id)
        ORDER BY pm.created_at DESC
        LIMIT 30
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function det(array $params) {
        $stmt = db()->prepare("
            SELECT 
                pm.*,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                d.nama AS nama_dokter,
                pol.nama AS nama_poli,
                a.reservasi_id,
                po.id AS permintaan_id,
                po.status AS status_resep,
                po.catatan AS catatan_apotek,
                po.waktu_diserahkan,
                po.created_at AS waktu_permintaan
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            JOIN poli pol  ON pol.id = pm.poli_id
            JOIN antrean a ON a.id  = pm.antrean_id
            LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
            WHERE pm.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function items(array $params) {
        $stmt = db()->prepare("
                SELECT ro.id, ro.jumlah, ro.aturan_pakai,
                       o.kode_obat, o.nama_obat, o.bentuk, o.satuan, o.keterangan
                FROM resep_obat ro
                JOIN obat o ON o.id = ro.obat_id
                WHERE ro.pemeriksaan_id = :pem_id
                ORDER BY ro.id ASC
            ");
        $stmt->execute($params);
        return $stmt;
    }
}
