<?php
declare(strict_types=1);

final class QueryDokterNavbar {
    public static function dokNav(array $params) {
        $stmt = db()->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id, p.nama AS nama_poli, p.butuh_reservasi
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function rowTimeNav() {
        return db()->query("
            SELECT fn_today()::text AS tgl,
                   TO_CHAR((NOW() AT TIME ZONE 'Asia/Jakarta'), 'HH24.MI') AS jam
        ")->fetch();
    }
}
