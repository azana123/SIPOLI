<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$host    = getenv('DB_HOST') ?: 'localhost';
$port    = getenv('DB_PORT') ?: '5432';
$db      = getenv('DB_NAME') ?: 'db_sipoli';
$user    = getenv('DB_USER') ?: 'postgres';
$pass    = getenv('DB_PASSWORD') ?: 'postgres';
$sslmode = getenv('DB_SSLMODE') ?: 'prefer';

if (getenv('APP_ENV') === 'production' && (!getenv('DB_HOST') || !getenv('DB_PASSWORD'))) {
    error_log('Konfigurasi database production belum lengkap (DB_HOST / DB_PASSWORD).');
    http_response_code(500);
    exit('Konfigurasi sistem belum lengkap.');
}

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db;sslmode=$sslmode;connect_timeout=10",
        $user,
        $pass,
        $options
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}