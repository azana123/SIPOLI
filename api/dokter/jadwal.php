<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('dokter');
api_write_guard();

$hasil = JadwalDokterService::ajukanPerubahan(api_input());

api_success($hasil, 'Pengajuan perubahan jadwal berhasil dikirim ke Admin.');
