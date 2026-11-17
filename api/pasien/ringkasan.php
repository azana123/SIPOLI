<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('GET');
api_role('pasien');

api_success(PasienService::ringkasan());
