<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$in    = api_input();
$hasil = match (api_action($in, ['create', 'update', 'soft_delete'])) {
    'create'      => JadwalService::create($in),
    'update'      => JadwalService::update($in),
    'soft_delete' => JadwalService::softDelete($in),
};

api_success($hasil, 'Jadwal berhasil disimpan.');
