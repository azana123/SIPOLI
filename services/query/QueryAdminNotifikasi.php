<?php
declare(strict_types=1);

final class QueryAdminNotifikasi {
    public static function stmt(array $params) {
        $stmt = db()->prepare("
        SELECT id, tipe, judul, pesan, ref_tipe, ref_id, dibaca_at, created_at
        FROM inbox
        WHERE user_id = :uid
        ORDER BY created_at DESC
        LIMIT 30
    ");
        $stmt->execute($params);
        return $stmt;
    }
}
