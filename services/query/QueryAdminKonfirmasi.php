<?php
declare(strict_types=1);

final class QueryAdminKonfirmasi {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function st(array $params) {
        $stmt = db()->prepare("
        SELECT 
            COUNT(*) FILTER (WHERE r.status <> 'dibatalkan') AS total,
            COUNT(*) FILTER (WHERE r.status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE r.status = 'dikonfirmasi') AS hadir,
            COUNT(*) FILTER (WHERE r.status IN ('dibatalkan', 'tidak_hadir')) AS batal
        FROM reservasi r
        JOIN jadwal j ON j.id = r.jadwal_id
        JOIN poli po  ON po.id = j.poli_id
        WHERE j.tanggal = :besok AND po.nama ILIKE '%konseling%'
    ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function m(array $params) {
        $stmt = db()->prepare("
            SELECT r.id AS reservasi_id, r.no_reservasi, p.nama AS nama_pasien, p.nim_nip, p.status_civitas,
                   po.nama AS nama_poli, j.tanggal, j.jam_mulai, j.jam_selesai
            FROM reservasi r
            JOIN pasien p ON p.id = r.pasien_id
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po  ON po.id = j.poli_id
            WHERE r.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }
}
