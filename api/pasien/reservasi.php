<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('GET', 'POST');
api_role('pasien');

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
    $id = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
    api_success($id > 0
        ? PasienService::detailReservasi($id)
        : PasienService::daftarReservasi());
}

api_write_guard();

$in    = api_input();
$aksi  = api_action($in, ['create', 'konfirmasi', 'batal', 'reschedule']);

$hasil = match ($aksi) {
    'create'      => ReservasiService::buat($in),
    'konfirmasi'  => ReservasiService::konfirmasi($in),
    'batal'       => ReservasiService::batalPasien($in),
    'reschedule'  => ReservasiService::reschedulePasien($in),
};

$pesan = match ($aksi) {
    'create'      => 'Reservasi berhasil dibuat.',
    'konfirmasi'  => $hasil['hasil'] === 'perlu_reschedule'
        ? 'Reservasi perlu dijadwalkan ulang.'
        : 'Konfirmasi kehadiran berhasil dicatat.',
    'batal'       => 'Reservasi berhasil dibatalkan.',
    'reschedule'  => 'Jadwal reservasi berhasil diubah.',
};

api_success($hasil, $pesan);
