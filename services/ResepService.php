<?php
declare(strict_types=1);

final class ResepService {
    public static function updateStatus(array $in): array {
        $id        = api_int($in, 'permintaan_id');
        $status    = api_str($in, 'status_baru');
        $catatan   = api_nullable_str($in, 'catatan');
        $adminId   = profil_id_sesi('admin') ?: null;

        if (!in_array($status, ['disiapkan', 'diserahkan', 'dibatalkan'], true)) {
            throw new ApiException('Status pemrosesan resep tidak valid.');
        }

        api_transaction(static function (PDO $pdo) use ($id, $status, $catatan, $adminId): void {
            $stmt = $pdo->prepare('SELECT id, status FROM permintaan_obat WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $id]);
            $req = $stmt->fetch();
            if (!$req) {
                throw new ApiException('Data permintaan resep tidak ditemukan.');
            }
            if (in_array($req['status'], ['diserahkan', 'dibatalkan'], true)) {
                throw new ApiException('Resep yang sudah selesai atau dibatalkan tidak dapat diubah lagi.');
            }

            $pdo->prepare("
                UPDATE permintaan_obat
                   SET status = :status::text,
                       diproses_oleh = COALESCE(:admin, diproses_oleh),
                       waktu_diserahkan = CASE WHEN :status = 'diserahkan' THEN NOW() ELSE waktu_diserahkan END,
                       catatan = COALESCE(NULLIF(:catatan, ''), catatan)
                 WHERE id = :id
            ")->execute([
                ':status'  => $status,
                ':admin'   => $adminId,
                ':catatan' => $catatan ?? '',
                ':id'      => $id,
            ]);

            $pdo->prepare("
                UPDATE inbox
                   SET dibaca_at = COALESCE(dibaca_at, NOW()),
                       aksi_selesai_at = CASE WHEN :status IN ('diserahkan', 'dibatalkan') THEN NOW() ELSE aksi_selesai_at END
                 WHERE tipe = 'permintaan_obat' AND ref_tipe = 'permintaan_obat' AND ref_id = :id
            ")->execute([':status' => $status, ':id' => $id]);
        });

        return ['permintaan_id' => $id, 'status' => $status];
    }
}
