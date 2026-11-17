<?php
declare(strict_types=1);

final class QueryAdminSidebar {
    public static function admSb(array $params) {
        $stmt = db()->prepare("SELECT id, nama, nim_nip, is_super, no_hp FROM admin WHERE id = :id");
        $stmt->execute($params);
        return $stmt;
    }

    public static function badge(array $params) {
        $stmt = db()->prepare("
            SELECT
                (
                    SELECT COUNT(*)
                    FROM reservasi r
                    JOIN jadwal j ON j.id = r.jadwal_id
                    JOIN poli po  ON po.id = j.poli_id
                    WHERE j.tanggal = fn_today() + 1
                      AND po.nama ILIKE '%konseling%'
                      AND r.status = 'menunggu'
                ) AS konfirmasi_h1,
                (
                    SELECT COUNT(*)
                    FROM permintaan_obat
                    WHERE status = 'menunggu'
                ) AS resep_menunggu,
                (
                    SELECT COUNT(*)
                    FROM inbox
                    WHERE user_id = :uid AND dibaca_at IS NULL
                ) AS notif_unread
        ");
        $stmt->execute($params);
        return $stmt;
    }
}
