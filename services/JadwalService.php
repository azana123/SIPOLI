<?php
declare(strict_types=1);

final class JadwalService {
    public static function create(array $in): array {
        $poliId     = api_int($in, 'poli_id');
        $dokterId   = api_int($in, 'dokter_id') ?: null;
        $tanggal    = api_str($in, 'tanggal');
        $jamMulai   = api_str($in, 'jam_mulai');
        $jamSelesai = api_str($in, 'jam_selesai');
        $kuota      = api_bool($in, 'tanpa_batas') ? null : (api_int($in, 'kuota') ?: null);
        $publikasi  = api_bool($in, 'dipublikasi');

        if ($poliId <= 0 || $tanggal === '' || $jamMulai === '' || $jamSelesai === '') {
            throw new ApiException('Poli, tanggal, jam mulai, dan jam selesai wajib diisi.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            throw new ApiException('Format tanggal tidak valid.');
        }

        $id = api_transaction(static function (PDO $pdo) use (
            $poliId, $dokterId, $tanggal, $jamMulai, $jamSelesai, $kuota, $publikasi
        ): int {
            $stmt = $pdo->prepare("
                INSERT INTO jadwal (poli_id, dokter_id, tanggal, jam_mulai, jam_selesai, kuota, dipublikasi)
                VALUES (:poli, :dokter, :tanggal, :mulai, :selesai, :kuota, :publikasi)
                RETURNING id
            ");
            $stmt->bindValue(':poli', $poliId, PDO::PARAM_INT);
            $stmt->bindValue(':dokter', $dokterId, $dokterId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':tanggal', $tanggal);
            $stmt->bindValue(':mulai', $jamMulai);
            $stmt->bindValue(':selesai', $jamSelesai);
            $stmt->bindValue(':kuota', $kuota, $kuota === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':publikasi', $publikasi, PDO::PARAM_BOOL);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        });

        return ['id' => $id, 'tanggal' => $tanggal];
    }

    public static function update(array $in): array {
        $id         = api_int($in, 'id');
        $poliId     = api_int($in, 'poli_id');
        $dokterId   = api_int($in, 'dokter_id') ?: null;
        $tanggal    = api_str($in, 'tanggal');
        $jamMulai   = api_str($in, 'jam_mulai');
        $jamSelesai = api_str($in, 'jam_selesai');
        $kuota      = api_bool($in, 'tanpa_batas') ? null : (api_int($in, 'kuota') ?: null);
        $status     = api_str($in, 'status', 'buka');
        if (!in_array($status, ['buka', 'penuh', 'tutup'], true)) {
            $status = 'buka';
        }
        $publikasi = $status === 'tutup' ? false : api_bool($in, 'dipublikasi');

        if ($id <= 0 || $poliId <= 0 || $tanggal === '' || $jamMulai === '' || $jamSelesai === '') {
            throw new ApiException('Data jadwal tidak lengkap.');
        }

        api_transaction(static function (PDO $pdo) use (
            $id, $poliId, $dokterId, $tanggal, $jamMulai, $jamSelesai, $kuota, $status, $publikasi
        ): void {
            $stmt = $pdo->prepare("
                UPDATE jadwal
                   SET poli_id = :poli, dokter_id = :dokter, tanggal = :tanggal,
                       jam_mulai = :mulai, jam_selesai = :selesai, kuota = :kuota,
                       status = :status, dipublikasi = :publikasi
                 WHERE id = :id
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':poli', $poliId, PDO::PARAM_INT);
            $stmt->bindValue(':dokter', $dokterId, $dokterId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':tanggal', $tanggal);
            $stmt->bindValue(':mulai', $jamMulai);
            $stmt->bindValue(':selesai', $jamSelesai);
            $stmt->bindValue(':kuota', $kuota, $kuota === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':publikasi', $publikasi, PDO::PARAM_BOOL);
            $stmt->execute();

            if ($status !== 'tutup') {
                $pdo->prepare('SELECT fn_hitung_status_jadwal(:id)')->execute([':id' => $id]);
            }
        });

        return ['id' => $id, 'tanggal' => $tanggal];
    }

    public static function softDelete(array $in): array {
        $id = api_int($in, 'id');
        if ($id <= 0) {
            throw new ApiException('Jadwal tidak ditemukan.');
        }

        api_transaction(static function (PDO $pdo) use ($id): void {
            $pdo->prepare("
                UPDATE jadwal SET status = 'tutup', dipublikasi = FALSE WHERE id = :id
            ")->execute([':id' => $id]);
        });

        return ['id' => $id];
    }
}
