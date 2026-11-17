-- =====================================================================
-- TABEL   : users
-- URUTAN  : tables/01
-- ISI     : tabel users, trigger trg_users_updated
-- DEPEND  : 00_common.sql
-- =====================================================================

-- users : akun login semua peran
CREATE TABLE users (
    id                    BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username              VARCHAR(50)  NOT NULL UNIQUE,
    password_hash         VARCHAR(255) NOT NULL,
    role                  VARCHAR(10)  NOT NULL
                          CHECK (role IN ('admin', 'dokter', 'pasien')),
    is_active             BOOLEAN      NOT NULL DEFAULT TRUE,
    must_change_password  BOOLEAN      NOT NULL DEFAULT FALSE,
    failed_login_count    SMALLINT     NOT NULL DEFAULT 0,
    locked_until          TIMESTAMPTZ,
    last_login_at         TIMESTAMPTZ,
    created_at            TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at            TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TRIGGER trg_users_updated BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
