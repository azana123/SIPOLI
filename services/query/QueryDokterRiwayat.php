<?php
declare(strict_types=1);

final class QueryDokterRiwayat {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

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

    public static function aktif(array $params) {
        $stmt = db()->prepare("
        SELECT a.id AS antrean_id, a.no_antrean, ps.nama AS nama_pasien,
               COALESCE(j.jam_mulai, (a.waktu_daftar AT TIME ZONE 'Asia/Jakarta')::time) AS jam_kunjungan,
               COALESCE(r.keluhan, 'Pemeriksaan langsung') AS keluhan_awal
        FROM antrean a
        JOIN pasien ps ON ps.id = a.pasien_id
        LEFT JOIN jadwal j    ON j.id = a.jadwal_id
        LEFT JOIN reservasi r ON r.id = a.reservasi_id
        WHERE a.tanggal = :today
          AND a.poli_id = :poli_id
          AND (a.dokter_id = :dok_id OR a.dokter_id IS NULL)
          AND a.status = 'diperiksa'
        ORDER BY a.no_antrean ASC
        LIMIT 1
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function listPoli() {
        return db()->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();
    }

    public static function psList(array $params) {
        $stmt = db()->prepare("
        SELECT DISTINCT ps.id, ps.nama, ps.nim_nip
        FROM pemeriksaan pm
        JOIN pasien ps ON ps.id = pm.pasien_id
        WHERE pm.dokter_id = :dok_id OR pm.poli_id = :poli_id
        ORDER BY ps.nama ASC
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
                pol.nama AS nama_poli,
                d.nama AS nama_dokter,
                a.reservasi_id,
                po.id AS permintaan_obat_id,
                po.status AS status_resep,
                po.waktu_diserahkan,
                po.created_at AS waktu_resep,
                (SELECT COUNT(*) FROM resep_obat ro WHERE ro.pemeriksaan_id = pm.id) AS jumlah_jenis_obat,
                (SELECT COUNT(*) FROM pemeriksaan p2 WHERE p2.pasien_id = pm.pasien_id) AS total_kunjungan_pasien
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN poli pol  ON pol.id = pm.poli_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            JOIN antrean a ON a.id  = pm.antrean_id
            LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
            WHERE pm.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }
}
