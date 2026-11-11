<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . '/../middleware/check-role.php';
check_role('admin');
require_once __DIR__ . '/../config/database.php';

$page_title = "Datamaster";
$app_name   = "SIPOLI";
$errorDb    = false;

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$isSuperAdmin   = false;
$currentAdminId = (int) ($_SESSION['profil_id'] ?? 0);
try {
    if ($currentAdminId > 0) {
        $stmtSuper = $pdo->prepare("SELECT is_super FROM admin WHERE id = :id");
        $stmtSuper->execute([':id' => $currentAdminId]);
        $admRow = $stmtSuper->fetch();
        $isSuperAdmin = !empty($admRow['is_super']);
    }
} catch (PDOException $e) {
    $isSuperAdmin = false;
}

$validTabs = ['pasien', 'tenaga-layanan', 'poli', 'admin'];
$activeTab = isset($_GET['tab']) && in_array($_GET['tab'], $validTabs, true) ? $_GET['tab'] : 'pasien';

// =====================================================================
// PROSES POST: SIMPAN PASIEN, TENAGA LAYANAN, POLI, ADMIN & UBAH STATUS
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $backTab = $_POST['tab'] ?? 'pasien';

    try {
        $pdo->beginTransaction();

        if (!empty($_SESSION['user_id'])) {
            $stmtCfg = $pdo->prepare("SELECT set_config('app.user_id', :uid, true)");
            $stmtCfg->execute([':uid' => (string) $_SESSION['user_id']]);
        }

        // TAMBAH / EDIT PASIEN
        if ($action === 'save_pasien') {
            $pasienId = !empty($_POST['pasien_id']) ? (int) $_POST['pasien_id'] : 0;
            $nimNip   = trim($_POST['nim_nip'] ?? '');
            $noHp     = trim($_POST['no_hp'] ?? '');
            $goldar   = trim($_POST['golongan_darah'] ?? '');
            $alergi   = trim($_POST['riwayat_alergi'] ?? '');

            $validGoldar = ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            $goldarVal   = in_array($goldar, $validGoldar, true) ? $goldar : null;

            if ($pasienId > 0) {
                $stmtUpd = $pdo->prepare("
                    UPDATE pasien
                       SET no_hp          = :no_hp,
                           golongan_darah = :goldar,
                           riwayat_alergi = :alergi
                     WHERE id = :id
                ");
                $stmtUpd->bindValue(':no_hp', $noHp !== '' ? $noHp : null, $noHp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtUpd->bindValue(':goldar', $goldarVal, $goldarVal !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtUpd->bindValue(':alergi', $alergi !== '' ? $alergi : null, $alergi !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtUpd->bindValue(':id', $pasienId, PDO::PARAM_INT);
                $stmtUpd->execute();

                $_SESSION['flash_success'] = "Data pasien berhasil diperbarui.";
            } else {
                if ($nimNip === '') {
                    throw new Exception("NIM atau NIP wajib diisi.");
                }
                $stmtIns = $pdo->prepare("
                    INSERT INTO pasien (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, no_hp, golongan_darah, riwayat_alergi)
                    VALUES (:nim_nip, '-', 'L', CURRENT_DATE, 'mahasiswa', :no_hp, :goldar, :alergi)
                    RETURNING nama
                ");
                $stmtIns->bindValue(':nim_nip', $nimNip, PDO::PARAM_STR);
                $stmtIns->bindValue(':no_hp', $noHp !== '' ? $noHp : null, $noHp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtIns->bindValue(':goldar', $goldarVal, $goldarVal !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtIns->bindValue(':alergi', $alergi !== '' ? $alergi : null, $alergi !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtIns->execute();
                $insertedRow = $stmtIns->fetch();

                $_SESSION['flash_success'] = "Data pasien " . ($insertedRow['nama'] ?? '') . " ({$nimNip}) berhasil ditambahkan.";
            }

        // TAMBAH / EDIT TENAGA LAYANAN (DOKTER / PSIKOLOG)
        } elseif ($action === 'save_dokter') {
            $dokterId = !empty($_POST['dokter_id']) ? (int) $_POST['dokter_id'] : 0;
            $nama     = trim($_POST['nama'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $poliId   = (int) ($_POST['poli_id'] ?? 0);
            $profesi  = strtolower(trim($_POST['profesi'] ?? ''));
            $noStr    = trim($_POST['no_str'] ?? '');
            $noHp     = trim($_POST['no_hp'] ?? '');

            if ($nama === '' or $username === '' or $poliId <= 0) {
                throw new Exception("Nama lengkap, username, dan poli layanan wajib diisi.");
            }
            if (!in_array($profesi, ['dokter umum', 'dokter gigi', 'psikolog'], true)) {
                throw new Exception("Pilihan profesi tidak sesuai.");
            }

            if ($dokterId > 0) {
                $stmtGet = $pdo->prepare("SELECT user_id FROM dokter WHERE id = :id");
                $stmtGet->execute([':id' => $dokterId]);
                $dRow = $stmtGet->fetch();
                if (!$dRow) {
                    throw new Exception("Data tenaga layanan tidak ditemukan.");
                }
                $uId = (int) $dRow['user_id'];

                if ($password !== '') {
                    if (strlen($password) < 8) {
                        throw new Exception("Kata sandi baru minimal terdiri dari 8 karakter.");
                    }
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u, password_hash = :p WHERE id = :id");
                    $stmtU->execute([
                        ':u'  => $username,
                        ':p'  => password_hash($password, PASSWORD_BCRYPT),
                        ':id' => $uId,
                    ]);
                } else {
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u WHERE id = :id");
                    $stmtU->execute([':u' => $username, ':id' => $uId]);
                }

                $stmtD = $pdo->prepare("
                    UPDATE dokter
                       SET poli_id = :poli_id,
                           nama    = :nama,
                           no_str  = :no_str,
                           no_hp   = :no_hp,
                           profesi = :profesi
                     WHERE id = :id
                ");
                $stmtD->bindValue(':poli_id', $poliId, PDO::PARAM_INT);
                $stmtD->bindValue(':nama', $nama, PDO::PARAM_STR);
                $stmtD->bindValue(':no_str', $noStr !== '' ? $noStr : null, $noStr !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtD->bindValue(':no_hp', $noHp !== '' ? $noHp : null, $noHp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtD->bindValue(':profesi', $profesi, PDO::PARAM_STR);
                $stmtD->bindValue(':id', $dokterId, PDO::PARAM_INT);
                $stmtD->execute();

                $_SESSION['flash_success'] = "Data tenaga layanan {$nama} berhasil diperbarui.";
            } else {
                if (strlen($password) < 8) {
                    throw new Exception("Kata sandi untuk akun baru wajib diisi minimal 8 karakter.");
                }

                $stmtU = $pdo->prepare("
                    INSERT INTO users (username, password_hash, role, is_active, must_change_password)
                    VALUES (:u, :p, 'dokter', TRUE, FALSE)
                    RETURNING id
                ");
                $stmtU->execute([
                    ':u' => $username,
                    ':p' => password_hash($password, PASSWORD_BCRYPT),
                ]);
                $uRow = $stmtU->fetch();
                $uId  = (int) $uRow['id'];

                $stmtD = $pdo->prepare("
                    INSERT INTO dokter (user_id, poli_id, nama, no_str, no_hp, profesi, is_active)
                    VALUES (:uid, :poli_id, :nama, :no_str, :no_hp, :profesi, TRUE)
                ");
                $stmtD->bindValue(':uid', $uId, PDO::PARAM_INT);
                $stmtD->bindValue(':poli_id', $poliId, PDO::PARAM_INT);
                $stmtD->bindValue(':nama', $nama, PDO::PARAM_STR);
                $stmtD->bindValue(':no_str', $noStr !== '' ? $noStr : null, $noStr !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtD->bindValue(':no_hp', $noHp !== '' ? $noHp : null, $noHp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtD->bindValue(':profesi', $profesi, PDO::PARAM_STR);
                $stmtD->execute();

                $_SESSION['flash_success'] = "Tenaga layanan {$nama} berhasil ditambahkan.";
            }

        // TAMBAH / EDIT POLI
        } elseif ($action === 'save_poli') {
            if (!$isSuperAdmin) {
                throw new Exception("Hanya Super Admin yang memiliki akses untuk menambah atau mengubah data Poli.");
            }

            $idPoli         = !empty($_POST['poli_id_PK']) ? (int) $_POST['poli_id_PK'] : 0;
            $namaPoli       = trim($_POST['nama'] ?? '');
            $butuhReservasi = (($_POST['butuh_reservasi'] ?? '0') === '1');
            $kuotaHarian    = $butuhReservasi ? max(1, (int) ($_POST['kuota_harian'] ?? 5)) : null;
            $durasiMenit    = !empty($_POST['durasi_menit']) ? max(5, (int) $_POST['durasi_menit']) : null;

            if ($namaPoli === '') {
                throw new Exception("Nama poli wajib diisi.");
            }

            if ($idPoli > 0) {
                $stmtP = $pdo->prepare("
                    UPDATE poli
                       SET nama            = :nama,
                           butuh_reservasi = :butuh,
                           kuota_harian    = :kuota,
                           durasi_menit    = :durasi
                     WHERE id = :id
                ");
                $stmtP->bindValue(':nama', $namaPoli, PDO::PARAM_STR);
                $stmtP->bindValue(':butuh', $butuhReservasi, PDO::PARAM_BOOL);
                $stmtP->bindValue(':kuota', $kuotaHarian, $kuotaHarian === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtP->bindValue(':durasi', $durasiMenit, $durasiMenit === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtP->bindValue(':id', $idPoli, PDO::PARAM_INT);
                $stmtP->execute();

                $_SESSION['flash_success'] = "Data {$namaPoli} berhasil diperbarui.";
            } else {
                $stmtP = $pdo->prepare("
                    INSERT INTO poli (nama, butuh_reservasi, kuota_harian, durasi_menit)
                    VALUES (:nama, :butuh, :kuota, :durasi)
                ");
                $stmtP->bindValue(':nama', $namaPoli, PDO::PARAM_STR);
                $stmtP->bindValue(':butuh', $butuhReservasi, PDO::PARAM_BOOL);
                $stmtP->bindValue(':kuota', $kuotaHarian, $kuotaHarian === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtP->bindValue(':durasi', $durasiMenit, $durasiMenit === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtP->execute();

                $_SESSION['flash_success'] = "Poli {$namaPoli} berhasil ditambahkan.";
            }

        // TAMBAH / EDIT ADMIN
        } elseif ($action === 'save_admin') {
            if (!$isSuperAdmin) {
                throw new Exception("Hanya Super Admin yang memiliki akses untuk mengelola akun Admin.");
            }

            $adminId  = !empty($_POST['admin_id']) ? (int) $_POST['admin_id'] : 0;
            $nimNip   = trim($_POST['nim_nip'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $noHp     = trim($_POST['no_hp'] ?? '');
            $isSuper  = (($_POST['is_super'] ?? '0') === '1');

            if ($username === '') {
                throw new Exception("Username wajib diisi.");
            }

            if ($adminId > 0) {
                if ($adminId === $currentAdminId && !$isSuper) {
                    throw new Exception("Anda tidak dapat menurunkan peran Super Admin milik Anda sendiri.");
                }

                $stmtGet = $pdo->prepare("SELECT user_id FROM admin WHERE id = :id");
                $stmtGet->execute([':id' => $adminId]);
                $aRow = $stmtGet->fetch();
                if (!$aRow) {
                    throw new Exception("Data admin tidak ditemukan.");
                }
                $uId = (int) $aRow['user_id'];

                if ($password !== '') {
                    if (strlen($password) < 8) {
                        throw new Exception("Kata sandi baru minimal terdiri dari 8 karakter.");
                    }
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u, password_hash = :p WHERE id = :id");
                    $stmtU->execute([
                        ':u'  => $username,
                        ':p'  => password_hash($password, PASSWORD_BCRYPT),
                        ':id' => $uId,
                    ]);
                } else {
                    $stmtU = $pdo->prepare("UPDATE users SET username = :u WHERE id = :id");
                    $stmtU->execute([':u' => $username, ':id' => $uId]);
                }

                $stmtA = $pdo->prepare("
                    UPDATE admin
                       SET no_hp    = :no_hp,
                           is_super = :is_super
                     WHERE id = :id
                ");
                $stmtA->bindValue(':no_hp', $noHp !== '' ? $noHp : null, $noHp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtA->bindValue(':is_super', $isSuper, PDO::PARAM_BOOL);
                $stmtA->bindValue(':id', $adminId, PDO::PARAM_INT);
                $stmtA->execute();

                $_SESSION['flash_success'] = "Data pengelola berhasil diperbarui.";
            } else {
                if ($nimNip === '' or strlen($password) < 8) {
                    throw new Exception("NIM/NIP civitas wajib diisi dan kata sandi minimal 8 karakter.");
                }

                $stmtCiv = $pdo->prepare("SELECT nama, status_keanggotaan FROM data_civitas WHERE nim_nip = :nim");
                $stmtCiv->execute([':nim' => $nimNip]);
                $civRow = $stmtCiv->fetch();

                if (!$civRow) {
                    throw new Exception("NIM/NIP {$nimNip} tidak ditemukan pada data civitas akademika.");
                }
                if ($civRow['status_keanggotaan'] !== 'aktif') {
                    throw new Exception("Civitas dengan NIM/NIP {$nimNip} sudah tidak berstatus aktif.");
                }

                $stmtU = $pdo->prepare("
                    INSERT INTO users (username, password_hash, role, is_active, must_change_password)
                    VALUES (:u, :p, 'admin', TRUE, FALSE)
                    RETURNING id
                ");
                $stmtU->execute([
                    ':u' => $username,
                    ':p' => password_hash($password, PASSWORD_BCRYPT),
                ]);
                $uRow = $stmtU->fetch();
                $uId  = (int) $uRow['id'];

                $stmtA = $pdo->prepare("
                    INSERT INTO admin (user_id, nama, nim_nip, is_super, no_hp)
                    VALUES (:uid, :nama, :nim_nip, :is_super, :no_hp)
                ");
                $stmtA->bindValue(':uid', $uId, PDO::PARAM_INT);
                $stmtA->bindValue(':nama', $civRow['nama'], PDO::PARAM_STR);
                $stmtA->bindValue(':nim_nip', $nimNip, PDO::PARAM_STR);
                $stmtA->bindValue(':is_super', $isSuper, PDO::PARAM_BOOL);
                $stmtA->bindValue(':no_hp', $noHp !== '' ? $noHp : null, $noHp !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmtA->execute();

                $_SESSION['flash_success'] = "Admin baru atas nama {$civRow['nama']} berhasil ditambahkan.";
            }

        // UBAH STATUS AKTIF / NON-AKTIF
        } elseif ($action === 'toggle_active') {
            $entity   = $_POST['entity'] ?? '';
            $targetId = (int) ($_POST['id'] ?? 0);
            $newState = ($_POST['new_state'] ?? '0') === '1';

            if ($entity === 'admin' && !$isSuperAdmin) {
                throw new Exception("Hanya Super Admin yang memiliki akses untuk mengubah status akun Admin.");
            }
            if ($entity === 'admin' && $targetId === $currentAdminId && !$newState) {
                throw new Exception("Anda tidak dapat menonaktifkan akun Anda sendiri.");
            }

            if ($entity === 'pasien') {
                $stmt = $pdo->prepare("UPDATE pasien SET is_active = :st WHERE id = :id RETURNING user_id");
                $stmt->bindValue(':st', $newState, PDO::PARAM_BOOL);
                $stmt->bindValue(':id', $targetId, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();

                if (!empty($res['user_id'])) {
                    $stmtU = $pdo->prepare("UPDATE users SET is_active = :st WHERE id = :uid");
                    $stmtU->bindValue(':st', $newState, PDO::PARAM_BOOL);
                    $stmtU->bindValue(':uid', (int) $res['user_id'], PDO::PARAM_INT);
                    $stmtU->execute();
                }
                $_SESSION['flash_success'] = $newState ? "Data pasien berhasil diaktifkan kembali." : "Data pasien berhasil dinonaktifkan.";

            } elseif ($entity === 'dokter') {
                $stmt = $pdo->prepare("UPDATE dokter SET is_active = :st WHERE id = :id RETURNING user_id");
                $stmt->bindValue(':st', $newState, PDO::PARAM_BOOL);
                $stmt->bindValue(':id', $targetId, PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();

                if (!empty($res['user_id'])) {
                    $stmtU = $pdo->prepare("UPDATE users SET is_active = :st WHERE id = :uid");
                    $stmtU->bindValue(':st', $newState, PDO::PARAM_BOOL);
                    $stmtU->bindValue(':uid', (int) $res['user_id'], PDO::PARAM_INT);
                    $stmtU->execute();
                }
                $_SESSION['flash_success'] = $newState ? "Data tenaga layanan berhasil diaktifkan kembali." : "Data tenaga layanan berhasil dinonaktifkan.";

            } elseif ($entity === 'admin') {
                $stmt = $pdo->prepare("SELECT user_id FROM admin WHERE id = :id");
                $stmt->execute([':id' => $targetId]);
                $res = $stmt->fetch();

                if (!empty($res['user_id'])) {
                    $stmtU = $pdo->prepare("UPDATE users SET is_active = :st WHERE id = :uid");
                    $stmtU->bindValue(':st', $newState, PDO::PARAM_BOOL);
                    $stmtU->bindValue(':uid', (int) $res['user_id'], PDO::PARAM_INT);
                    $stmtU->execute();
                }
                $_SESSION['flash_success'] = $newState ? "Akun admin berhasil diaktifkan kembali." : "Akun admin berhasil dinonaktifkan.";
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $rawMsg = $e->getMessage();
        if (str_contains($rawMsg, 'users_username_key')) {
            $_SESSION['flash_error'] = "Username tersebut sudah digunakan oleh akun lain.";
        } elseif (str_contains($rawMsg, 'dokter_no_str_key')) {
            $_SESSION['flash_error'] = "Nomor STR tersebut sudah terdaftar.";
        } elseif (str_contains($rawMsg, 'admin_nim_nip_key')) {
            $_SESSION['flash_error'] = "NIM/NIP tersebut sudah terdaftar sebagai Admin.";
        } elseif (str_contains($rawMsg, 'pasien_nim_nip_key')) {
            $_SESSION['flash_error'] = "Pasien dengan NIM/NIP tersebut sudah terdaftar.";
        } elseif (str_contains($rawMsg, 'poli_nama_key')) {
            $_SESSION['flash_error'] = "Nama poli tersebut sudah digunakan.";
        } elseif (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $rawMsg, $matches)) {
            $_SESSION['flash_error'] = trim($matches[1]);
        } else {
            $_SESSION['flash_error'] = $rawMsg;
        }
        error_log('[datamaster-post] ' . $rawMsg);
    }

    header("Location: datamaster.php?tab=" . urlencode($backTab));
    exit;
}

// =====================================================================
// PROSES GET: FILTER, STATISTIK COUNTER, DAN DAFTAR DATA MASTER
// =====================================================================
$search   = trim($_GET['q'] ?? '');
$status   = trim($_GET['status'] ?? '');
$civitas  = trim($_GET['civitas'] ?? '');
$poliId   = trim($_GET['poli_id'] ?? '');
$reserv   = trim($_GET['reservasi'] ?? '');
$tipeAdm  = trim($_GET['tipe_admin'] ?? '');
$detailId = isset($_GET['detail_id']) && ctype_digit((string) $_GET['detail_id']) ? (int) $_GET['detail_id'] : null;
$editId   = isset($_GET['edit_id']) && ctype_digit((string) $_GET['edit_id']) ? (int) $_GET['edit_id'] : null;

$totalPasien  = 0;
$totalDokter  = 0;
$totalPoli    = 0;
$totalAdmin   = 0;
$rows         = [];
$listPoli     = [];

$detailPasien = null;
$editPasien   = null;
$detailDokter = null;
$editDokter   = null;
$editPoli     = null;
$detailAdmin  = null;
$editAdmin    = null;

try {
    $stats = $pdo->query("
        SELECT 
            (
                SELECT COUNT(*)
                FROM admin a
                JOIN users u ON a.user_id = u.id
                WHERE u.is_active = TRUE
            ) AS total_admin,
            (
                SELECT COUNT(*)
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.is_active = TRUE AND (u.id IS NULL OR u.is_active = TRUE)
            ) AS total_pasien,
            (
                SELECT COUNT(*)
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                WHERE d.is_active = TRUE AND u.is_active = TRUE
            ) AS total_dokter,
            (
                SELECT COUNT(*)
                FROM poli
            ) AS total_poli
    ")->fetch();

    $totalAdmin  = (int) ($stats['total_admin'] ?? 0);
    $totalPasien = (int) ($stats['total_pasien'] ?? 0);
    $totalDokter = (int) ($stats['total_dokter'] ?? 0);
    $totalPoli   = (int) ($stats['total_poli'] ?? 0);

    $where  = [];
    $params = [];

    if ($activeTab === 'pasien') {
        if ($search !== '') {
            $where[]      = "(p.nama ILIKE :q OR p.nim_nip ILIKE :q OR COALESCE(p.no_hp, '') ILIKE :q OR COALESCE(u.username, '') ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if (in_array($civitas, ['mahasiswa', 'dosen', 'staf'], true)) {
            $where[]            = "p.status_civitas = :civitas";
            $params[':civitas'] = $civitas;
        }
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
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY (p.is_active AND COALESCE(u.is_active, TRUE)) DESC, p.nama ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($detailId !== null) {
            $stmtDet = $pdo->prepare("
                SELECT p.*, u.username, (p.is_active AND COALESCE(u.is_active, TRUE)) AS status_aktif
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = :id
            ");
            $stmtDet->execute([':id' => $detailId]);
            $detailPasien = $stmtDet->fetch();
        }
        if ($editId !== null) {
            $stmtEd = $pdo->prepare("
                SELECT p.*, u.username
                FROM pasien p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = :id
            ");
            $stmtEd->execute([':id' => $editId]);
            $editPasien = $stmtEd->fetch();
        }

    } elseif ($activeTab === 'tenaga-layanan') {
        $listPoli = $pdo->query("SELECT id, nama FROM poli ORDER BY id ASC")->fetchAll();

        if ($search !== '') {
            $where[]      = "(d.nama ILIKE :q OR COALESCE(d.no_str, '') ILIKE :q OR COALESCE(d.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if ($poliId !== '' && ctype_digit($poliId)) {
            $where[]            = "d.poli_id = :poli_id";
            $params[':poli_id'] = (int) $poliId;
        }
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
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY (d.is_active AND u.is_active) DESC, d.nama ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($detailId !== null) {
            $stmtDet = $pdo->prepare("
                SELECT d.*, po.nama AS nama_poli, u.username, (d.is_active AND u.is_active) AS status_aktif
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                JOIN poli po ON d.poli_id = po.id
                WHERE d.id = :id
            ");
            $stmtDet->execute([':id' => $detailId]);
            $detailDokter = $stmtDet->fetch();
        }
        if ($editId !== null) {
            $stmtEd = $pdo->prepare("
                SELECT d.*, u.username
                FROM dokter d
                JOIN users u ON d.user_id = u.id
                WHERE d.id = :id
            ");
            $stmtEd->execute([':id' => $editId]);
            $editDokter = $stmtEd->fetch();
        }

    } elseif ($activeTab === 'poli') {
        if ($search !== '') {
            $where[]      = "po.nama ILIKE :q";
            $params[':q'] = '%' . $search . '%';
        }
        if ($reserv === 'ya') {
            $where[] = "po.butuh_reservasi = TRUE";
        } elseif ($reserv === 'tidak') {
            $where[] = "po.butuh_reservasi = FALSE";
        }

        $sql = "
            SELECT po.id, po.nama, po.butuh_reservasi, po.kuota_harian, po.durasi_menit,
                   COUNT(d.id) FILTER (WHERE d.is_active = TRUE) AS jumlah_dokter
            FROM poli po
            LEFT JOIN dokter d ON d.poli_id = po.id
        ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " GROUP BY po.id ORDER BY po.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($editId !== null) {
            $stmtEd = $pdo->prepare("SELECT * FROM poli WHERE id = :id");
            $stmtEd->execute([':id' => $editId]);
            $editPoli = $stmtEd->fetch();
        }

    } elseif ($activeTab === 'admin') {
        if ($search !== '') {
            $where[]      = "(a.nama ILIKE :q OR COALESCE(a.nim_nip, '') ILIKE :q OR COALESCE(a.no_hp, '') ILIKE :q OR u.username ILIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }
        if ($tipeAdm === 'super') {
            $where[] = "a.is_super = TRUE";
        } elseif ($tipeAdm === 'regular') {
            $where[] = "a.is_super = FALSE";
        }
        if ($status === 'aktif') {
            $where[] = "u.is_active = TRUE";
        } elseif ($status === 'non-aktif') {
            $where[] = "u.is_active = FALSE";
        }

        $sql = "
            SELECT a.id, a.nama, a.nim_nip, a.no_hp, a.is_super,
                   u.username, u.is_active AS status_aktif,
                   dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin, dc.unit
            FROM admin a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
        ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY a.is_super DESC, u.is_active DESC, a.nama ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($detailId !== null) {
            $stmtDet = $pdo->prepare("
                SELECT a.*, u.username, u.is_active AS status_aktif,
                       dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin, dc.unit
                FROM admin a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
                WHERE a.id = :id
            ");
            $stmtDet->execute([':id' => $detailId]);
            $detailAdmin = $stmtDet->fetch();
        }
        if ($editId !== null) {
            $stmtEd = $pdo->prepare("
                SELECT a.*, u.username, dc.status_civitas, dc.tanggal_lahir, dc.jenis_kelamin
                FROM admin a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN data_civitas dc ON a.nim_nip = dc.nim_nip
                WHERE a.id = :id
            ");
            $stmtEd->execute([':id' => $editId]);
            $editAdmin = $stmtEd->fetch();
        }
    }

} catch (PDOException $e) {
    $errorDb = true;
    error_log('[datamaster-admin] ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | <?= e($app_name) ?></title>
    <link rel="stylesheet" href="./../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-page text-gray-800 antialiased">
    <div class="grid md:grid-cols-[16rem_minmax(0,1fr)] min-h-screen">

        <!-- Sidebar -->
        <?php require_once __DIR__ . "/components/sidebar.php" ?>

        <!-- Main Area -->
        <div class="flex min-w-0 flex-col w-full">

            <!-- Navbar -->
            <?php require_once __DIR__ . "/components/navbar.php" ?>

            <!-- Content -->
            <main class="flex-1 min-w-0 bg-page p-6 md:p-8">

                <?php if ($flashSuccess): ?>
                    <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 border border-emerald-300 rounded-base" role="alert">
                        <?= e($flashSuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        <?= e($flashError) ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorDb): ?>
                    <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 border border-red-300 rounded-base" role="alert">
                        Data master belum dapat dimuat saat ini. Silakan muat ulang halaman.
                    </div>
                <?php endif; ?>

                <div class="mb-4 text-sm text-gray-600 font-medium">
                    <p>Kelola data civitas akademika, tenaga layanan, dan poli &middot; Politeknik Negeri Malang</p>
                </div>

                <!-- Selector Tab Datamaster -->
                <div class="datamaster grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div>
                        <input type="radio" id="pasien" name="datamaster" value="pasien" class="hidden peer"
                            <?= $activeTab === 'pasien' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=pasien'" />
                        <label for="pasien" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Pasien</span>
                                <i class="fas fa-user-injured text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalPasien) ?></h3>
                            <span class="text-xs text-gray-500">Civitas Akademika Aktif</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" id="tenaga-layanan" name="datamaster" value="tenaga-layanan" class="hidden peer"
                            <?= $activeTab === 'tenaga-layanan' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=tenaga-layanan'" />
                        <label for="tenaga-layanan" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Dokter &amp; Psikolog</span>
                                <i class="fas fa-user-md text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalDokter) ?></h3>
                            <span class="text-xs text-gray-500">Tenaga Layanan Aktif</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" id="poli" name="datamaster" value="poli" class="hidden peer"
                            <?= $activeTab === 'poli' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=poli'" />
                        <label for="poli" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Poli Layanan</span>
                                <i class="far fa-hospital text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalPoli) ?></h3>
                            <span class="text-xs text-gray-500">Unit Layanan Medis</span>
                        </label>
                    </div>

                    <div>
                        <input type="radio" id="admin" name="datamaster" value="admin" class="hidden peer"
                            <?= $activeTab === 'admin' ? 'checked' : '' ?>
                            onchange="window.location.href='?tab=admin'" />
                        <label for="admin" class="flex flex-col justify-between w-full p-5 bg-white border border-gray-200 rounded-base shadow-sm cursor-pointer hover:bg-primary-50/40 peer-checked:bg-primary-50/70 peer-checked:border-primary transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-gray-700">Admin</span>
                                <i class="fas fa-user-shield text-primary-500"></i>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= e($totalAdmin) ?></h3>
                            <span class="text-xs text-gray-500">Pengelola Poliklinik</span>
                        </label>
                    </div>
                </div>

                <?php
                switch ($activeTab) {
                    case 'tenaga-layanan':
                        require_once __DIR__ . '/components/datamaster/tenaga-layanan/card-data-tenaga-layanan.php';
                        require_once __DIR__ . '/components/datamaster/tenaga-layanan/detail-tenaga-layanan.php';
                        require_once __DIR__ . '/components/datamaster/tenaga-layanan/form-data-tenaga-layanan.php';
                        break;
                    case 'poli':
                        require_once __DIR__ . '/components/datamaster/poli/card-data-poli.php';
                        require_once __DIR__ . '/components/datamaster/poli/form-data-poli.php';
                        break;
                    case 'admin':
                        require_once __DIR__ . '/components/datamaster/admin/card-data-admin.php';
                        require_once __DIR__ . '/components/datamaster/admin/detail-admin.php';
                        require_once __DIR__ . '/components/datamaster/admin/form-data-admin.php';
                        break;
                    case 'pasien':
                    default:
                        require_once __DIR__ . '/components/datamaster/pasien/card-data-pasien.php';
                        require_once __DIR__ . '/components/datamaster/pasien/detail-pasien.php';
                        require_once __DIR__ . '/components/datamaster/pasien/form-data-pasien.php';
                        break;
                }
                ?>
            </main>
        </div>
    </div>

    <script src="./../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>