<?php
declare(strict_types=1);

// SERVICE LOAD
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/JadwalService.php';
require_once __DIR__ . '/ReservasiService.php';
require_once __DIR__ . '/DatamasterService.php';
require_once __DIR__ . '/KonfirmasiService.php';
require_once __DIR__ . '/ObatService.php';
require_once __DIR__ . '/ResepService.php';
require_once __DIR__ . '/NotifikasiService.php';
require_once __DIR__ . '/InformasiService.php';
require_once __DIR__ . '/PasienOfflineService.php';
require_once __DIR__ . '/PemeriksaanService.php';
require_once __DIR__ . '/JadwalDokterService.php';
require_once __DIR__ . '/FormatPasien.php';

// QUERY LOAD
require_once __DIR__ . '/query/QueryAdminDashboard.php';

require_once __DIR__ . '/query/QueryAdminDatamaster.php';

require_once __DIR__ . '/query/QueryAdminJadwal.php';

require_once __DIR__ . '/query/QueryAdminReservasi.php';

require_once __DIR__ . '/query/QueryAdminKonfirmasi.php';

require_once __DIR__ . '/query/QueryAdminObat.php';

require_once __DIR__ . '/query/QueryAdminResep.php';

require_once __DIR__ . '/query/QueryAdminNotifikasi.php';

require_once __DIR__ . '/query/QueryAdminInformasi.php';

require_once __DIR__ . '/query/QueryDokterDashboard.php';

require_once __DIR__ . '/query/QueryDokterDaftarPasien.php';

require_once __DIR__ . '/query/QueryDokterJadwal.php';

require_once __DIR__ . '/query/QueryDokterRiwayat.php';

require_once __DIR__ . '/query/QueryDokterDetailResep.php';

require_once __DIR__ . '/query/QueryAdminNavbar.php';

require_once __DIR__ . '/query/QueryAdminSidebar.php';

require_once __DIR__ . '/query/QueryDokterNavbar.php';

require_once __DIR__ . '/query/QueryDokterSidebar.php';

require_once __DIR__ . '/query/QueryAdminPasienOffline.php';

require_once __DIR__ . '/query/QueryDokterFormPeriksa.php';

require_once __DIR__ . '/query/QueryDokterPerubahanJadwal.php';

require_once __DIR__ . '/query/QueryDaftar.php';

require_once __DIR__ . '/query/QueryPasienBeranda.php';

require_once __DIR__ . '/query/QueryPasienJadwal.php';

require_once __DIR__ . '/query/QueryPasienDokter.php';

require_once __DIR__ . '/query/QueryPasienReservasi.php';

require_once __DIR__ . '/query/QueryPasienNotifikasi.php';
