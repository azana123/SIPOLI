-- =====================================================================
-- TABEL   : permintaan_obat
-- URUTAN  : tables/14
-- ISI     : tabel permintaan_obat, index idx_permintaan_status, trigger trg_permintaan_updated
-- DEPEND  : 00_common.sql, tables/04_admin.sql, tables/11_pemeriksaan.sql
-- =====================================================================

-- permintaan_obat : status penyiapan & penyerahan obat di apotek
CREATE TABLE permintaan_obat (
    id                BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    pemeriksaan_id    BIGINT      NOT NULL UNIQUE REFERENCES pemeriksaan(id) ON DELETE CASCADE,
    status            VARCHAR(12) NOT NULL DEFAULT 'menunggu'
                      CHECK (status IN ('menunggu', 'disiapkan', 'diserahkan', 'dibatalkan')),
    diproses_oleh     BIGINT      REFERENCES admin(id),
    waktu_diserahkan  TIMESTAMPTZ,
    catatan           TEXT,
    created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at        TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_permintaan_status ON permintaan_obat (status, created_at);

CREATE TRIGGER trg_permintaan_updated BEFORE UPDATE ON permintaan_obat
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
