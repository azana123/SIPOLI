<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$hasil = InformasiService::kirim(api_input());

api_success($hasil, "Pengumuman berhasil dikirim kepada {$hasil['penerima']} pasien.");
