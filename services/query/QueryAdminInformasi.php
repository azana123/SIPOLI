<?php
declare(strict_types=1);

final class QueryAdminInformasi {
    public static function riwayatInfo() {
        return db()->query("
        SELECT i.judul, i.pesan, i.created_at, COUNT(i.id) AS jumlah_penerima
        FROM inbox i
        WHERE i.ref_tipe = 'informasi'
        GROUP BY i.judul, i.pesan, i.created_at
        ORDER BY i.created_at DESC
        LIMIT 10
    ")->fetchAll();
    }
}
