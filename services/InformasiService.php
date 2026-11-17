<?php
declare(strict_types=1);

final class InformasiService {
    public static function kirim(array $in): array {
        $judul  = api_str($in, 'judul');
        $pesan  = api_str($in, 'pesan');
        $target = api_str($in, 'target_penerima', 'semua');

        if ($judul === '' || $pesan === '') {
            throw new ApiException('Judul dan isi pesan wajib diisi.');
        }

        $jumlah = api_transaction(static function (PDO $pdo) use ($judul, $pesan, $target): int {
            $sql    = "SELECT id FROM users WHERE role = 'pasien' AND is_active = TRUE";
            $params = [];
            if (in_array($target, ['mahasiswa', 'dosen', 'staf'], true)) {
                $sql = "
                    SELECT u.id FROM users u
                    JOIN pasien p ON p.user_id = u.id
                    WHERE u.role = 'pasien' AND u.is_active = TRUE AND p.status_civitas = :civ
                ";
                $params[':civ'] = $target;
            }

            $penerima = $pdo->prepare($sql);
            $penerima->execute($params);
            $daftar = $penerima->fetchAll();
            if ($daftar === []) {
                throw new ApiException('Belum ada akun pasien aktif pada kategori penerima yang dipilih.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id)
                VALUES (:uid, 'info', :judul, :pesan, 'informasi', NULL)
            ");
            foreach ($daftar as $row) {
                $stmt->execute([
                    ':uid'   => (int) $row['id'],
                    ':judul' => $judul,
                    ':pesan' => $pesan,
                ]);
            }
            return count($daftar);
        });

        return ['penerima' => $jumlah, 'judul' => $judul];
    }
}
