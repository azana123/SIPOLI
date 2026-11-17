<?php
declare(strict_types=1);

require_once __DIR__ . '/_boot.php';

api_method('GET');

api_success(['csrf_token' => csrf_token()], 'Token CSRF siap digunakan.');
