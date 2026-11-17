<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$in     = api_input();
$hasil  = match (api_action($in, ['save_pasien', 'save_dokter', 'save_poli', 'save_admin', 'toggle_active'])) {
    'save_pasien'  => DatamasterService::simpanPasien($in),
    'save_dokter'  => DatamasterService::simpanDokter($in),
    'save_poli'    => DatamasterService::simpanPoli($in),
    'save_admin'   => DatamasterService::simpanAdmin($in),
    'toggle_active' => DatamasterService::toggleActive($in),
};

api_success($hasil, 'Data berhasil disimpan.');
