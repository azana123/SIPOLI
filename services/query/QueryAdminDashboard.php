<?php
declare(strict_types=1);

final class QueryAdminDashboard {
    public static function stats()
    {
        return db()->query("
        SELECT
            (
                SELECT COUNT(*)
                FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id
                WHERE j.tanggal = fn_today() AND r.status <> 'dibatalkan'
            ) AS reservasi_hari_ini,
            (
                SELECT COUNT(*)
                FROM reservasi r JOIN jadwal j ON j.id = r.jadwal_id
                WHERE j.tanggal = fn_today() + 1 AND r.status = 'menunggu'
            ) AS konfirmasi_h1,
            (
                SELECT COUNT(*)
                FROM permintaan_obat 
                WHERE status = 'menunggu'
            ) AS resep_menunggu
    ")->fetch();
    }

    public static function kuotaPerPoli() {
        return db()->query("
        SELECT po.nama,
               COALESCE(SUM(x.kuota), 0)    AS kuota,
               COALESCE(SUM(x.terpakai), 0) AS terpakai
          FROM poli po
          LEFT JOIN (
                SELECT j.poli_id, j.kuota,
                       (SELECT COUNT(*) FROM reservasi r
                         WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') AS terpakai
                  FROM jadwal j
                 WHERE j.tanggal = fn_today() AND j.status <> 'tutup'
          ) x ON x.poli_id = po.id
         WHERE po.butuh_reservasi
         GROUP BY po.id, po.nama
         ORDER BY po.nama
    ")->fetchAll();
    }

    public static function listReservasi() {
        return db()->query("
        SELECT r.id, r.no_reservasi, r.status, r.reschedule_count,
               p.nama   AS nama_pasien,
               pol.nama AS nama_poli,
               j.jam_mulai, j.jam_selesai
          FROM reservasi r
          JOIN pasien p   ON p.id   = r.pasien_id
          JOIN jadwal j   ON j.id   = r.jadwal_id
          JOIN poli   pol ON pol.id = j.poli_id
         WHERE j.tanggal = fn_today() AND r.status <> 'dibatalkan'
         ORDER BY j.jam_mulai, r.created_at
         LIMIT 5
    ")->fetchAll();
    }
}
