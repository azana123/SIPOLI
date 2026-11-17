<?php
declare(strict_types=1);

final class QueryAdminNavbar {
    public static function admNav(array $params) {
        $stmt = db()->prepare("SELECT id, nama, nim_nip, is_super, no_hp FROM admin WHERE id = :id");
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
