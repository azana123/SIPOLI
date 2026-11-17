<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/csrf.php';
require_once __DIR__ . '/../middleware/check-role.php';

const PESAN_LOGIN_GAGAL = 'Username atau password salah, atau akun sedang tidak dapat digunakan.';

const HASH_DUMMY = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

function login_gagal(string $pesan): void {
    $_SESSION['error'] = $pesan;
    redirect('auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('auth/login.php');
}

csrf_verify();

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    login_gagal('Semua field harus diisi.');
}
if (strlen($username) > 50 || strlen($password) > 200) {
    login_gagal(PESAN_LOGIN_GAGAL);
}

try {
    $st = $pdo->prepare(
        "SELECT id, username, password_hash, role,
                CASE WHEN is_active THEN 1 ELSE 0 END AS aktif,
                CASE WHEN locked_until IS NOT NULL AND locked_until > NOW() THEN 1 ELSE 0 END AS terkunci
         FROM users
         WHERE username = :username"
    );
    $st->execute(['username' => $username]);
    $user = $st->fetch();

    $passwordBenar = password_verify($password, $user ? $user['password_hash'] : HASH_DUMMY);

    // user tidak ada / nonaktif / sedang terkunci (tidak menambah hitungan gagal)
    if (!$user || (int) $user['aktif'] !== 1 || (int) $user['terkunci'] === 1) {
        login_gagal(PESAN_LOGIN_GAGAL);
    }

    // password salah: tambah hitungan; 5x gagal -> database mengunci akun 15 menit
    if (!$passwordBenar) {
        $pdo->prepare('SELECT fn_login_gagal(:id)')->execute(['id' => $user['id']]);
        login_gagal(PESAN_LOGIN_GAGAL);
    }

    // ambil id profil (admin.id / dokter.id / pasien.id) sesuai role; nama tabel dari whitelist
    $tabelProfil = ['admin' => 'admin', 'dokter' => 'dokter', 'pasien' => 'pasien'];
    if (!isset($tabelProfil[$user['role']])) {
        login_gagal('Role pengguna tidak valid.');
    }
    $st = $pdo->prepare('SELECT id FROM ' . $tabelProfil[$user['role']] . ' WHERE user_id = :id');
    $st->execute(['id' => $user['id']]);
    $profil = $st->fetch();
    if (!$profil) {
        login_gagal('Profil akun belum lengkap. Hubungi admin.');
    }

    $pdo->prepare('SELECT fn_login_berhasil(:id)')->execute(['id' => $user['id']]);

    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);

    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['profil_id'] = (int) $profil['id'];

    redirect_by_role($user['role']);
} catch (PDOException $e) {
    error_log('[login] ' . $e->getMessage());
    login_gagal('Terjadi kesalahan pada sistem. Silakan coba kembali.');
}