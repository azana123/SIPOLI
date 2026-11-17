<?php
declare(strict_types=1);

// Baca body JSON -> kalau kosong, fallback ke form POST
function api_input(): array {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $data  = [];
    $raw   = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $decode = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Body JSON tidak valid.', 400);
        }
        if (!is_array($decode)) {
            throw new ApiException('Body JSON harus berupa objek.', 400);
        }
        $data = $decode;
    }

    if ($data === []) {
        $data = $_POST;
    }
    if (!isset($data['action']) && isset($_GET['action'])) {
        $data['action'] = $_GET['action'];
    }

    return $cache = $data;
}

function api_str(array $in, string $key, string $default = ''): string {
    $value = $in[$key] ?? $default;
    return is_scalar($value) ? trim((string) $value) : $default;
}

function api_int(array $in, string $key, int $default = 0): int {
    $value = $in[$key] ?? $default;
    return is_numeric($value) ? (int) $value : $default;
}

function api_float(array $in, string $key, float $default = 0.0): float {
    $value = str_replace(',', '.', (string) ($in[$key] ?? ''));
    return is_numeric($value) ? (float) $value : $default;
}

function api_bool(array $in, string $key, bool $default = false): bool {
    if (!array_key_exists($key, $in)) {
        return $default;
    }
    $value = $in[$key];
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
}

function api_nullable_str(array $in, string $key): ?string {
    $value = api_str($in, $key);
    return $value === '' ? null : $value;
}

function api_action(array $in, array $allowed): string
{
    $action = api_str($in, 'action');
    if (!in_array($action, $allowed, true)) {
        throw new ApiException('Aksi tidak dikenal.', 400);
    }
    return $action;
}
