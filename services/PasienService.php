<?php
declare(strict_types=1);

final class PasienService {
    // Semua data yang dibutuhkan Beranda pasien, dikirim sekaligus
    public static function ringkasan(): array {
        $uid      = (int) ($_SESSION['user_id'] ?? 0);
        $pasienId = profil_id_sesi('pasien');
        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap. Hubungi admin.', 403);
        }

        $profil = db()->prepare('SELECT id, nama, nim_nip, status_civitas FROM pasien WHERE id = :id');
        $profil->execute([':id' => $pasienId]);
        $pasien = $profil->fetch();

        $reservasi = db()->prepare("
            SELECT r.id, r.no_reservasi, r.status, r.reschedule_count,
                   po.nama AS poli, COALESCE(d.nama, 'Belum ditentukan') AS dokter,
                   j.tanggal::text AS tanggal, j.jam_mulai::text AS jam_mulai,
                   j.jam_selesai::text AS jam_selesai
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE r.pasien_id = :pid AND r.status NOT IN ('dibatalkan', 'selesai', 'tidak_hadir')
            ORDER BY j.tanggal, j.jam_mulai
            LIMIT 5
        ");
        $reservasi->execute([':pid' => $pasienId]);

        $riwayat = db()->prepare("
            SELECT r.id, r.status, po.nama AS poli, j.tanggal::text AS tanggal
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            WHERE r.pasien_id = :pid AND r.status IN ('selesai', 'dibatalkan', 'tidak_hadir')
            ORDER BY j.tanggal DESC
            LIMIT 5
        ");
        $riwayat->execute([':pid' => $pasienId]);

        $notif = db()->prepare("
            SELECT id, tipe, judul, pesan, butuh_aksi, ref_tipe, ref_id, dibaca_at, created_at
            FROM inbox WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5
        ");
        $notif->execute([':uid' => $uid]);

        $belum = db()->prepare('SELECT COUNT(*) FROM inbox WHERE user_id = :uid AND dibaca_at IS NULL');
        $belum->execute([':uid' => $uid]);

        $jadwal = db()->query("
            SELECT id, poli, dokter, tanggal::text AS tanggal,
                   jam_mulai::text AS jam_mulai, jam_selesai::text AS jam_selesai, sisa_kuota
            FROM v_jadwal_tersedia
            ORDER BY tanggal, jam_mulai
            LIMIT 12
        ")->fetchAll();

        $dokter = db()->query("
            SELECT d.id, d.nama, d.profesi, po.nama AS poli,
                   MIN(j.jam_mulai)::text AS jam_mulai,
                   MAX(j.jam_selesai)::text AS jam_selesai
            FROM dokter d
            JOIN poli po ON po.id = d.poli_id
            LEFT JOIN jadwal j ON j.dokter_id = d.id AND j.tanggal >= fn_today() AND j.status <> 'tutup'
            WHERE d.is_active = TRUE
            GROUP BY d.id, d.nama, d.profesi, po.nama
            ORDER BY d.id
            LIMIT 10
        ")->fetchAll();

        return [
            'profil'           => $pasien,
            'reservasi_aktif'  => $reservasi->fetchAll(),
            'riwayat'          => $riwayat->fetchAll(),
            'notifikasi'       => $notif->fetchAll(),
            'notifikasi_belum' => (int) $belum->fetchColumn(),
            'jadwal_tersedia'  => $jadwal,
            'dokter'           => $dokter,
        ];
    }

    /** Daftar jadwal yang bisa dipesan, opsional filter tanggal dan poli. */
    public static function daftarJadwal(?string $tanggal = null, int $poliId = 0): array
    {
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

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $baris = $stmt->fetchAll();

        foreach ($baris as &$row) {
            $row['penuh'] = (int) $row['sisa_kuota'] <= 0;
            $row['tanggal_pendek'] = self::tanggalPendek($row['tanggal']);
            $row['jam'] = self::jam($row['jam_mulai']) . ' – ' . self::jam($row['jam_selesai']);
        }
        return $baris;
    }

    /** Daftar dokter aktif untuk ditampilkan di beranda publik. */
    public static function daftarDokter(): array
    {
        $baris = db()->query("
            SELECT d.id, d.nama, d.profesi, p.nama AS poli,
                   MIN(j.jam_mulai)::text AS jam_mulai,
                   MAX(j.jam_selesai)::text AS jam_selesai
            FROM dokter d
            JOIN poli p ON p.id = d.poli_id
            LEFT JOIN jadwal j ON j.dokter_id = d.id
                 AND j.tanggal >= fn_today() AND j.status <> 'tutup'
            WHERE d.is_active = TRUE
            GROUP BY d.id, d.nama, d.profesi, p.nama
            ORDER BY d.id
            LIMIT 6
        ")->fetchAll();
        foreach ($baris as &$row) {
            $row['jam'] = $row['jam_mulai'] !== null
                ? self::jam($row['jam_mulai']) . ' – ' . self::jam($row['jam_selesai'])
                : 'Jadwal belum tersedia';
        }
        return $baris;
    }

    /** Peta tanggal dalam satu bulan: tersedia / penuh. */
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

    /** Daftar reservasi aktif dan riwayat milik pasien yang login. */
    public static function daftarReservasi(): array
    {
        $pasienId = profil_id_sesi('pasien');
        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap.', 403);
        }

        $sql = "
            SELECT r.id, r.no_reservasi, r.status, r.reschedule_count,
                   r.keluhan, j.tanggal::text AS tanggal,
                   j.jam_mulai::text AS jam_mulai, j.jam_selesai::text AS jam_selesai,
                   po.nama AS poli, COALESCE(d.nama, 'Dokter akan ditentukan') AS dokter,
                   ja.tanggal::text AS tanggal_awal
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            LEFT JOIN jadwal ja ON ja.id = r.jadwal_awal_id
            WHERE r.pasien_id = :pid
        ";

        $aktif = db()->prepare($sql . "
            AND r.status NOT IN ('dibatalkan', 'selesai', 'tidak_hadir')
            ORDER BY j.tanggal ASC, j.jam_mulai ASC LIMIT 20
        ");
        $aktif->execute([':pid' => $pasienId]);

        $riwayat = db()->prepare($sql . "
            AND r.status IN ('dibatalkan', 'selesai', 'tidak_hadir')
            ORDER BY j.tanggal DESC, j.jam_mulai DESC LIMIT 20
        ");
        $riwayat->execute([':pid' => $pasienId]);

        return [
            'akan_datang' => self::rapikanReservasi($aktif->fetchAll()),
            'riwayat'     => self::rapikanReservasi($riwayat->fetchAll()),
        ];
    }

    /** Detail satu reservasi, dengan pengecekan kepemilikan. */
    public static function detailReservasi(int $id): array
    {
        $pasienId = profil_id_sesi('pasien');
        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap.', 403);
        }
        if ($id <= 0) {
            throw new ApiException('Reservasi tidak ditemukan.', 404);
        }

        $stmt = db()->prepare("
            SELECT r.id, r.no_reservasi, r.status, r.reschedule_count, r.keluhan,
                   j.tanggal::text AS tanggal,
                   j.jam_mulai::text AS jam_mulai, j.jam_selesai::text AS jam_selesai,
                   po.nama AS poli, COALESCE(d.nama, 'Dokter akan ditentukan') AS dokter
            FROM reservasi r
            JOIN jadwal j ON j.id = r.jadwal_id
            JOIN poli po ON po.id = j.poli_id
            LEFT JOIN dokter d ON d.id = j.dokter_id
            WHERE r.id = :id AND r.pasien_id = :pid
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':pid' => $pasienId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new ApiException('Reservasi tidak ditemukan.', 404);
        }
        $row['status_label'] = self::labelStatus($row['status'], (int) $row['reschedule_count']);
        $row['tanggal_panjang'] = self::tanggalPanjang($row['tanggal']);
        $row['jam'] = self::jam($row['jam_mulai']);
        return $row;
    }

    /** Daftar notifikasi inbox milik user yang login. */
    public static function daftarNotifikasi(): array
    {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $stmt = db()->prepare("
            SELECT id, tipe, judul, pesan, butuh_aksi, aksi_selesai_at,
                   ref_tipe, ref_id, dibaca_at, created_at
            FROM inbox
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':uid' => $uid]);
        $baris = $stmt->fetchAll();
        foreach ($baris as &$row) {
            $row['waktu'] = self::waktuRelatif($row['created_at']);
            $row['belum_dibaca'] = $row['dibaca_at'] === null;
        }
        return $baris;
    }

    private static function rapikanReservasi(array $baris): array
    {
        foreach ($baris as &$row) {
            $row['status_label'] = self::labelStatus($row['status'], (int) $row['reschedule_count']);
            $row['badge_color'] = self::warnaStatus($row['status'], (int) $row['reschedule_count']);
            $row['jadwal'] = self::tanggalPendek($row['tanggal']) . ', ' . self::jam($row['jam_mulai']);
            $row['note'] = (int) $row['reschedule_count'] > 0 && $row['tanggal_awal'] !== null
                ? 'Dipindah dari ' . self::tanggalPendek($row['tanggal_awal'])
                : null;
        }
        return $baris;
    }

    private static function labelStatus(string $status, int $reschedule): string
    {
        if ($reschedule > 0 && !in_array($status, ['dibatalkan', 'selesai'], true)) {
            return 'Reschedule';
        }
        return [
            'menunggu' => 'Menunggu',
            'dikonfirmasi' => 'Terkonfirmasi',
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'dibatalkan' => 'Dibatalkan',
            'selesai' => 'Selesai',
        ][$status] ?? $status;
    }

    private static function warnaStatus(string $status, int $reschedule): string
    {
        if ($reschedule > 0 && !in_array($status, ['dibatalkan', 'selesai'], true)) {
            return 'bg-blue-100 text-blue-700';
        }
        return match ($status) {
            'menunggu' => 'bg-orange-100 text-orange-700',
            'dikonfirmasi', 'hadir' => 'bg-green-100 text-green-700',
            'selesai' => 'bg-gray-200 text-gray-700',
            'tidak_hadir', 'dibatalkan' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    private static function tanggalPendek(string $tanggal): string
    {
        $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $hari = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        $ts = strtotime($tanggal);
        return $hari[(int) date('N', $ts)] . ', ' . (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)];
    }

    private static function tanggalPanjang(string $tanggal): string
    {
        $bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $ts = strtotime($tanggal);
        return $hari[(int) date('N', $ts)] . ', ' . (int) date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }

    private static function jam($jam): string
    {
        $jam = (string) $jam;
        return strlen($jam) >= 5 ? substr($jam, 0, 5) : $jam;
    }

    private static function waktuRelatif($createdAt): string
    {
        $ts = strtotime((string) $createdAt);
        $selisih = time() - $ts;
        if ($selisih < 60) return 'Baru saja';
        if ($selisih < 3600) return ((int) floor($selisih / 60)) . ' menit lalu';
        if ($selisih < 86400) return ((int) floor($selisih / 3600)) . ' jam lalu';
        return ((int) floor($selisih / 86400)) . ' hari lalu';
    }
}
