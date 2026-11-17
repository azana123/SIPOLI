<?php
declare(strict_types=1);

final class QueryDokterFormPeriksa {
    public static function mulai(array $params) {
        $stmt = db()->prepare("
            UPDATE antrean
               SET status      = 'diperiksa',
                   dokter_id   = COALESCE(dokter_id, :dok_id),
                   waktu_mulai = COALESCE(waktu_mulai, NOW())
             WHERE id = :id AND status = 'menunggu'
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function rsvHadir(array $params) {
        $stmt = db()->prepare("
            UPDATE reservasi
               SET status = 'hadir'
             WHERE id = (SELECT reservasi_id FROM antrean WHERE id = :id)
               AND status = 'dikonfirmasi'
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function dataAnt(array $params) {
        $stmt = db()->prepare("
            SELECT 
                a.id AS antrean_id,
                a.no_antrean,
                a.status,
                a.waktu_daftar,
                ps.id AS pasien_id,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                ps.riwayat_alergi,
                pol.nama AS nama_poli,
                j.jam_mulai,
                r.keluhan AS keluhan_awal
            FROM antrean a
            JOIN pasien ps ON ps.id = a.pasien_id
            JOIN poli pol  ON pol.id = a.poli_id
            LEFT JOIN jadwal j    ON j.id = a.jadwal_id
            LEFT JOIN reservasi r ON r.id = a.reservasi_id
            WHERE a.id = :id AND a.status IN ('menunggu', 'diperiksa')
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function katalogObat() {
        return db()->query("
            SELECT id, kode_obat, nama_obat, COALESCE(bentuk, '-') AS bentuk, satuan, stok
            FROM obat
            WHERE is_active = TRUE AND stok > 0
            ORDER BY nama_obat ASC
        ")->fetchAll();
    }
}
