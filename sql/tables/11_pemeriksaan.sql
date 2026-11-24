-- =====================================================================
-- TABEL   : pemeriksaan
-- URUTAN  : tables/11
-- ISI     : tabel pemeriksaan, index, trigger trg_pemeriksaan_updated
-- DEPEND  : 00_common.sql, tables/02_poli.sql, tables/03_pasien.sql,
--           tables/05_dokter.sql, tables/09_antrean.sql
-- =====================================================================

-- pemeriksaan (rekam medis)
-- Semua data klinis (keluhan, diagnosa, vital signs, catatan, dll.)
-- disimpan dalam data_pemeriksaan (JSONB) untuk fleksibilitas per poli.
-- Poli Umum, Gigi, dan Konseling masing-masing simpan field yang
-- relevan.
CREATE TABLE pemeriksaan (
    id               BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    antrean_id       BIGINT      NOT NULL UNIQUE REFERENCES antrean(id),
    pasien_id        BIGINT      NOT NULL REFERENCES pasien(id),
    dokter_id        BIGINT      NOT NULL REFERENCES dokter(id),
    poli_id          SMALLINT    NOT NULL REFERENCES poli(id),
    tanggal          DATE        NOT NULL DEFAULT fn_today(),
    data_pemeriksaan JSONB       NOT NULL DEFAULT '{}'::jsonb,
    created_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_pemeriksaan_pasien ON pemeriksaan (pasien_id, tanggal DESC);

CREATE INDEX idx_pemeriksaan_dokter ON pemeriksaan (dokter_id, tanggal DESC);

-- Indeks GIN untuk query data di dalam JSONB (misal: cari diagnosa)
CREATE INDEX idx_pemeriksaan_data   ON pemeriksaan USING GIN (data_pemeriksaan);

CREATE TRIGGER trg_pemeriksaan_updated BEFORE UPDATE ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
