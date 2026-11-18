<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

// Jadwal dokter bersifat publik: boleh dilihat tanpa login.
api_method('GET');

$tanggal = isset($_GET['tanggal']) ? (string) $_GET['tanggal'] : null;
$poliId  = isset($_GET['poli_id']) && ctype_digit((string) $_GET['poli_id'])
    ? (int) $_GET['poli_id'] : 0;
$bulan   = isset($_GET['m']) && ctype_digit((string) $_GET['m']) ? (int) $_GET['m'] : (int) date('n');
$tahun   = isset($_GET['y']) && ctype_digit((string) $_GET['y']) ? (int) $_GET['y'] : (int) date('Y');

api_success([
    'jadwal'   => PasienService::daftarJadwal($tanggal, $poliId),
    'kalender' => PasienService::kalender($bulan, $tahun),
]);
