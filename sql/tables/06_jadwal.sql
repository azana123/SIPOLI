-- =====================================================================
-- TABEL   : jadwal
-- URUTAN  : tables/06
-- ISI     : tabel jadwal, index idx_jadwal_tanggal
-- DEPEND  : tables/02_poli.sql, tables/05_dokter.sql
-- =====================================================================

-- jadwal
-- dokter_id nullable: dokter Gigi/Konseling baru ditetapkan saat sudah
-- ada reservasi ("dokter datang jika ada reservasi")
CREATE TABLE jadwal (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    poli_id      SMALLINT    NOT NULL REFERENCES poli(id),
    dokter_id    BIGINT      REFERENCES dokter(id) ON DELETE SET NULL,
    tanggal      DATE        NOT NULL,
    jam_mulai    TIME        NOT NULL,
    jam_selesai  TIME        NOT NULL,
    kuota        SMALLINT    CHECK (kuota IS NULL OR kuota > 0),  -- NULL untuk poli umum
    status       VARCHAR(10) NOT NULL DEFAULT 'buka'
                 CHECK (status IN ('buka', 'penuh', 'tutup')),
    dipublikasi  BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_jadwal_jam CHECK (jam_selesai > jam_mulai),
    CONSTRAINT uq_jadwal UNIQUE (poli_id, tanggal, jam_mulai),
    -- satu dokter tidak boleh punya dua jadwal yang waktunya beririsan
    CONSTRAINT ex_jadwal_dokter_bentrok EXCLUDE USING gist (
        dokter_id WITH =,
        tsrange(tanggal + jam_mulai, tanggal + jam_selesai) WITH &&
    ) WHERE (dokter_id IS NOT NULL AND status <> 'tutup')
);

CREATE INDEX idx_jadwal_tanggal ON jadwal (tanggal, poli_id);
