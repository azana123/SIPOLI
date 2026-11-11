-- =====================================================================
-- TABEL   : admin
-- URUTAN  : tables/04
-- ISI     : tabel admin
-- DEPEND  : tables/01_users.sql
-- =====================================================================

CREATE TABLE admin (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id    BIGINT       NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    nama       VARCHAR(100) NOT NULL,
    nim_nip    VARCHAR(30)  UNIQUE,
    is_super   BOOLEAN      NOT NULL DEFAULT FALSE,
    no_hp      VARCHAR(20),
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);