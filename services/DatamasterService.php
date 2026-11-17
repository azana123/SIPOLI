<?php
declare(strict_types=1);

final class DatamasterService {
    private const GOLDAR = ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    private const PROFESI = ['dokter umum', 'dokter gigi', 'psikolog'];

    public static function simpanPasien(array $in): array {
        $id     = api_int($in, 'pasien_id');
        $nimNip = api_str($in, 'nim_nip');
        $noHp   = api_nullable_str($in, 'no_hp');
        $goldar = api_str($in, 'golongan_darah');
        $alergi = api_nullable_str($in, 'riwayat_alergi');
        $goldar = in_array($goldar, self::GOLDAR, true) ? $goldar : null;

        return api_transaction(static function (PDO $pdo) use ($id, $nimNip, $noHp, $goldar, $alergi): array {
            if ($id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE pasien
                       SET no_hp = :hp, golongan_darah = :goldar, riwayat_alergi = :alergi
                     WHERE id = :id
                ");
                $stmt->bindValue(':hp', $noHp, $noHp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':goldar', $goldar, $goldar === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':alergi', $alergi, $alergi === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return ['id' => $id, 'mode' => 'update'];
            }

            if ($nimNip === '') {
                throw new ApiException('NIM atau NIP wajib diisi.');
            }
            $stmt = $pdo->prepare("
                INSERT INTO pasien (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, no_hp, golongan_darah, riwayat_alergi)
                VALUES (:nim, '-', 'L', CURRENT_DATE, 'mahasiswa', :hp, :goldar, :alergi)
                RETURNING id
            ");
            $stmt->bindValue(':nim', $nimNip);
            $stmt->bindValue(':hp', $noHp, $noHp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':goldar', $goldar, $goldar === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':alergi', $alergi, $alergi === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->execute();
            return ['id' => (int) $stmt->fetchColumn(), 'mode' => 'create'];
        });
    }

    public static function simpanDokter(array $in): array {
        $id       = api_int($in, 'dokter_id');
        $nama     = api_str($in, 'nama');
        $username = api_str($in, 'username');
        $password = (string) ($in['password'] ?? '');
        $poliId   = api_int($in, 'poli_id');
        $profesi  = strtolower(api_str($in, 'profesi'));
        $noStr    = api_nullable_str($in, 'no_str');
        $noHp     = api_nullable_str($in, 'no_hp');

        if ($nama === '' || $username === '' || $poliId <= 0) {
            throw new ApiException('Nama, username, dan poli layanan wajib diisi.');
        }
        if (!in_array($profesi, self::PROFESI, true)) {
            throw new ApiException('Pilihan profesi tidak sesuai.');
        }

        return api_transaction(static function (PDO $pdo) use ($id, $nama, $username, $password, $poliId, $profesi, $noStr, $noHp): array {
            if ($id > 0) {
                $stmtGet = $pdo->prepare('SELECT user_id FROM dokter WHERE id = :id');
                $stmtGet->execute([':id' => $id]);
                $dRow = $stmtGet->fetch();
                if (!$dRow) {
                    throw new ApiException('Data tenaga layanan tidak ditemukan.');
                }
                self::updateAkun($pdo, (int) $dRow['user_id'], $username, $password);

                $stmt = $pdo->prepare("
                    UPDATE dokter
                       SET poli_id = :poli, nama = :nama, no_str = :str, no_hp = :hp, profesi = :profesi
                     WHERE id = :id
                ");
                $stmt->bindValue(':poli', $poliId, PDO::PARAM_INT);
                $stmt->bindValue(':nama', $nama);
                $stmt->bindValue(':str', $noStr, $noStr === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':hp', $noHp, $noHp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':profesi', $profesi);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return ['id' => $id, 'mode' => 'update'];
            }

            if (strlen($password) < 8) {
                throw new ApiException('Kata sandi akun baru minimal 8 karakter.');
            }
            $stmtU = $pdo->prepare("
                INSERT INTO users (username, password_hash, role, is_active, must_change_password)
                VALUES (:u, :p, 'dokter', TRUE, FALSE) RETURNING id
            ");
            $stmtU->execute([':u' => $username, ':p' => password_hash($password, PASSWORD_BCRYPT)]);
            $uid = (int) $stmtU->fetchColumn();

            $stmtD = $pdo->prepare("
                INSERT INTO dokter (user_id, poli_id, nama, no_str, no_hp, profesi, is_active)
                VALUES (:uid, :poli, :nama, :str, :hp, :profesi, TRUE) RETURNING id
            ");
            $stmtD->bindValue(':uid', $uid, PDO::PARAM_INT);
            $stmtD->bindValue(':poli', $poliId, PDO::PARAM_INT);
            $stmtD->bindValue(':nama', $nama);
            $stmtD->bindValue(':str', $noStr, $noStr === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtD->bindValue(':hp', $noHp, $noHp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtD->bindValue(':profesi', $profesi);
            $stmtD->execute();
            return ['id' => (int) $stmtD->fetchColumn(), 'mode' => 'create'];
        });
    }

    public static function simpanPoli(array $in): array {
        self::pastikanSuperAdmin();
        $id       = api_int($in, 'poli_id_PK');
        $nama     = api_str($in, 'nama');
        $butuh    = api_bool($in, 'butuh_reservasi');
        $kuota    = $butuh ? max(1, api_int($in, 'kuota_harian', 5)) : null;
        $durasi   = api_int($in, 'durasi_menit') > 0 ? max(5, api_int($in, 'durasi_menit')) : null;
        if ($nama === '') {
            throw new ApiException('Nama poli wajib diisi.');
        }

        return api_transaction(static function (PDO $pdo) use ($id, $nama, $butuh, $kuota, $durasi): array {
            if ($id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE poli SET nama = :nama, butuh_reservasi = :butuh,
                                    kuota_harian = :kuota, durasi_menit = :durasi
                     WHERE id = :id
                ");
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO poli (nama, butuh_reservasi, kuota_harian, durasi_menit)
                    VALUES (:nama, :butuh, :kuota, :durasi) RETURNING id
                ");
            }
            $stmt->bindValue(':nama', $nama);
            $stmt->bindValue(':butuh', $butuh, PDO::PARAM_BOOL);
            $stmt->bindValue(':kuota', $kuota, $kuota === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':durasi', $durasi, $durasi === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
            return ['id' => $id > 0 ? $id : (int) $stmt->fetchColumn()];
        });
    }

    public static function simpanAdmin(array $in): array {
        self::pastikanSuperAdmin();
        $id       = api_int($in, 'admin_id');
        $nimNip   = api_str($in, 'nim_nip');
        $username = api_str($in, 'username');
        $password = (string) ($in['password'] ?? '');
        $noHp     = api_nullable_str($in, 'no_hp');
        $isSuper  = api_bool($in, 'is_super');
        $adminId  = profil_id_sesi('admin');

        if ($username === '') {
            throw new ApiException('Username wajib diisi.');
        }

        return api_transaction(static function (PDO $pdo) use ($id, $nimNip, $username, $password, $noHp, $isSuper, $adminId): array {
            if ($id > 0) {
                if ($id === $adminId && !$isSuper) {
                    throw new ApiException('Anda tidak dapat menurunkan peran Super Admin sendiri.');
                }
                $stmtGet = $pdo->prepare('SELECT user_id FROM admin WHERE id = :id');
                $stmtGet->execute([':id' => $id]);
                $aRow = $stmtGet->fetch();
                if (!$aRow) {
                    throw new ApiException('Data admin tidak ditemukan.');
                }
                self::updateAkun($pdo, (int) $aRow['user_id'], $username, $password);

                $stmt = $pdo->prepare('UPDATE admin SET no_hp = :hp, is_super = :super WHERE id = :id');
                $stmt->bindValue(':hp', $noHp, $noHp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(':super', $isSuper, PDO::PARAM_BOOL);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return ['id' => $id, 'mode' => 'update'];
            }

            if ($nimNip === '' || strlen($password) < 8) {
                throw new ApiException('NIM/NIP civitas wajib diisi dan kata sandi minimal 8 karakter.');
            }
            $stmtCiv = $pdo->prepare('SELECT nama, status_keanggotaan FROM data_civitas WHERE nim_nip = :nim');
            $stmtCiv->execute([':nim' => $nimNip]);
            $civ = $stmtCiv->fetch();
            if (!$civ) {
                throw new ApiException("NIM/NIP {$nimNip} tidak ditemukan pada data civitas.");
            }
            if ($civ['status_keanggotaan'] !== 'aktif') {
                throw new ApiException("Civitas dengan NIM/NIP {$nimNip} tidak aktif.");
            }

            $stmtU = $pdo->prepare("
                INSERT INTO users (username, password_hash, role, is_active, must_change_password)
                VALUES (:u, :p, 'admin', TRUE, FALSE) RETURNING id
            ");
            $stmtU->execute([':u' => $username, ':p' => password_hash($password, PASSWORD_BCRYPT)]);
            $uid = (int) $stmtU->fetchColumn();

            $stmtA = $pdo->prepare("
                INSERT INTO admin (user_id, nama, nim_nip, is_super, no_hp)
                VALUES (:uid, :nama, :nim, :super, :hp) RETURNING id
            ");
            $stmtA->bindValue(':uid', $uid, PDO::PARAM_INT);
            $stmtA->bindValue(':nama', $civ['nama']);
            $stmtA->bindValue(':nim', $nimNip);
            $stmtA->bindValue(':super', $isSuper, PDO::PARAM_BOOL);
            $stmtA->bindValue(':hp', $noHp, $noHp === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtA->execute();
            return ['id' => (int) $stmtA->fetchColumn(), 'mode' => 'create'];
        });
    }

    public static function toggleActive(array $in): array {
        $entity   = api_str($in, 'entity');
        $id       = api_int($in, 'id');
        $newState = api_bool($in, 'new_state');

        if (!in_array($entity, ['pasien', 'dokter', 'admin'], true)) {
            throw new ApiException('Jenis data tidak dikenal.');
        }
        if ($entity === 'admin') {
            self::pastikanSuperAdmin();
        }
        if ($id <= 0) {
            throw new ApiException('Data tidak ditemukan.');
        }

        api_transaction(static function (PDO $pdo) use ($entity, $id, $newState): void {
            // Super Admin terakhir tidak boleh dinonaktifkan, termasuk oleh dirinya sendiri.
            if ($entity === 'admin' && !$newState) {
                $stmtSuper = $pdo->prepare('SELECT is_super FROM admin WHERE id = :id');
                $stmtSuper->execute([':id' => $id]);
                if ($stmtSuper->fetchColumn()) {
                    $jumlah = (int) $pdo->query("
                        SELECT COUNT(*)
                        FROM admin a
                        JOIN users u ON u.id = a.user_id
                        WHERE a.is_super = TRUE AND u.is_active = TRUE
                    ")->fetchColumn();
                    if ($jumlah <= 1) {
                        throw new ApiException('Super Admin terakhir tidak dapat dinonaktifkan.');
                    }
                }
            }

            $kolom = $entity === 'pasien' ? 'pasien' : $entity;
            $stmt = $pdo->prepare("UPDATE {$kolom} SET is_active = :st WHERE id = :id RETURNING user_id");
            $stmt->bindValue(':st', $newState, PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $userId = (int) ($stmt->fetchColumn() ?: 0);
            if ($userId > 0) {
                $pdo->prepare('UPDATE users SET is_active = :st WHERE id = :uid')
                    ->execute([':st' => $newState, ':uid' => $userId]);
            }
        });

        return ['entity' => $entity, 'id' => $id, 'is_active' => $newState];
    }

    private static function updateAkun(PDO $pdo, int $userId, string $username, string $password): void {
        if ($password !== '') {
            if (strlen($password) < 8) {
                throw new ApiException('Kata sandi baru minimal 8 karakter.');
            }
            $stmt = $pdo->prepare('UPDATE users SET username = :u, password_hash = :p WHERE id = :id');
            $stmt->execute([':u' => $username, ':p' => password_hash($password, PASSWORD_BCRYPT), ':id' => $userId]);
        } else {
            $pdo->prepare('UPDATE users SET username = :u WHERE id = :id')
                ->execute([':u' => $username, ':id' => $userId]);
        }
    }

    private static function pastikanSuperAdmin(): void {
        $id = profil_id_sesi('admin');
        $stmt = db()->prepare('SELECT is_super FROM admin WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) {
            throw new ApiException('Hanya Super Admin yang dapat melakukan aksi ini.', 403);
        }
    }
}
