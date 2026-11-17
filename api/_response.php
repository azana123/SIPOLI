<?php
declare(strict_types=1);

// Kirim satu respons JSON lalu hentikan eksekusi
function api_send(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_success(mixed $data = null, string $message = 'Berhasil.', int $status = 200): never {
    api_send($status, [
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ]);
}

// Terjemahkan exception menjadi payload + HTTP status.
// Semua pesan validasi dari trigger/function database (SQLSTATE P0001)
// dibaca di satu tempat ini.
function api_exception_payload(Throwable $e): array {
    if ($e instanceof ApiException) {
        return [$e->httpStatus, [
            'success' => false,
            'message' => $e->getMessage(),
            'errors'  => $e->errors,
        ]];
    }

    if ($e instanceof PDOException) {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $mentah   = $e->getMessage();

        if ($sqlState === 'P0001' && preg_match('/ERROR:\s*(.+?)(\n|CONTEXT:|$)/s', $mentah, $m)) {
            return [400, [
                'success' => false,
                'message' => trim($m[1]),
                'errors'  => [],
            ]];
        }

        if ($sqlState === '23505') {
            return [409, [
                'success' => false,
                'message' => api_pesan_duplikat($mentah),
                'errors'  => [],
            ]];
        }

        if ($sqlState === '23514' || $sqlState === '23503' || $sqlState === '22007') {
            return [400, [
                'success' => false,
                'message' => 'Nilai yang dikirim tidak memenuhi aturan data.',
                'errors'  => [],
            ]];
        }

        error_log('[api-pdo] ' . $mentah);
        return [500, [
            'success' => false,
            'message' => 'Terjadi kendala saat mengakses database.',
            'errors'  => [],
        ]];
    }

    error_log('[api] ' . $e->getMessage());
    return [500, [
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.',
        'errors'  => [],
    ]];
}

function api_pesan_duplikat(string $mentah): string {
    return match (true) {
        str_contains($mentah, 'users_username_key')      => 'Username sudah digunakan akun lain.',
        str_contains($mentah, 'dokter_no_str_key')       => 'Nomor STR sudah terdaftar.',
        str_contains($mentah, 'admin_nim_nip_key')       => 'NIM/NIP sudah terdaftar sebagai Admin.',
        str_contains($mentah, 'pasien_nim_nip_key')      => 'Pasien dengan NIM/NIP tersebut sudah terdaftar.',
        str_contains($mentah, 'poli_nama_key')           => 'Nama poli sudah digunakan.',
        str_contains($mentah, 'obat_kode_obat_key')      => 'Kode obat sudah digunakan.',
        str_contains($mentah, 'uq_jadwal')               => 'Jadwal untuk poli, tanggal, dan jam tersebut sudah ada.',
        str_contains($mentah, 'ex_jadwal_dokter_bentrok')=> 'Dokter sudah memiliki jadwal lain yang waktunya beririsan.',
        str_contains($mentah, 'uq_antrean_reservasi')    => 'Pasien pada reservasi ini sudah tercatat di antrean.',
        str_contains($mentah, 'uq_antrean_pasien_aktif') => 'Pasien sudah memiliki antrean aktif di poli ini hari ini.',
        str_contains($mentah, 'uq_reservasi_pasien_jadwal') => 'Pasien sudah memiliki reservasi pada jadwal ini.',
        default => 'Data yang sama sudah terdaftar.',
    };
}
