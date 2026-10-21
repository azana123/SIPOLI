-- =====================================================================
-- TABEL  : antrean (dipakai semua poli)
-- DEPEND : 02_poli, 03_pasien, 04_admin, 06_jadwal, 07_reservasi
-- FITUR  : features/02_nomor_antrean.sql (no_antrean otomatis)
-- =====================================================================
CREATE TABLE antrean (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tanggal        DATE        NOT NULL DEFAULT CURRENT_DATE,
    no_antrean     INT         NOT NULL,
    pasien_id      BIGINT      NOT NULL REFERENCES pasien(id),
    poli_id        SMALLINT    NOT NULL REFERENCES poli(id),
    jadwal_id      BIGINT      REFERENCES jadwal(id),
    reservasi_id   BIGINT      REFERENCES reservasi(id),
    admin_id       BIGINT      NOT NULL REFERENCES admin(id),
    status         VARCHAR(10) NOT NULL DEFAULT 'menunggu'
                   CHECK (status IN ('menunggu', 'diperiksa', 'selesai', 'batal')),
    waktu_daftar   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    waktu_mulai    TIMESTAMPTZ,
    waktu_selesai  TIMESTAMPTZ,
    CONSTRAINT uq_antrean_no UNIQUE (poli_id, tanggal, no_antrean)
);

CREATE UNIQUE INDEX uq_antrean_reservasi
    ON antrean (reservasi_id) WHERE reservasi_id IS NOT NULL;

CREATE INDEX idx_antrean_list   ON antrean (poli_id, tanggal, status);
CREATE INDEX idx_antrean_pasien ON antrean (pasien_id);
