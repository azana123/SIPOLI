-- =====================================================================
-- TABEL  : pemeriksaan (riwayat pemeriksaan)
-- DEPEND : 02_poli, 03_pasien, 05_dokter, 08_antrean
-- FITUR  : features/04_selesaikan_antrean.sql
-- =====================================================================
CREATE TABLE pemeriksaan (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    antrean_id     BIGINT      NOT NULL UNIQUE REFERENCES antrean(id),
    pasien_id      BIGINT      NOT NULL REFERENCES pasien(id),
    dokter_id      BIGINT      NOT NULL REFERENCES dokter(id),
    poli_id        SMALLINT    NOT NULL REFERENCES poli(id),
    tanggal        DATE        NOT NULL DEFAULT CURRENT_DATE,
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
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_pemeriksaan_pasien ON pemeriksaan (pasien_id, tanggal DESC);
CREATE INDEX idx_pemeriksaan_dokter ON pemeriksaan (dokter_id, tanggal DESC);
