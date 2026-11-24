<?php
declare(strict_types=1);

final class QueryDaftar {
    public static function datamaster(string $tab, array $f): array {
        $where  = [];
        $params = [];

        if ($tab === 'pasien') {
            if (($f['q'] ?? '') !== '') {
                $where[] = "(p.nama ILIKE :q OR p.nim_nip ILIKE :q OR COALESCE(p.no_hp, '') ILIKE :q OR COALESCE(u.username, '') ILIKE :q)";
                $params[':q'] = '%' . $f['q'] . '%';
            }
            if (in_array($f['civitas'] ?? '', ['mahasiswa', 'dosen', 'staf'], true)) {
                $where[] = "p.status_civitas = :civitas";
                $params[':civitas'] = $f['civitas'];
            }
            $status = $f['status'] ?? '';
            if ($status === 'aktif') {
                $where[] = "(p.is_active = TRUE AND (u.id IS NULL OR u.is_active = TRUE))";
            } elseif ($status === 'non-aktif') {
                $where[] = "(p.is_active = FALSE OR u.is_active = FALSE)";
            }
            $sql = "
                SELECT p.id, p.nim_nip, p.nama, p.jenis_kelamin, p.tanggal_lahir,
                       p.no_hp, p.status_civitas, p.golongan_darah, p.riwayat_alergi,
                       u.username,
                       (p.is_active AND COALESCE(u.is_active, TRUE)) AS status_aktif
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
            ";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY (p.is_active AND COALESCE(u.is_active, TRUE)) DESC, p.nama ASC";
        } elseif ($tab === 'tenaga-layanan') {
            if (($f['q'] ?? '') !== '') {
                $where[] = "(d.nama ILIKE :q OR COALESCE(d.no_str, '') ILIKE :q OR COALESCE(d.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
                $params[':q'] = '%' . $f['q'] . '%';
            }
            if (ctype_digit((string) ($f['poli_id'] ?? ''))) {
                $where[] = "d.poli_id = :poli_id";
                $params[':poli_id'] = (int) $f['poli_id'];
            }
            $status = $f['status'] ?? '';
            if ($status === 'aktif') {
                $where[] = "(d.is_active = TRUE AND u.is_active = TRUE)";
            } elseif ($status === 'non-aktif') {
                $where[] = "(d.is_active = FALSE OR u.is_active = FALSE)";
            }
            $sql = "
                SELECT d.id, d.poli_id, d.nama, d.no_str, d.no_hp, d.profesi,
                       po.nama AS nama_poli, u.username,
                       (d.is_active AND u.is_active) AS status_aktif
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                JOIN poli po ON d.poli_id = po.id
            ";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY (d.is_active AND u.is_active) DESC, d.nama ASC";
        } elseif ($tab === 'poli') {
            if (($f['q'] ?? '') !== '') {
                $where[] = "po.nama ILIKE :q";
                $params[':q'] = '%' . $f['q'] . '%';
            }
            if (($f['reservasi'] ?? '') === 'ya') $where[] = "po.butuh_reservasi = TRUE";
            elseif (($f['reservasi'] ?? '') === 'tidak') $where[] = "po.butuh_reservasi = FALSE";
            $sql = "
                SELECT po.id, po.nama, po.butuh_reservasi, po.kuota_harian, po.durasi_menit,
                       COUNT(d.id) FILTER (WHERE d.is_active = TRUE) AS jumlah_dokter
                FROM poli po
                LEFT JOIN dokter d ON d.poli_id = po.id
            ";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " GROUP BY po.id ORDER BY po.id ASC";
        } else {
            if (($f['q'] ?? '') !== '') {
                $where[] = "(a.nama ILIKE :q OR COALESCE(a.nim_nip, '') ILIKE :q OR COALESCE(a.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
                $params[':q'] = '%' . $f['q'] . '%';
            }
            if (($f['tipe_admin'] ?? '') === 'super') $where[] = "a.is_super = TRUE";
            elseif (($f['tipe_admin'] ?? '') === 'regular') $where[] = "a.is_super = FALSE";
            $status = $f['status'] ?? '';
            if ($status === 'aktif') $where[] = "u.is_active = TRUE";
            elseif ($status === 'non-aktif') $where[] = "u.is_active = FALSE";
            $sql = "
                SELECT a.id, a.nama, a.nim_nip, a.no_hp, a.is_super,
                       u.username, u.is_active AS status_aktif,
                       dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin, dc.unit
                FROM admin a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
            ";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY a.is_super DESC, u.is_active DESC, a.nama ASC";
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function jadwalAdmin(array $f): array {
        $where  = ["j.tanggal = :tanggal"];
        $params = [':tanggal' => $f['tanggal']];

        if (ctype_digit((string) ($f['poli_id'] ?? ''))) {
            $where[] = "j.poli_id = :poli_id";
            $params[':poli_id'] = (int) $f['poli_id'];
        }
        $status = $f['status'] ?? '';
        if (in_array($status, ['buka', 'penuh', 'tutup'], true)) {
            $where[] = "j.status = :status";
            $params[':status'] = $status;
        } elseif ($status === '') {
            $where[] = "j.status <> 'tutup'";
        }

        $sql = "
            SELECT
                j.id, j.poli_id, p.nama AS nama_poli, p.butuh_reservasi, p.kuota_harian,
                j.dokter_id, d.nama AS nama_dokter, d.profesi,
                j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota, j.status, j.dipublikasi,
                (
                    SELECT COUNT(*) FROM reservasi r
                    WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan'
                ) AS terisi_reservasi,
                (
                    SELECT COUNT(*) FROM antrean a
                    WHERE (a.jadwal_id = j.id OR (a.jadwal_id IS NULL AND a.poli_id = j.poli_id AND a.tanggal = j.tanggal))
                      AND a.status <> 'batal'
                ) AS terisi_antrean
            FROM jadwal j
            JOIN poli p ON p.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY (j.status = 'tutup') ASC, j.jam_mulai ASC, j.poli_id ASC
        ";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function reservasiAdmin(array $f): array {
        $sql = "
            SELECT * FROM (
                SELECT
                    r.id AS item_id,
                    'online' AS asal,
                    r.no_reservasi AS kode_tiket,
                    p.id AS pasien_id,
                    p.nama AS nama_pasien,
                    p.nim_nip,
                    p.status_civitas,
                    j.poli_id,
                    po.nama AS nama_poli,
                    d.nama AS nama_dokter,
                    j.tanggal,
                    j.jam_mulai,
                    j.jam_selesai,
                    r.status,
                    r.reschedule_count,
                    r.keluhan,
                    r.created_at,
                    a.id AS antrean_id,
                    a.no_antrean
                FROM reservasi r
                JOIN pasien p ON p.id = r.pasien_id
                JOIN jadwal j ON j.id = r.jadwal_id
                JOIN poli po ON po.id = j.poli_id
                LEFT JOIN dokter d ON d.id = j.dokter_id
                LEFT JOIN antrean a ON a.reservasi_id = r.id

                UNION ALL

                SELECT
                    a.id AS item_id,
                    'offline' AS asal,
                    concat('ANT-', lpad(a.no_antrean::text, 3, '0')) AS kode_tiket,
                    p.id AS pasien_id,
                    p.nama AS nama_pasien,
                    p.nim_nip,
                    p.status_civitas,
                    a.poli_id,
                    po.nama AS nama_poli,
                    COALESCE(d.nama, jd.nama) AS nama_dokter,
                    a.tanggal,
                    j.jam_mulai,
                    j.jam_selesai,
                    CASE a.status
                        WHEN 'batal' THEN 'dibatalkan'
                        WHEN 'diperiksa' THEN 'hadir'
                        ELSE a.status
                    END AS status,
                    0 AS reschedule_count,
                    pm.data_pemeriksaan->>'keluhan',
                    a.waktu_daftar AS created_at,
                    a.id AS antrean_id,
                    a.no_antrean
                FROM antrean a
                JOIN pasien p ON p.id = a.pasien_id
                JOIN poli po ON po.id = a.poli_id
                LEFT JOIN dokter d ON d.id = a.dokter_id
                LEFT JOIN jadwal j ON j.id = a.jadwal_id
                LEFT JOIN dokter jd ON jd.id = j.dokter_id
                LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
                WHERE a.reservasi_id IS NULL
            ) gabungan
            WHERE tanggal = :tanggal
        ";
        $params = [':tanggal' => $f['tanggal']];

        if (($f['q'] ?? '') !== '') {
            $sql .= " AND (nama_pasien ILIKE :q OR nim_nip ILIKE :q OR kode_tiket ILIKE :q OR COALESCE(nama_dokter, '') ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (ctype_digit((string) ($f['poli_id'] ?? ''))) {
            $sql .= " AND poli_id = :poli_id";
            $params[':poli_id'] = (int) $f['poli_id'];
        }
        if (in_array($f['asal'] ?? '', ['online', 'offline'], true)) {
            $sql .= " AND asal = :asal";
            $params[':asal'] = $f['asal'];
        }
        if (($f['status'] ?? '') !== '') {
            $sql .= " AND status = :status";
            $params[':status'] = $f['status'];
        }
        $sql .= " ORDER BY COALESCE(jam_mulai, '08:00:00'::time) ASC, created_at ASC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function konfirmasiAdmin(array $f): array {
        $where  = ["j.tanggal = :tgl", "po.nama ILIKE '%konseling%'"];
        $params = [':tgl' => $f['tanggal']];

        if (($f['q'] ?? '') !== '') {
            $where[] = "(p.nama ILIKE :q OR p.nim_nip ILIKE :q OR r.no_reservasi ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (in_array($f['status'] ?? '', ['menunggu', 'dikonfirmasi', 'dibatalkan'], true)) {
            $where[] = "r.status = :status";
            $params[':status'] = $f['status'];
        } else {
            $where[] = "r.status <> 'dibatalkan'";
        }

        $sql = "
            SELECT
                r.id AS reservasi_id,
                r.no_reservasi,
                r.status,
                r.konfirmasi_diminta_at,
                r.dikonfirmasi_at,
                p.nama AS nama_pasien,
                p.nim_nip,
                p.no_hp,
                j.tanggal AS tanggal_kunjungan,
                j.jam_mulai,
                j.jam_selesai,
                d.nama AS nama_dokter
            FROM reservasi r
            JOIN pasien p ON p.id = r.pasien_id
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY j.jam_mulai ASC, r.created_at ASC
        ";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function obatAdmin(array $f): array {
        $where  = [];
        $params = [];

        if (($f['q'] ?? '') !== '') {
            $where[] = "(o.nama_obat ILIKE :q OR o.kode_obat ILIKE :q OR COALESCE(o.keterangan, '') ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (($f['bentuk'] ?? '') !== '') {
            $where[] = "lower(o.bentuk) = :bentuk";
            $params[':bentuk'] = strtolower($f['bentuk']);
        }
        $stok = $f['stok'] ?? '';
        if ($stok === 'tersedia') {
            $where[] = "(o.stok > o.stok_minimum AND (o.tanggal_kadaluarsa IS NULL OR o.tanggal_kadaluarsa >= fn_today()))";
        } elseif ($stok === 'menipis') {
            $where[] = "(o.stok > 0 AND o.stok <= o.stok_minimum)";
        } elseif ($stok === 'habis') {
            $where[] = "o.stok = 0";
        } elseif ($stok === 'kadaluarsa') {
            $where[] = "(o.tanggal_kadaluarsa IS NOT NULL AND o.tanggal_kadaluarsa <= fn_today() + 30)";
        }
        $status = $f['status'] ?? '';
        if ($status === 'aktif') $where[] = "o.is_active = TRUE";
        elseif ($status === 'nonaktif') $where[] = "o.is_active = FALSE";

        $sql = "
            SELECT o.*,
                   (o.tanggal_kadaluarsa IS NOT NULL AND o.tanggal_kadaluarsa < fn_today()) AS is_expired,
                   (o.tanggal_kadaluarsa IS NOT NULL AND o.tanggal_kadaluarsa >= fn_today() AND o.tanggal_kadaluarsa <= fn_today() + 30) AS is_near_expired
            FROM obat o
        ";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY o.is_active DESC, o.kode_obat ASC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function resepAdmin(array $f): array {
        $where  = ["pm.tanggal = :tanggal"];
        $params = [':tanggal' => $f['tanggal']];

        if (($f['q'] ?? '') !== '') {
            $where[] = "(ps.nama ILIKE :q OR ps.nim_nip ILIKE :q OR d.nama ILIKE :q OR concat('RSP-', lpad(po.id::text, 3, '0')) ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (in_array($f['status'] ?? '', ['menunggu', 'disiapkan', 'diserahkan', 'dibatalkan'], true)) {
            $where[] = "po.status = :status";
            $params[':status'] = $f['status'];
        } else {
            $where[] = "po.status <> 'dibatalkan'";
        }

        $sql = "
            SELECT
                po.id AS permintaan_id,
                concat('RSP-', lpad(po.id::text, 3, '0')) AS kode_resep,
                po.status,
                po.created_at,
                po.waktu_diserahkan,
                pm.id AS pemeriksaan_id,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                d.nama AS nama_dokter,
                (SELECT COUNT(*) FROM resep_obat ro WHERE ro.pemeriksaan_id = pm.id) AS jumlah_obat
            FROM permintaan_obat po
            JOIN pemeriksaan pm ON pm.id = po.pemeriksaan_id
            JOIN pasien ps      ON ps.id = pm.pasien_id
            JOIN dokter d       ON d.id  = pm.dokter_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY
                CASE po.status
                    WHEN 'menunggu' THEN 1
                    WHEN 'disiapkan' THEN 2
                    WHEN 'diserahkan' THEN 3
                    ELSE 4
                END ASC,
                po.created_at DESC
        ";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function daftarPasienDokter(array $f): array {
        $where  = ["a.tanggal = :tgl", "a.poli_id = :poli_id"];
        $params = [
            ':tgl'     => $f['tanggal'],
            ':poli_id' => $f['poli_id'],
        ];

        if (($f['q'] ?? '') !== '') {
            $where[] = "(ps.nama ILIKE :q OR ps.nim_nip ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (in_array($f['status'] ?? '', ['menunggu', 'diperiksa', 'selesai'], true)) {
            $where[] = "a.status = :status";
            $params[':status'] = $f['status'];
        }
        if (($f['asal'] ?? '') === 'online') $where[] = "a.reservasi_id IS NOT NULL";
        elseif (($f['asal'] ?? '') === 'offline') $where[] = "a.reservasi_id IS NULL";

        $sql = "
            SELECT
                a.id AS antrean_id,
                a.no_antrean,
                a.status,
                a.reservasi_id,
                a.waktu_daftar,
                ps.id AS pasien_id,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                COALESCE(j.jam_mulai, (a.waktu_daftar AT TIME ZONE 'Asia/Jakarta')::time) AS jam_kunjungan,
                COALESCE(pm.data_pemeriksaan->>'keluhan', r.keluhan, 'Pemeriksaan langsung') AS keluhan_awal,
                pm.id AS pemeriksaan_id
            FROM antrean a
            JOIN pasien ps ON ps.id = a.pasien_id
            LEFT JOIN jadwal j       ON j.id = a.jadwal_id
            LEFT JOIN reservasi r    ON r.id = a.reservasi_id
            LEFT JOIN pemeriksaan pm ON pm.antrean_id = a.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY
                CASE a.status
                    WHEN 'diperiksa' THEN 1
                    WHEN 'menunggu' THEN 2
                    WHEN 'selesai' THEN 3
                    ELSE 4
                END ASC,
                a.no_antrean ASC
        ";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function riwayatDokter(array $f): array {
        $where  = ["(pm.dokter_id = :dok_id OR pm.poli_id = :poli_default)"];
        $params = [
            ':dok_id'       => $f['dok_id'],
            ':poli_default' => $f['poli_default'],
        ];

        if (($f['q'] ?? '') !== '') {
            $where[] = "(ps.nama ILIKE :q OR ps.nim_nip ILIKE :q OR COALESCE(pm.data_pemeriksaan->>'diagnosa', '') ILIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }
        if (ctype_digit((string) ($f['poli_id'] ?? ''))) {
            $where[] = "pm.poli_id = :f_poli";
            $params[':f_poli'] = (int) $f['poli_id'];
        }
        if ((int) ($f['pasien_id'] ?? 0) > 0) {
            $where[] = "pm.pasien_id = :f_pasien";
            $params[':f_pasien'] = (int) $f['pasien_id'];
        }

        $sql = "
            SELECT
                pm.id,
                pm.antrean_id,
                pm.pasien_id,
                pm.tanggal,
                pm.data_pemeriksaan->>'keluhan',
                pm.data_pemeriksaan->>'diagnosa',
                pm.created_at,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                pol.nama AS nama_poli,
                d.nama AS nama_dokter
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN poli pol  ON pol.id = pm.poli_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY pm.tanggal DESC, pm.created_at DESC
            LIMIT 50
        ";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function ringkasanDokter(int $id, bool $olehPemeriksaan) {
        $kolom = $olehPemeriksaan ? 'pm.id = :id' : 'pm.antrean_id = :id';
        $stmt = db()->prepare("
            SELECT
                pm.*,
                ps.nama AS nama_pasien,
                ps.nim_nip,
                ps.status_civitas,
                d.nama AS nama_dokter,
                pol.nama AS nama_poli,
                po.id AS permintaan_obat_id,
                po.status AS status_resep
            FROM pemeriksaan pm
            JOIN pasien ps ON ps.id = pm.pasien_id
            JOIN dokter d  ON d.id  = pm.dokter_id
            JOIN poli pol  ON pol.id = pm.poli_id
            LEFT JOIN permintaan_obat po ON po.pemeriksaan_id = pm.id
            WHERE {$kolom}
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function ringkasanObat(int $pemeriksaanId): array {
        $stmt = db()->prepare("
            SELECT ro.jumlah, ro.aturan_pakai, o.nama_obat, o.satuan, o.bentuk
            FROM resep_obat ro
            JOIN obat o ON o.id = ro.obat_id
            WHERE ro.pemeriksaan_id = :pem_id
            ORDER BY ro.id ASC
        ");
        $stmt->execute([':pem_id' => $pemeriksaanId]);
        return $stmt->fetchAll();
    }
}
