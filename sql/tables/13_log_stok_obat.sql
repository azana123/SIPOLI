-- =====================================================================
-- TABEL   : log_stok_obat
-- URUTAN  : tables/13
-- ISI     : tabel log_stok_obat, index idx_log_stok_obat
-- DEPEND  : tables/04_admin.sql, tables/10_obat.sql, tables/12_resep_obat.sql
-- =====================================================================

-- log_stok_obat (riwayat stok masuk/keluar)
CREATE TABLE log_stok_obat (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    obat_id        BIGINT      NOT NULL REFERENCES obat(id),
    jenis          VARCHAR(6)  NOT NULL CHECK (jenis IN ('masuk', 'keluar')),
    jumlah         INT         NOT NULL CHECK (jumlah > 0),
    stok_sebelum   INT,
    stok_sesudah   INT,
    resep_obat_id  BIGINT      REFERENCES resep_obat(id) ON DELETE SET NULL,
    admin_id       BIGINT      REFERENCES admin(id),
    keterangan     TEXT,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_log_stok_obat ON log_stok_obat (obat_id, created_at DESC);
