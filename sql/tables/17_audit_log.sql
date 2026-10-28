-- =====================================================================
-- TABEL   : audit_log
-- URUTAN  : tables/17
-- ISI     : tabel audit_log, index idx_audit_tabel_record
-- DEPEND  : -
-- =====================================================================

-- audit_log : jejak perubahan data penting (data medis wajib ditelusuri)
CREATE TABLE audit_log (
    id          BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tabel       VARCHAR(50) NOT NULL,
    record_id   BIGINT,
    aksi        VARCHAR(6)  NOT NULL,        -- INSERT / UPDATE / DELETE
    user_id     BIGINT,                      -- dari set_config('app.user_id', ...)
    data_lama   JSONB,
    data_baru   JSONB,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_audit_tabel_record ON audit_log (tabel, record_id, created_at DESC);
