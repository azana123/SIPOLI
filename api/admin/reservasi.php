<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_boot.php';

api_method('POST');
api_role('admin');
api_write_guard();

$in    = api_input();
$hasil = match (api_action($in, ['checkin', 'reschedule', 'batal_reservasi', 'batal_walkin'])) {
    'checkin'         => ReservasiService::checkin($in),
    'reschedule'      => ReservasiService::rescheduleAdmin($in),
    'batal_reservasi' => ReservasiService::batalAdmin($in),
    'batal_walkin'    => ReservasiService::batalWalkin($in),
};

api_success($hasil, 'Tindakan reservasi berhasil diproses.');
