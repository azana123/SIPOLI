<?php
declare(strict_types=1);

final class QueryPasienNotifikasi {
    // Jumlah notifikasi belum dibaca, untuk badge lonceng pasien
    public static function belumDibaca(int $userId): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM inbox WHERE user_id = :uid AND dibaca_at IS NULL');
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    // Notifikasi ringkas untuk beranda: field sama seperti versi lama
    public static function ringkas(int $userId, int $limit = 5): array
    {
        $stmt = db()->prepare("
            SELECT id, tipe, judul, pesan, butuh_aksi, ref_tipe, ref_id, dibaca_at, created_at
            FROM inbox
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT {$limit}
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    // Daftar notifikasi inbox milik user yang login
    public static function daftar(int $userId): array
    {
        $stmt = db()->prepare("
            SELECT id, tipe, judul, pesan, butuh_aksi, aksi_selesai_at,
                   ref_tipe, ref_id, dibaca_at, created_at
            FROM inbox
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':uid' => $userId]);
        $baris = $stmt->fetchAll();
        foreach ($baris as &$row) {
            $row['waktu'] = FormatPasien::waktuRelatif($row['created_at']);
            $row['belum_dibaca'] = $row['dibaca_at'] === null;
        }
        return $baris;
    }
}
