<?php
declare(strict_types=1);

// Job malam. Jalankan dari CLI: php cron/malam.php <CRON_TOKEN>

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
    $hasil = $pdo->query('SELECT fn_job_malam()')->fetchColumn();
    echo "Job malam selesai: {$hasil}\n";
} catch (PDOException $e) {
    error_log('[cron-malam] ' . $e->getMessage());
    fwrite(STDERR, "Job malam gagal: " . $e->getMessage() . "\n");
    exit(1);
}
