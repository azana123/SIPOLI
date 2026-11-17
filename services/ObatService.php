<?php
declare(strict_types=1);

final class ObatService {
    public static function create(array $in): array {
        $kode    = strtoupper(api_str($in, 'kode_obat'));
        $nama    = api_str($in, 'nama_obat');
        $bentuk  = strtolower(api_str($in, 'bentuk')) ?: null;
        $satuan  = strtolower(api_str($in, 'satuan'));
        $stok    = max(0, api_int($in, 'stok_awal'));
        $min     = max(0, api_int($in, 'stok_minimum', 10));
        $exp     = api_nullable_str($in, 'tanggal_kadaluarsa');
        $dosis   = api_str($in, 'dosis_umum');
        $ket     = api_str($in, 'keterangan');
        $aktif   = api_str($in, 'status', 'aktif') === 'aktif';
        $adminId = profil_id_sesi('admin') ?: null;

        if ($kode === '' || $nama === '' || $satuan === '') {
            throw new ApiException('Kode, nama, dan satuan obat wajib diisi.');
        }
        $keterangan = $dosis !== ''
            ? 'Dosis umum: ' . $dosis . ($ket !== '' ? ' - ' . $ket : '')
            : ($ket !== '' ? $ket : null);

        $id = api_transaction(static function (PDO $pdo) use ($kode, $nama, $bentuk, $satuan, $min, $exp, $keterangan, $aktif, $stok, $adminId): int {
            $stmt = $pdo->prepare("
                INSERT INTO obat (kode_obat, nama_obat, bentuk, satuan, stok, stok_minimum, tanggal_kadaluarsa, keterangan, is_active)
                VALUES (:kode, :nama, :bentuk, :satuan, 0, :min, :exp, :ket, :aktif)
                RETURNING id
            ");
            $stmt->bindValue(':kode', $kode);
            $stmt->bindValue(':nama', $nama);
            $stmt->bindValue(':bentuk', $bentuk, $bentuk === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':satuan', $satuan);
            $stmt->bindValue(':min', $min, PDO::PARAM_INT);
            $stmt->bindValue(':exp', $exp, $exp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':ket', $keterangan, $keterangan === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':aktif', $aktif, PDO::PARAM_BOOL);
            $stmt->execute();
            $id = (int) $stmt->fetchColumn();

            if ($stok > 0) {
                $pdo->prepare('SELECT fn_tambah_stok_obat(:id, :jumlah, :admin, :ket)')->execute([
                    ':id'     => $id,
                    ':jumlah' => $stok,
                    ':admin'  => $adminId,
                    ':ket'    => 'Stok awal penambahan obat baru',
                ]);
            }
            return $id;
        });

        return ['id' => $id];
    }

    public static function aturStok(array $in): array {
        $obatId   = api_int($in, 'obat_id');
        $jenis    = api_str($in, 'jenis_perubahan', 'tambah');
        $jumlah   = api_int($in, 'jumlah');
        $catatan  = api_str($in, 'catatan', 'Penyesuaian stok oleh admin');
        $adminId  = profil_id_sesi('admin') ?: null;

        if ($obatId <= 0 || $jumlah <= 0) {
            throw new ApiException('Obat dan jumlah perubahan stok wajib diisi.');
        }

        api_transaction(static function (PDO $pdo) use ($obatId, $jenis, $jumlah, $catatan, $adminId): void {
            if ($jenis === 'tambah') {
                $stmt = $pdo->prepare('SELECT fn_tambah_stok_obat(:id, :jumlah, :admin, :ket)');
                $stmt->execute([':id' => $obatId, ':jumlah' => $jumlah, ':admin' => $adminId, ':ket' => $catatan]);
            } else {
                $stmt = $pdo->prepare('SELECT fn_ubah_stok(:id, :delta, NULL, :admin, :ket)');
                $stmt->execute([':id' => $obatId, ':delta' => -$jumlah, ':admin' => $adminId, ':ket' => $catatan]);
            }
        });

        return ['obat_id' => $obatId, 'jenis' => $jenis, 'jumlah' => $jumlah];
    }

    public static function toggleActive(array $in): array {
        $id       = api_int($in, 'obat_id');
        $newState = api_bool($in, 'new_state');
        if ($id <= 0) {
            throw new ApiException('Obat tidak ditemukan.');
        }

        api_transaction(static function (PDO $pdo) use ($id, $newState): void {
            $pdo->prepare('UPDATE obat SET is_active = :st WHERE id = :id')
                ->execute([':st' => (int) $newState, ':id' => $id]);
        });

        return ['obat_id' => $id, 'is_active' => $newState];
    }
}
