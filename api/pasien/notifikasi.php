<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('pasien');
api_write_guard();

$hasil = NotifikasiService::tandaiDibaca(api_input());

api_success($hasil, 'Notifikasi ditandai sudah dibaca.');
