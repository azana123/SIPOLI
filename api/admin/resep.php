<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$in    = api_input();
$hasil = ResepService::updateStatus($in);

api_success($hasil, 'Status resep berhasil diperbarui.');
