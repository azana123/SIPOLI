-- =====================================================================
-- TABEL   : obat
-- URUTAN  : tables/10
-- ISI     : tabel obat, index idx_obat_nama, trigger trg_obat_updated
-- DEPEND  : 00_common.sql
-- =====================================================================

-- obat
CREATE TABLE obat (
    id                 BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    kode_obat          VARCHAR(30)  NOT NULL UNIQUE,
    nama_obat          VARCHAR(150) NOT NULL,
    bentuk             VARCHAR(30),           -- tablet, sirup, salep, dll.
    satuan             VARCHAR(20)  NOT NULL, -- strip, botol, tube, dll.
    stok               INT          NOT NULL DEFAULT 0 CHECK (stok >= 0),
    stok_minimum       INT          NOT NULL DEFAULT 0 CHECK (stok_minimum >= 0),
    tanggal_kadaluarsa DATE,
    keterangan         TEXT,
    is_active          BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at         TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_obat_nama ON obat (nama_obat);

CREATE TRIGGER trg_obat_updated BEFORE UPDATE ON obat
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
