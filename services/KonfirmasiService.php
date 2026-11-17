<?php
declare(strict_types=1);

final class KonfirmasiService {
    public static function catatRespons(array $in): array {
        $reservasiId = api_int($in, 'reservasi_id');
        $hasil       = api_str($in, 'hasil_konfirmasi', 'hadir');
        $catatan     = api_nullable_str($in, 'catatan_respons');
        if ($reservasiId <= 0) {
            throw new ApiException('Reservasi tidak ditemukan.');
        }

        $hasilFungsi = api_transaction(static function (PDO $pdo) use ($reservasiId, $hasil, $catatan): string {
            $stmt = $pdo->prepare('SELECT fn_konfirmasi_reservasi(:id, :hadir)');
            $stmt->bindValue(':id', $reservasiId, PDO::PARAM_INT);
            $stmt->bindValue(':hadir', $hasil === 'hadir', PDO::PARAM_BOOL);
            $stmt->execute();
            $hasilFn = (string) $stmt->fetchColumn();

            if ($catatan !== null) {
                $pdo->prepare("
                    UPDATE reservasi
                       SET keluhan = concat(COALESCE(keluhan, ''), ' [Catatan Konfirmasi: ', :cat, ']')
                     WHERE id = :id
                ")->execute([':cat' => $catatan, ':id' => $reservasiId]);
            }

            return $hasilFn;
        });

        return ['reservasi_id' => $reservasiId, 'hasil' => $hasilFungsi];
    }
}
