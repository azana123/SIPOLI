-- =====================================================================
-- TABEL   : php_sessions
-- URUTAN  : tables/16
-- ISI     : tabel php_sessions, index idx_php_sessions_activity
-- DEPEND  : tables/01_users.sql
-- =====================================================================

-- php_sessions : sesi PHP di database (container hosting bisa restart,
-- sesi berbasis file akan hilang). Pakai SessionHandler kustom di PHP.
CREATE TABLE php_sessions (
    id             VARCHAR(128) PRIMARY KEY,
    user_id        BIGINT REFERENCES users(id) ON DELETE CASCADE,
    data           TEXT        NOT NULL DEFAULT '',
    last_activity  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_php_sessions_activity ON php_sessions (last_activity);
