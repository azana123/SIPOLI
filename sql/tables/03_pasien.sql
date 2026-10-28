-- =====================================================================
-- TABEL   : pasien
-- URUTAN  : tables/03
-- ISI     : tabel pasien, index idx_pasien_nama, trigger trg_pasien_updated
-- DEPEND  : 00_common.sql, tables/01_users.sql
-- =====================================================================

-- pasien
-- user_id nullable: pasien walk-in poli umum belum tentu punya akun
CREATE TABLE pasien (
    id               BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id          BIGINT UNIQUE REFERENCES users(id) ON DELETE SET NULL,
    nim_nip          VARCHAR(30)  NOT NULL UNIQUE,
    nama             VARCHAR(100) NOT NULL,
    jenis_kelamin    CHAR(1)      NOT NULL CHECK (jenis_kelamin IN ('L', 'P')),
    tanggal_lahir    DATE         NOT NULL,
    no_hp            VARCHAR(20),
    status_civitas   VARCHAR(10)  NOT NULL
                     CHECK (status_civitas IN ('mahasiswa', 'dosen', 'staf')),
    golongan_darah   VARCHAR(3)
                     CHECK (golongan_darah IN ('A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-')),
    riwayat_alergi   TEXT,
    is_active        BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_pasien_nama ON pasien (nama);

CREATE TRIGGER trg_pasien_updated BEFORE UPDATE ON pasien
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
