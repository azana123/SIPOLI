<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$hasil = PasienOfflineService::daftar(api_input());

api_success(
    $hasil,
    "Pasien {$hasil['pasien']} berhasil didaftarkan ke {$hasil['poli']} dengan nomor antrean #{$hasil['no_antrean']}."
);
