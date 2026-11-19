<?php
declare(strict_types=1);

final class QueryPasienDokter {
    // Baris dokter aktif mentah beserta jam praktiknya
    public static function mentah(int $limit = 6): array {
        return db()->query("
            SELECT d.id, d.nama, d.profesi, po.nama AS poli,
                   MIN(j.jam_mulai)::text AS jam_mulai,
                   MAX(j.jam_selesai)::text AS jam_selesai
            FROM dokter d
            JOIN poli po ON po.id = d.poli_id
            LEFT JOIN jadwal j ON j.dokter_id = d.id AND j.tanggal >= fn_today() AND j.status <> 'tutup'
            WHERE d.is_active = TRUE
            GROUP BY d.id, d.nama, d.profesi, po.nama
            ORDER BY d.id
            LIMIT {$limit}
        ")->fetchAll();
    }

    // Daftar dokter aktif untuk ditampilkan di beranda publik
    public static function daftar(): array
    {
        $baris = self::mentah();
        foreach ($baris as &$row) {
            $row['jam'] = $row['jam_mulai'] !== null
                ? FormatPasien::jam($row['jam_mulai']) . ' – ' . FormatPasien::jam($row['jam_selesai'])
                : 'Jadwal belum tersedia';
        }
        return $baris;
    }
}
