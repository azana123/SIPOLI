<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('pasien');
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
