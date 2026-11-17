-- =====================================================================
-- SEED    : data awal poli (SESUAIKAN kuota & durasi)
-- URUTAN  : seeds/01
-- ISI     : INSERT poli
-- DEPEND  : tables/02_poli.sql
-- =====================================================================

INSERT INTO poli (nama, butuh_reservasi, kuota_harian, durasi_menit) VALUES
    ('Poli Umum',  FALSE, NULL, NULL),
    ('Poli Gigi',  TRUE,  10,   30),
    ('Konseling',  TRUE,  6,    60)
ON CONFLICT (nama) DO NOTHING;
