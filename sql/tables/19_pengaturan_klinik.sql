-- =====================================================================
-- TABEL   : pengaturan_klinik
-- URUTAN  : tables/19
-- ISI     : tabel pengaturan_klinik, trigger trg_pengaturan_updated
-- DEPEND  : 00_common.sql
-- =====================================================================

CREATE TABLE pengaturan_klinik (
    kunci       VARCHAR(50)  PRIMARY KEY,
    nilai       TEXT         NOT NULL DEFAULT '',
    keterangan  VARCHAR(200),
    updated_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_pengaturan_updated BEFORE UPDATE ON pengaturan_klinik
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();