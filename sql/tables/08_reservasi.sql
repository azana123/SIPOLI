-- =====================================================================
-- TABEL   : reservasi
-- URUTAN  : tables/08
-- ISI     : tabel reservasi, seq seq_no_reservasi, index uq_reservasi_pasien_jadwal, index idx_reservasi_jadwal, index idx_reservasi_pasien, trigger trg_reservasi_updated
-- DEPEND  : 00_common.sql, tables/03_pasien.sql, tables/06_jadwal.sql
-- =====================================================================

-- reservasi (khusus Gigi & Konseling)
CREATE TABLE reservasi (
    id                     BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    no_reservasi           VARCHAR(30) NOT NULL UNIQUE,   -- diisi trigger bila kosong
    pasien_id              BIGINT      NOT NULL REFERENCES pasien(id),
    jadwal_id              BIGINT      NOT NULL REFERENCES jadwal(id),
    keluhan                TEXT,
    status                 VARCHAR(15) NOT NULL DEFAULT 'menunggu'
                           CHECK (status IN ('menunggu', 'dikonfirmasi', 'dibatalkan',
                                             'hadir', 'tidak_hadir', 'selesai')),
    reschedule_count       SMALLINT    NOT NULL DEFAULT 0
                           CHECK (reschedule_count BETWEEN 0 AND 1),
    jadwal_awal_id         BIGINT      REFERENCES jadwal(id),
    konfirmasi_diminta_at  TIMESTAMPTZ,
    dikonfirmasi_at        TIMESTAMPTZ,
    dibatalkan_at          TIMESTAMPTZ,
    alasan_batal           VARCHAR(20)
                           CHECK (alasan_batal IN ('pasien', 'tidak_konfirmasi',
                                                   'jadwal_ditutup', 'admin')),
    created_at             TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at             TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE SEQUENCE seq_no_reservasi;

-- 1 pasien hanya boleh punya 1 reservasi aktif per jadwal
-- reservasi yang dibatalkan tidak dihitung, sehingga bisa reservasi ulang
CREATE UNIQUE INDEX uq_reservasi_pasien_jadwal
    ON reservasi (pasien_id, jadwal_id)
    WHERE status <> 'dibatalkan';

CREATE INDEX idx_reservasi_jadwal ON reservasi (jadwal_id);

CREATE INDEX idx_reservasi_pasien ON reservasi (pasien_id);

CREATE TRIGGER trg_reservasi_updated BEFORE UPDATE ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
