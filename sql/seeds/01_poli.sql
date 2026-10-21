-- =====================================================================
-- SEED   : data awal poli
-- DEPEND : tables/02_poli
-- =====================================================================
INSERT INTO poli (nama, butuh_reservasi, kuota_harian, durasi_menit) VALUES
    ('Umum',      FALSE, NULL, 15),
    ('Gigi',      TRUE,  5,    60),
    ('Konseling', TRUE,  5,    60);
