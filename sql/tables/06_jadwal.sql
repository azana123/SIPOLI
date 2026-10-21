-- =====================================================================
-- TABEL  : jadwal
-- CATATAN: dokter_id nullable, dokter Gigi/Konseling baru ditetapkan
--          saat sudah ada reservasi ("dokter datang jika ada reservasi")
-- DEPEND : 02_poli, 05_dokter
-- =====================================================================
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
    created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_jadwal_jam CHECK (jam_selesai > jam_mulai),
    CONSTRAINT uq_jadwal UNIQUE (poli_id, tanggal, jam_mulai)
);

CREATE INDEX idx_jadwal_tanggal ON jadwal (tanggal, poli_id);
