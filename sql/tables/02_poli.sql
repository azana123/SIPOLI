-- =====================================================================
-- TABEL  : poli (Umum, Gigi, Konseling)
-- DEPEND : -
-- =====================================================================
CREATE TABLE poli (
    id               SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama             VARCHAR(50) NOT NULL UNIQUE,
    butuh_reservasi  BOOLEAN     NOT NULL DEFAULT FALSE,
    kuota_harian     SMALLINT,
    durasi_menit     SMALLINT,
    CONSTRAINT chk_poli_kuota CHECK (
        (butuh_reservasi = TRUE  AND kuota_harian IS NOT NULL AND kuota_harian > 0) OR
        (butuh_reservasi = FALSE AND kuota_harian IS NULL)
    )
);
