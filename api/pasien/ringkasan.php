<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('GET');
api_role('pasien');

$pasienId = profil_id_sesi('pasien');
$userId   = (int) ($_SESSION['user_id'] ?? 0);

api_success(QueryPasienBeranda::ringkasan($pasienId, $userId));
