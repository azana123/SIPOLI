<?php
declare(strict_types=1);

final class QueryDokterPerubahanJadwal {
    public static function todayModalStr() {
        return db()->query("SELECT fn_today()::text")->fetchColumn();
    }

    public static function opsi(array $params) {
        $stmt = db()->prepare("
            SELECT j.id, j.tanggal, j.jam_mulai, j.jam_selesai, p.nama AS nama_poli
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            WHERE j.dokter_id = :dok_id
              AND j.tanggal >= fn_today()
              AND j.status <> 'tutup'
            ORDER BY j.tanggal ASC, j.jam_mulai ASC
            LIMIT 14
        ");
        $stmt->execute($params);
        return $stmt;
    }
}
