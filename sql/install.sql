-- =====================================================================
-- SIPOLI - INSTALL DATABASE (jalankan dari folder database/)
--
--   psql "$DATABASE_URL" -v ON_ERROR_STOP=1 -1 -f install.sql
--
-- -1 = seluruh file dijalankan dalam SATU transaksi (gagal -> rollback total).
-- Gunakan koneksi DIRECT Neon (bukan -pooler). Urutan di bawah = urutan wajib.
-- =====================================================================

\ir 00_common.sql
\ir tables/01_users.sql
\ir tables/02_poli.sql
\ir tables/03_pasien.sql
\ir tables/04_admin.sql
\ir tables/05_dokter.sql
\ir tables/06_jadwal.sql
\ir tables/07_jadwal_template.sql
\ir tables/08_reservasi.sql
\ir tables/09_antrean.sql
\ir tables/10_obat.sql
\ir tables/11_pemeriksaan.sql
\ir tables/12_resep_obat.sql
\ir tables/13_log_stok_obat.sql
\ir tables/14_permintaan_obat.sql
\ir tables/15_inbox.sql
\ir tables/16_php_sessions.sql
\ir tables/17_audit_log.sql
\ir tables/18_data_civitas.sql
\ir features/01_autentikasi.sql
\ir features/02_audit_log.sql
\ir features/03_notifikasi_admin.sql
\ir features/04_jadwal_validasi.sql
\ir features/05_jadwal_ditutup.sql
\ir features/06_jadwal_penugasan_dokter.sql
\ir features/07_jadwal_generate.sql
\ir features/08_reservasi_validasi.sql
\ir features/09_reservasi_nomor_otomatis.sql
\ir features/10_reservasi_sinkron_jadwal.sql
\ir features/11_reservasi_konfirmasi.sql
\ir features/12_reservasi_reschedule.sql
\ir features/13_antrean_nomor_otomatis.sql
\ir features/14_antrean_validasi.sql
\ir features/15_antrean_sinkron_reservasi.sql
\ir features/16_antrean_panggil.sql
\ir features/17_pemeriksaan_validasi.sql
\ir features/18_pemeriksaan_selesaikan_antrean.sql
\ir features/19_stok_obat_core.sql
\ir features/20_stok_resep.sql
\ir features/21_stok_peringatan.sql
\ir features/22_permintaan_obat.sql
\ir features/23_job_reservasi.sql
\ir features/24_job_antrean.sql
\ir features/25_job_scheduler.sql
\ir features/26_views_dashboard.sql
\ir features/27_validasi_civitas.sql
\ir seeds/01_poli.sql
\ir seeds/02_data_civitas.sql
