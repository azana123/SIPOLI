<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !is_string($sessionToken) ||
        $token === '' ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $token)
    ) {
        http_response_code(403);
        exit('Permintaan ditolak: token CSRF tidak valid atau kedaluwarsa.');
    }
}