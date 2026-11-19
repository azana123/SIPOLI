<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('GET', 'POST');
api_role('pasien');

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    api_success(['notifikasi' => QueryPasienNotifikasi::daftar($userId)]);
}

api_write_guard();

$hasil = NotifikasiService::tandaiDibaca(api_input());

api_success($hasil, 'Notifikasi ditandai sudah dibaca.');
