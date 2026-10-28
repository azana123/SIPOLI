-- =====================================================================
-- TABEL   : dokter
-- URUTAN  : tables/05
-- ISI     : tabel dokter, index idx_dokter_poli
-- DEPEND  : tables/01_users.sql, tables/02_poli.sql
-- =====================================================================

-- dokter (termasuk psikolog)
CREATE TABLE dokter (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id    BIGINT       NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    poli_id    SMALLINT     NOT NULL REFERENCES poli(id),
    nama       VARCHAR(100) NOT NULL,
    no_str     VARCHAR(50)  UNIQUE,
    no_hp      VARCHAR(20),
    profesi    VARCHAR(20)  NOT NULL
               CHECK (profesi IN ('dokter umum', 'dokter gigi', 'psikolog')),
    is_active  BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_dokter_poli ON dokter (poli_id);
