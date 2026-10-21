-- =====================================================================
-- INSTALLER: jalankan dari folder ini dengan
--   psql -U <user> -d <database> -f install.sql
-- Semua modul dijalankan dalam satu transaksi (gagal satu = batal semua).
-- \ir = path relatif terhadap file ini.
-- =====================================================================
\set ON_ERROR_STOP on

BEGIN;

-- 0. Common
\ir 00_common.sql

-- 1. Tabel (urutan mengikuti dependensi foreign key)
\ir tables/01_users.sql
\ir tables/02_poli.sql
\ir tables/03_pasien.sql
\ir tables/04_admin.sql
\ir tables/05_dokter.sql
\ir tables/06_jadwal.sql
\ir tables/07_reservasi.sql
\ir tables/08_antrean.sql
\ir tables/09_obat.sql
\ir tables/10_pemeriksaan.sql
\ir tables/11_resep_obat.sql
\ir tables/12_log_stok_obat.sql

-- 2. Fitur (trigger & function). Boleh dimatikan satu per satu.
\ir features/01_kuota_reservasi.sql
\ir features/02_nomor_antrean.sql
\ir features/03_stok_obat.sql
\ir features/04_selesaikan_antrean.sql

-- 3. Data awal
\ir seeds/01_poli.sql

COMMIT;
