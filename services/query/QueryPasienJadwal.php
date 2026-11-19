<?php
declare(strict_types=1);

final class QueryPasienJadwal {
    // Baris jadwal mentah dari view, opsional filter tanggal, poli, dan batas
    public static function mentah(?string $tanggal = null, int $poliId = 0, int $limit = 0): array {
        $sql = "
            SELECT id, poli, dokter, tanggal::text AS tanggal,
                   jam_mulai::text AS jam_mulai, jam_selesai::text AS jam_selesai,
                   sisa_kuota
            FROM v_jadwal_tersedia
            WHERE 1 = 1
        ";
        $params = [];
        if ($tanggal !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $sql .= ' AND tanggal = :tanggal';
            $params[':tanggal'] = $tanggal;
        }
        if ($poliId > 0) {
            $sql .= ' AND poli_id = :poli';
            $params[':poli'] = $poliId;
        }
        $sql .= ' ORDER BY tanggal ASC, jam_mulai ASC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Daftar jadwal yang bisa dipesan, opsional filter tanggal dan poli
    public static function daftar(?string $tanggal = null, int $poliId = 0): array {
        $baris = self::mentah($tanggal, $poliId);

        foreach ($baris as &$row) {
            $row['penuh'] = (int) $row['sisa_kuota'] <= 0;
            $row['tanggal_pendek'] = FormatPasien::tanggalPendek($row['tanggal']);
            $row['jam'] = FormatPasien::jam($row['jam_mulai']) . ' – ' . FormatPasien::jam($row['jam_selesai']);
        }
        return $baris;
    }

    // Peta tanggal dalam satu bulan: tersedia / penuh
    public static function kalender(int $bulan, int $tahun): array
    {
        $awal = sprintf('%04d-%02d-01', $tahun, $bulan);
        $akhir = date('Y-m-t', strtotime($awal));
        $stmt = db()->prepare("
            SELECT tanggal::text AS tanggal, SUM(sisa_kuota)::int AS sisa
            FROM v_jadwal_tersedia
            WHERE tanggal BETWEEN :awal AND :akhir
            GROUP BY tanggal
            ORDER BY tanggal ASC
        ");
        $stmt->execute([':awal' => $awal, ':akhir' => $akhir]);
        $peta = [];
        foreach ($stmt->fetchAll() as $row) {
            $peta[(int) date('j', strtotime($row['tanggal']))] = (int) $row['sisa'] <= 0 ? 'penuh' : 'tersedia';
        }
        return $peta;
    }
}
