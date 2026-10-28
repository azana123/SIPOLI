-- =====================================================================
-- TABEL   : admin
-- URUTAN  : tables/04
-- ISI     : tabel admin
-- DEPEND  : tables/01_users.sql
-- =====================================================================

-- admin
CREATE TABLE admin (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id    BIGINT       NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    nama       VARCHAR(100) NOT NULL,
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
