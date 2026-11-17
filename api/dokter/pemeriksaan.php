<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('dokter');
api_write_guard();

$in = api_input();
$aksi = api_str($in, 'action') ?: api_str($in, 'action_pemeriksaan');
if (!in_array($aksi, ['draft', 'selesai'], true)) {
    throw new ApiException('Aksi pemeriksaan tidak dikenal.');
}

$hasil = $aksi === 'draft'
    ? PemeriksaanService::draft($in)
    : PemeriksaanService::selesai($in);

api_success($hasil, $aksi === 'draft' ? 'Draf pemeriksaan tersimpan.' : 'Pemeriksaan berhasil diselesaikan.');
