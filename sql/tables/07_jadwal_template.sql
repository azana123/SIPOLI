-- =====================================================================
-- TABEL   : jadwal_template
-- URUTAN  : tables/07
-- ISI     : tabel jadwal_template
-- DEPEND  : tables/02_poli.sql, tables/05_dokter.sql
-- =====================================================================

-- jadwal_template : template jadwal mingguan (dasar fn_generate_jadwal)
CREATE TABLE jadwal_template (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    poli_id      SMALLINT    NOT NULL REFERENCES poli(id),
    dokter_id    BIGINT      REFERENCES dokter(id) ON DELETE SET NULL,
    hari         SMALLINT    NOT NULL CHECK (hari BETWEEN 1 AND 7),  -- ISO: 1=Senin
    jam_mulai    TIME        NOT NULL,
    jam_selesai  TIME        NOT NULL,
    kuota        SMALLINT    CHECK (kuota IS NULL OR kuota > 0),
    is_active    BOOLEAN     NOT NULL DEFAULT TRUE,
    CONSTRAINT chk_jt_jam CHECK (jam_selesai > jam_mulai),
    CONSTRAINT uq_jt UNIQUE (poli_id, hari, jam_mulai)
);
