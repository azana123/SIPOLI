<?php
declare(strict_types=1);

final class NotifikasiService {
    public static function bacaSemua(): array {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $stmt = db()->prepare('UPDATE inbox SET dibaca_at = NOW() WHERE user_id = :uid AND dibaca_at IS NULL');
        $stmt->execute([':uid' => $uid]);
        return ['user_id' => $uid, 'terupdate' => $stmt->rowCount()];
    }

    public static function tandaiDibaca(array $in): array {
        $uid   = (int) ($_SESSION['user_id'] ?? 0);
        $id    = api_int($in, 'notifikasi_id');
        $aksi  = api_bool($in, 'aksi_selesai');

        if ($id <= 0) {
            throw new ApiException('Notifikasi tidak ditemukan.');
        }
        $sql = $aksi
            ? 'UPDATE inbox SET dibaca_at = COALESCE(dibaca_at, NOW()), aksi_selesai_at = NOW() WHERE id = :id AND user_id = :uid'
            : 'UPDATE inbox SET dibaca_at = COALESCE(dibaca_at, NOW()) WHERE id = :id AND user_id = :uid';
        $stmt = db()->prepare($sql);
        $stmt->execute([':id' => $id, ':uid' => $uid]);
        if ($stmt->rowCount() === 0) {
            throw new ApiException('Notifikasi tidak ditemukan.', 404);
        }
        return ['id' => $id, 'dibaca' => true];
    }
}
