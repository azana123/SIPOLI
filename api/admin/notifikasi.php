<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$hasil = NotifikasiService::bacaSemua();

api_success($hasil, 'Seluruh notifikasi ditandai sudah dibaca.');
