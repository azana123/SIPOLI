-- =====================================================================
-- TABEL   : pemeriksaan
-- URUTAN  : tables/11
-- ISI     : tabel pemeriksaan, index idx_pemeriksaan_pasien, index idx_pemeriksaan_dokter, trigger trg_pemeriksaan_updated
-- DEPEND  : 00_common.sql, tables/02_poli.sql, tables/03_pasien.sql, tables/05_dokter.sql, tables/09_antrean.sql
-- =====================================================================

-- pemeriksaan (rekam medis)
CREATE TABLE pemeriksaan (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    antrean_id     BIGINT      NOT NULL UNIQUE REFERENCES antrean(id),
    pasien_id      BIGINT      NOT NULL REFERENCES pasien(id),
    dokter_id      BIGINT      NOT NULL REFERENCES dokter(id),
    poli_id        SMALLINT    NOT NULL REFERENCES poli(id),
    tanggal        DATE        NOT NULL DEFAULT fn_today(),
    keluhan        TEXT,
    tekanan_darah  VARCHAR(10),            -- contoh: 120/80
    suhu           NUMERIC(4,1),           -- derajat Celsius
    nadi           SMALLINT,               -- x/menit
    berat_badan    NUMERIC(5,2),           -- kg
    tinggi_badan   NUMERIC(5,2),           -- cm
    diagnosa       TEXT,
    tindakan       TEXT,
    catatan        TEXT,
    tindak_lanjut  TEXT,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at     TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_pem_diagnosa CHECK (diagnosa IS NOT NULL AND btrim(diagnosa) <> ''),
    CONSTRAINT chk_pem_td   CHECK (tekanan_darah IS NULL OR tekanan_darah ~ '^[0-9]{2,3}/[0-9]{2,3}$'),
    CONSTRAINT chk_pem_suhu CHECK (suhu IS NULL OR suhu BETWEEN 30 AND 45),
    CONSTRAINT chk_pem_nadi CHECK (nadi IS NULL OR nadi BETWEEN 20 AND 250),
    CONSTRAINT chk_pem_bb   CHECK (berat_badan  IS NULL OR berat_badan  BETWEEN 1 AND 500),
    CONSTRAINT chk_pem_tb   CHECK (tinggi_badan IS NULL OR tinggi_badan BETWEEN 30 AND 250)
);

CREATE INDEX idx_pemeriksaan_pasien ON pemeriksaan (pasien_id, tanggal DESC);

CREATE INDEX idx_pemeriksaan_dokter ON pemeriksaan (dokter_id, tanggal DESC);

CREATE TRIGGER trg_pemeriksaan_updated BEFORE UPDATE ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
