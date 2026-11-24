<?php
declare(strict_types=1);

final class QueryDokterDashboard {
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
        WHERE dokter_id = :dok_id AND tanggal = fn_today() AND status <> 'tutup'
        ORDER BY jam_mulai ASC
        LIMIT 1
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function list(array $params) {
        $stmt = db()->prepare("
        SELECT 
            a.id AS antrean_id,
            a.no_antrean,
            a.status,
            a.waktu_daftar,
            a.waktu_mulai,
            a.waktu_selesai,
            ps.id AS pasien_id,
            ps.nama AS nama_pasien,
            ps.nim_nip,
            ps.status_civitas,
            COALESCE(j.jam_mulai, (a.waktu_daftar AT TIME ZONE 'Asia/Jakarta')::time) AS jam_kunjungan,
            COALESCE(pm.data_pemeriksaan->>'keluhan', r.keluhan, 'Pemeriksaan langsung') AS keluhan_awal,
            pm.id AS pemeriksaan_id
        FROM antrean a
        JOIN pasien ps ON ps.id = a.pasien_id
        LEFT JOIN jadwal j      ON j.id = a.jadwal_id
        LEFT JOIN reservasi r   ON r.id = a.reservasi_id
        LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
        WHERE a.tanggal = fn_today()
          AND a.poli_id = :poli_id
          AND (a.dokter_id = :dok_id OR a.dokter_id IS NULL)
          AND a.status <> 'batal'
        ORDER BY 
            CASE a.status
                WHEN 'diperiksa' THEN 1
                WHEN 'menunggu'  THEN 2
                WHEN 'selesai'   THEN 3
                ELSE 4
            END ASC,
            a.no_antrean ASC
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function rsp(array $params) {
        $stmt = db()->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE po.status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE po.status IN ('disiapkan', 'diserahkan')) AS diproses
        FROM permintaan_obat po
        JOIN pemeriksaan pm ON pm.id = po.pemeriksaan_id
        WHERE pm.dokter_id = :dok_id AND pm.tanggal = fn_today()
    ");
        $stmt->execute($params);
        return $stmt;
    }
}
