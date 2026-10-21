-- =====================================================================
-- TABEL  : reservasi (khusus Gigi & Konseling)
-- DEPEND : 03_pasien, 06_jadwal
-- FITUR  : features/01_kuota_reservasi.sql (validasi kuota & status jadwal)
-- =====================================================================
CREATE TABLE reservasi (
    id            BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    no_reservasi  VARCHAR(30) NOT NULL UNIQUE,
    pasien_id     BIGINT      NOT NULL REFERENCES pasien(id),
    jadwal_id     BIGINT      NOT NULL REFERENCES jadwal(id),
    keluhan       TEXT,
    status        VARCHAR(15) NOT NULL DEFAULT 'menunggu'
                  CHECK (status IN ('menunggu', 'dikonfirmasi', 'dibatalkan',
                                    'hadir', 'tidak_hadir', 'selesai')),
    created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- 1 pasien hanya boleh punya 1 reservasi aktif per jadwal
-- reservasi yang dibatalkan tidak dihitung, sehingga bisa reservasi ulang
CREATE UNIQUE INDEX uq_reservasi_pasien_jadwal
    ON reservasi (pasien_id, jadwal_id)
    WHERE status <> 'dibatalkan';

CREATE INDEX idx_reservasi_jadwal ON reservasi (jadwal_id);
CREATE INDEX idx_reservasi_pasien ON reservasi (pasien_id);

CREATE TRIGGER trg_reservasi_updated BEFORE UPDATE ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
