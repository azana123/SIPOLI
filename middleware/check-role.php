<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';

// Halaman utama tiap role -> path dari root
const HOME_PER_ROLE = [
    'admin'  => 'admin/dashboard.php',
    'dokter' => 'dokter/dashboard.php',
    'pasien' => 'pasien/dashboard.php',
];

function redirect_by_role(string $role): void {
    if (!isset(HOME_PER_ROLE[$role])) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['profil_id']);
        $_SESSION['error'] = 'Role pengguna tidak valid.';
        redirect('auth/login.php');
    }
    redirect(HOME_PER_ROLE[$role]);
}

function check_role($allowed_roles, bool $json = false): void {
    global $pdo;
    $allowed = (array) $allowed_roles;

    // halaman terproteksi -> no chache
    header('Cache-Control: no-store');

    $tolak = static function (int $kode, string $pesan, string $tujuan) use ($json): void {
        if ($json) {
            http_response_code($kode);
            header('Content-Type: application/json');
            echo json_encode(['error' => $pesan]);
            exit;
        }
        redirect($tujuan);
    };

    $userId = $_SESSION['user_id'] ?? null;
    if ($userId === null) {
        $tolak(401, 'Belum login', 'auth/login.php');
    }

    $st = $pdo->prepare(
        'SELECT role, CASE WHEN is_active THEN 1 ELSE 0 END AS aktif FROM users WHERE id = :id'
    );
    $st->execute(['id' => $userId]);
    $user = $st->fetch();

    // akun dihapus / dinonaktifkan -> paksa logout
    if (!$user || (int) $user['aktif'] !== 1) {
        $_SESSION = [];
        session_destroy();
        $tolak(401, 'Akun tidak aktif', 'auth/login.php');
    }

    $_SESSION['role'] = $user['role'];

    if (!in_array($user['role'], $allowed, true)) {
        $tolak(403, 'Tidak punya akses', HOME_PER_ROLE[$user['role']] ?? 'auth/login.php');
    }
}