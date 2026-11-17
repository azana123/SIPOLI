<?php
declare(strict_types=1);

final class QueryAdminDatamaster {
    public static function super(array $params)
    {
        $stmt = db()->prepare("SELECT is_super FROM admin WHERE id = :id");
        $stmt->execute($params);
        return $stmt;
    }

    public static function stats() {
        return db()->query("
        SELECT 
            (
                SELECT COUNT(*)
                FROM admin a
                JOIN users u ON a.user_id = u.id
                WHERE u.is_active = TRUE
            ) AS total_admin,
            (
                SELECT COUNT(*)
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.is_active = TRUE AND (u.id IS NULL OR u.is_active = TRUE)
            ) AS total_pasien,
            (
                SELECT COUNT(*)
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                WHERE d.is_active = TRUE AND u.is_active = TRUE
            ) AS total_dokter,
            (
                SELECT COUNT(*)
                FROM poli
            ) AS total_poli
    ")->fetch();
    }

    public static function det(array $params) {
        $stmt = db()->prepare("
                SELECT p.*, u.username, (p.is_active AND COALESCE(u.is_active, TRUE)) AS status_aktif
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function ed(array $params) {
        $stmt = db()->prepare("
                SELECT p.*, u.username
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function listPoli() {
        return db()->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();
    }

    public static function det2(array $params) {
        $stmt = db()->prepare("
                SELECT d.*, po.nama AS nama_poli, u.username, (d.is_active AND u.is_active) AS status_aktif
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                JOIN poli po ON d.poli_id = po.id
                WHERE d.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function ed2(array $params) {
        $stmt = db()->prepare("
                SELECT d.*, u.username
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                WHERE d.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function ed3(array $params) {
        $stmt = db()->prepare("SELECT * FROM poli WHERE id = :id");
        $stmt->execute($params);
        return $stmt;
    }

    public static function det3(array $params) {
        $stmt = db()->prepare("
                SELECT a.*, u.username, u.is_active AS status_aktif,
                       dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin, dc.unit
                FROM admin a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
                WHERE a.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }

    public static function ed4(array $params) {
        $stmt = db()->prepare("
                SELECT a.*, u.username, dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin
                FROM admin a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
                WHERE a.id = :id
            ");
        $stmt->execute($params);
        return $stmt;
    }
}
