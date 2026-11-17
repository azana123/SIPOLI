<?php
declare(strict_types=1);

final class ReservasiService {
    // ============
    // Aksi admin =
    // ============
    public static function checkin(array $in): array {
        $reservasiId = api_int($in, 'reservasi_id');
        $adminId     = profil_id_sesi('admin') ?: null;
        if ($reservasiId <= 0) {
            throw new ApiException('Reservasi tidak ditemukan.');
        }

        $noAntrean = api_transaction(static function (PDO $pdo) use ($reservasiId, $adminId): int {
            $stmt = $pdo->prepare("
                INSERT INTO antrean (no_antrean, pasien_id, poli_id, jadwal_id, dokter_id, reservasi_id, admin_id)
                SELECT 0, r.pasien_id, j.poli_id, j.id, j.dokter_id, r.id, :admin
                FROM reservasi r
                JOIN jadwal j ON j.id = r.jadwal_id
                WHERE r.id = :id
                RETURNING no_antrean
            ");
            $stmt->bindValue(':admin', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id', $reservasiId, PDO::PARAM_INT);
            $stmt->execute();
            $nomor = (int) $stmt->fetchColumn();

            $pdo->prepare("UPDATE reservasi SET status = 'hadir' WHERE id = :id AND status = 'dikonfirmasi'")
                ->execute([':id' => $reservasiId]);

            return $nomor;
        });

        return ['reservasi_id' => $reservasiId, 'no_antrean' => $noAntrean];
    }

    public static function rescheduleAdmin(array $in): array {
        $reservasiId  = api_int($in, 'reservasi_id');
        $jadwalBaruId = api_int($in, 'jadwal_baru_id');
        if ($reservasiId <= 0 || $jadwalBaruId <= 0) {
            throw new ApiException('Reservasi atau jadwal tujuan tidak valid.');
        }

        api_transaction(static function (PDO $pdo) use ($reservasiId, $jadwalBaruId): void {
            $pdo->prepare('SELECT fn_reschedule_reservasi(:id, :jadwal)')
                ->execute([':id' => $reservasiId, ':jadwal' => $jadwalBaruId]);
        });

        return ['reservasi_id' => $reservasiId, 'jadwal_baru_id' => $jadwalBaruId];
    }

    public static function batalAdmin(array $in): array {
        $reservasiId = api_int($in, 'reservasi_id');
        if ($reservasiId <= 0) {
            throw new ApiException('Reservasi tidak ditemukan.');
        }

        api_transaction(static function (PDO $pdo) use ($reservasiId): void {
            $pdo->prepare("
                UPDATE reservasi SET status = 'dibatalkan', alasan_batal = 'admin' WHERE id = :id
            ")->execute([':id' => $reservasiId]);
            $pdo->prepare("
                UPDATE antrean SET status = 'batal'
                 WHERE reservasi_id = :id AND status IN ('menunggu', 'diperiksa')
            ")->execute([':id' => $reservasiId]);
        });

        return ['reservasi_id' => $reservasiId];
    }

    public static function batalWalkin(array $in): array {
        $antreanId = api_int($in, 'antrean_id');
        if ($antreanId <= 0) {
            throw new ApiException('Antrean tidak ditemukan.');
        }

        api_transaction(static function (PDO $pdo) use ($antreanId): void {
            $pdo->prepare("
                UPDATE antrean SET status = 'batal'
                 WHERE id = :id AND status IN ('menunggu', 'diperiksa')
            ")->execute([':id' => $antreanId]);
        });

        return ['antrean_id' => $antreanId];
    }

    // =============
    // Aksi pasien =
    // =============

    public static function buat(array $in): array {
        $pasienId  = profil_id_sesi('pasien');
        $jadwalId  = api_int($in, 'jadwal_id');
        $keluhan   = api_nullable_str($in, 'keluhan');

        if ($pasienId <= 0) {
            throw new ApiException('Profil pasien belum lengkap. Hubungi admin.', 403);
        }
        if ($jadwalId <= 0) {
            throw new ApiException('Jadwal yang dipilih tidak valid.');
        }

        return api_transaction(static function (PDO $pdo) use ($pasienId, $jadwalId, $keluhan): array {
            $stmt = $pdo->prepare("
                INSERT INTO reservasi (pasien_id, jadwal_id, keluhan)
                VALUES (:pasien, :jadwal, :keluhan)
                RETURNING id, no_reservasi, status
            ");
            $stmt->bindValue(':pasien', $pasienId, PDO::PARAM_INT);
            $stmt->bindValue(':jadwal', $jadwalId, PDO::PARAM_INT);
            $stmt->bindValue(':keluhan', $keluhan, $keluhan === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();

            return [
                'id'           => (int) $row['id'],
                'no_reservasi' => $row['no_reservasi'],
                'status'       => $row['status'],
            ];
        });
    }

    public static function konfirmasi(array $in): array {
        $pasienId    = profil_id_sesi('pasien');
        $reservasiId = api_int($in, 'reservasi_id');
        $hadir       = api_bool($in, 'hadir');
        self::pastikanMilikPasien($reservasiId, $pasienId);

        $hasil = api_transaction(static function (PDO $pdo) use ($reservasiId, $hadir): string {
            $stmt = $pdo->prepare('SELECT fn_konfirmasi_reservasi(:id, :hadir)');
            $stmt->bindValue(':id', $reservasiId, PDO::PARAM_INT);
            $stmt->bindValue(':hadir', $hadir, PDO::PARAM_BOOL);
            $stmt->execute();
            return (string) $stmt->fetchColumn();
        });

        return ['reservasi_id' => $reservasiId, 'hasil' => $hasil];
    }

    public static function batalPasien(array $in): array {
        $pasienId    = profil_id_sesi('pasien');
        $reservasiId = api_int($in, 'reservasi_id');
        $alasan      = api_str($in, 'alasan');
        self::pastikanMilikPasien($reservasiId, $pasienId);

        api_transaction(static function (PDO $pdo) use ($reservasiId, $alasan): void {
            $pdo->prepare("
                UPDATE reservasi
                   SET status = 'dibatalkan', alasan_batal = 'pasien',
                       keluhan = CASE WHEN :alasan <> ''
                                 THEN concat(COALESCE(keluhan, ''), ' [Alasan batal: ', :alasan, ']')
                                 ELSE keluhan END
                 WHERE id = :id AND status IN ('menunggu', 'dikonfirmasi')
            ")->execute([':alasan' => $alasan, ':id' => $reservasiId]);
            $pdo->prepare("
                UPDATE antrean SET status = 'batal'
                 WHERE reservasi_id = :id AND status IN ('menunggu', 'diperiksa')
            ")->execute([':id' => $reservasiId]);
        });

        return ['reservasi_id' => $reservasiId];
    }

    public static function reschedulePasien(array $in): array {
        $pasienId     = profil_id_sesi('pasien');
        $reservasiId  = api_int($in, 'reservasi_id');
        $jadwalBaruId = api_int($in, 'jadwal_baru_id');
        self::pastikanMilikPasien($reservasiId, $pasienId);

        api_transaction(static function (PDO $pdo) use ($reservasiId, $jadwalBaruId): void {
            $pdo->prepare('SELECT fn_reschedule_reservasi(:id, :jadwal)')
                ->execute([':id' => $reservasiId, ':jadwal' => $jadwalBaruId]);
        });

        return ['reservasi_id' => $reservasiId, 'jadwal_baru_id' => $jadwalBaruId];
    }

    private static function pastikanMilikPasien(int $reservasiId, int $pasienId): void {
        if ($reservasiId <= 0) {
            throw new ApiException('Reservasi tidak ditemukan.');
        }
        $stmt = db()->prepare('SELECT 1 FROM reservasi WHERE id = :id AND pasien_id = :pid');
        $stmt->execute([':id' => $reservasiId, ':pid' => $pasienId]);
        if (!$stmt->fetchColumn()) {
            throw new ApiException('Reservasi ini bukan milik akun Anda.', 403);
        }
    }
}
