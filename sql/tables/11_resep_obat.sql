-- =====================================================================
-- TABEL  : resep_obat (detail obat per pemeriksaan, relasi M:N)
-- DEPEND : 09_obat, 10_pemeriksaan
-- FITUR  : features/03_stok_obat.sql (stok berkurang otomatis)
-- =====================================================================
CREATE TABLE resep_obat (
    id              BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pemeriksaan_id  BIGINT       NOT NULL REFERENCES pemeriksaan(id) ON DELETE CASCADE,
    obat_id         BIGINT       NOT NULL REFERENCES obat(id),
    jumlah          INT          NOT NULL CHECK (jumlah > 0),
    aturan_pakai    VARCHAR(100),            -- contoh: 3x1 sesudah makan
    CONSTRAINT uq_resep UNIQUE (pemeriksaan_id, obat_id)
);

CREATE INDEX idx_resep_obat ON resep_obat (obat_id);
