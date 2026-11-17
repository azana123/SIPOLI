<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/check-role.php';
require_once dirname(__DIR__) . '/middleware/csrf.php';
require_once dirname(__DIR__) . '/services/_load.php';
require_once __DIR__ . '/_response.php';
require_once __DIR__ . '/_input.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

set_exception_handler(static function (Throwable $e): void {
    [$status, $payload] = api_exception_payload($e);
    api_send($status, $payload);
});

// Set konteks user untuk audit log database
function api_audit_context(): void {
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid > 0) {
        db()->prepare("SELECT set_config('app.user_id', :uid, true)")
            ->execute([':uid' => (string) $uid]);
    }
}

// Jalankan satu unit kerja di dalam transaksi + konteks audit
function api_transaction(callable $kerja): mixed {
    $pdo = db();
    $pdo->beginTransaction();
    api_audit_context();
    try {
        $hasil = $kerja($pdo);
        $pdo->commit();
        return $hasil;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

// Gate role dengan respons JSON (mode json = true pada check_role)
function api_role(string|array $role): void { check_role($role, true); }

// CSRF request yang mengubah data
function api_write_guard(): void {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        csrf_verify_api();
    }
}

function api_method(string ...$diizinkan): void {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, $diizinkan, true)) {
        throw new ApiException('Metode HTTP tidak diizinkan.', 405);
    }
}

// ID profil sesuai role yang sedang login + verifikasi ke database
function profil_id_sesi(string $tabel): int {
    $whitelist = ['pasien', 'dokter', 'admin'];
    if (!in_array($tabel, $whitelist, true)) {
        throw new ApiException('Jenis profil tidak valid.', 500);
    }
    $uid = (int) ($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) {
        throw new ApiException('Sesi tidak valid.', 401);
    }
    $baris = db()->prepare("SELECT id FROM {$tabel} WHERE user_id = :uid AND is_active = TRUE");
    $baris->execute([':uid' => $uid]);
    return (int) ($baris->fetchColumn() ?: 0);
}
