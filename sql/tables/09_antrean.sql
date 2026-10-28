-- =====================================================================
-- TABEL   : antrean
-- URUTAN  : tables/09
-- ISI     : tabel antrean, index uq_antrean_reservasi, index uq_antrean_dokter_aktif, index uq_antrean_pasien_aktif, index idx_antrean_list, index idx_antrean_pasien
-- DEPEND  : 00_common.sql, tables/02_poli.sql, tables/03_pasien.sql, tables/04_admin.sql, tables/05_dokter.sql, tables/06_jadwal.sql, tables/08_reservasi.sql
-- =====================================================================

-- antrean (dipakai semua poli)
-- admin_id nullable: antrean reservasi dibuat oleh SISTEM
CREATE TABLE antrean (
    id             BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tanggal        DATE        NOT NULL DEFAULT fn_today(),
    no_antrean     INT         NOT NULL,
    pasien_id      BIGINT      NOT NULL REFERENCES pasien(id),
    poli_id        SMALLINT    NOT NULL REFERENCES poli(id),
    jadwal_id      BIGINT      REFERENCES jadwal(id),
    reservasi_id   BIGINT      REFERENCES reservasi(id),
    admin_id       BIGINT      REFERENCES admin(id),
    dokter_id      BIGINT      REFERENCES dokter(id),
    status         VARCHAR(10) NOT NULL DEFAULT 'menunggu'
                   CHECK (status IN ('menunggu', 'diperiksa', 'selesai', 'batal')),
    waktu_daftar   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    waktu_mulai    TIMESTAMPTZ,
    waktu_selesai  TIMESTAMPTZ,
    CONSTRAINT uq_antrean_no UNIQUE (poli_id, tanggal, no_antrean)
);

CREATE UNIQUE INDEX uq_antrean_reservasi
    ON antrean (reservasi_id) WHERE reservasi_id IS NOT NULL;

-- satu dokter hanya menangani satu pasien pada satu waktu
CREATE UNIQUE INDEX uq_antrean_dokter_aktif
    ON antrean (dokter_id) WHERE status = 'diperiksa' AND dokter_id IS NOT NULL;

-- satu pasien hanya satu antrean aktif per poli per hari
CREATE UNIQUE INDEX uq_antrean_pasien_aktif
    ON antrean (pasien_id, poli_id, tanggal) WHERE status <> 'batal';

CREATE INDEX idx_antrean_list   ON antrean (poli_id, tanggal, status);

CREATE INDEX idx_antrean_pasien ON antrean (pasien_id);
