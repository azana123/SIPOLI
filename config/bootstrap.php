<?php
declare(strict_types=1);

(static function (): void {
    $file = __DIR__ . '/../.env';
    if (!is_readable($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim(trim($value), "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
        }
    }
})();

// 2. BASE_URL: kosong jika aplikasi ada di root domain (php -S, hosting).
//    Jika dijalankan dari subfolder (mis. XAMPP: http://localhost/SIPOLI), isi
//    APP_BASE_URL=/SIPOLI di .env.
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim((string) getenv('APP_BASE_URL'), '/'));
}

function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function flash_pull(string $key): ?string {
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return is_string($value) ? $value : null;
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}