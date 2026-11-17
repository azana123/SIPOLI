<?php
declare(strict_types=1);

final class QueryAdminObat {
    public static function todayDb() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function st() {
        return db()->query("
        SELECT 
            COUNT(*) AS total,
            COUNT(*) FILTER (WHERE is_active = TRUE) AS aktif,
            COUNT(*) FILTER (WHERE is_active = FALSE) AS nonaktif,
            COUNT(*) FILTER (WHERE is_active = TRUE AND stok = 0) AS habis,
            COUNT(*) FILTER (WHERE is_active = TRUE AND stok > 0 AND stok <= stok_minimum) AS menipis,
            (SELECT COUNT(*) FROM v_obat_perlu_perhatian) AS perlu_perhatian,
            MAX(updated_at) AS last_upd
        FROM obat
    ")->fetch();
    }

    public static function listBentuk() {
        return db()->query("
        SELECT DISTINCT lower(bentuk) AS bentuk
        FROM obat
        WHERE bentuk IS NOT NULL AND btrim(bentuk) <> ''
        ORDER BY 1 ASC
    ")->fetchAll();
    }

    public static function det(array $params) {
        $stmt = db()->prepare("SELECT * FROM obat WHERE id = :id");
        $stmt->execute($params);
        return $stmt;
    }

    public static function log(array $params) {
        $stmt = db()->prepare("
                SELECT l.*, a.nama AS nama_admin
                FROM log_stok_obat l
                LEFT JOIN admin a ON a.id = l.admin_id
                WHERE l.obat_id = :id
                ORDER BY l.created_at DESC
                LIMIT 5
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function stok(array $params) {
        $stmt = db()->prepare("SELECT * FROM obat WHERE id = :id");
        $stmt->execute($params);
        return $stmt;
    }
}
