-- =====================================================================
-- TABEL  : log_stok_obat (riwayat stok masuk/keluar)
-- DEPEND : 09_obat, 11_resep_obat
-- =====================================================================
CREATE TABLE log_stok_obat (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    obat_id        BIGINT      NOT NULL REFERENCES obat(id),
    jenis          VARCHAR(6)  NOT NULL CHECK (jenis IN ('masuk', 'keluar')),
    jumlah         INT         NOT NULL CHECK (jumlah > 0),
    resep_obat_id  BIGINT      REFERENCES resep_obat(id) ON DELETE SET NULL,
    keterangan     TEXT,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_log_stok_obat ON log_stok_obat (obat_id, created_at DESC);
