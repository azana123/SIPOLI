<?php
declare(strict_types=1);

// Job pagi. Jalankan dari CLI:
// php cron/pagi.php <CRON_TOKEN>
// Token dibaca dari .env (CRON_TOKEN). Tanpa token, script menolak berjalan.
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once dirname(__DIR__) . '/config/database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Job hanya boleh dijalankan dari CLI.');
}

$token = (string) getenv('CRON_TOKEN');
$berikan = (string) ($argv[1] ?? '');
if ($token === '' || $berikan === '' || !hash_equals($token, $berikan)) {
    fwrite(STDERR, "Token cron tidak valid.\n");
    exit(1);
}

try {
    $hasil = $pdo->query('SELECT fn_job_pagi()')->fetchColumn();
    echo "Job pagi selesai: {$hasil}\n";
} catch (PDOException $e) {
    error_log('[cron-pagi] ' . $e->getMessage());
    fwrite(STDERR, "Job pagi gagal: " . $e->getMessage() . "\n");
    exit(1);
}
