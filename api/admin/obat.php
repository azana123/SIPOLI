<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$in    = api_input();
$hasil = match (api_action($in, ['create_obat', 'atur_stok', 'toggle_active_obat'])) {
    'create_obat'        => ObatService::create($in),
    'atur_stok'          => ObatService::aturStok($in),
    'toggle_active_obat' => ObatService::toggleActive($in),
};

api_success($hasil, 'Data obat berhasil disimpan.');
