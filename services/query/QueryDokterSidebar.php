<?php
declare(strict_types=1);

final class QueryDokterSidebar {
    public static function dokSb(array $params) {
        $stmt = db()->prepare("
            SELECT d.id, d.nama, d.profesi, d.no_str, d.no_hp, d.poli_id, p.nama AS nama_poli, p.butuh_reservasi
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            WHERE d.id = :id
        ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function admKontak() {
        return db()->query("SELECT nama, no_hp FROM fn_kontak_admin() LIMIT 1")->fetch();
    }
}
