-- =====================================================================
-- TABEL   : data_civitas
-- URUTAN  : tables/18
-- ISI     : tabel data_civitas, index idx_civitas_nama, index idx_civitas_status, trigger trg_civitas_updated
-- DEPEND  : 00_common.sql
-- =====================================================================


CREATE TABLE data_civitas (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nim_nip             VARCHAR(30)  NOT NULL UNIQUE,
    nama                VARCHAR(100) NOT NULL,
    jenis_kelamin       CHAR(1)      NOT NULL CHECK (jenis_kelamin IN ('L', 'P')),
    tanggal_lahir       DATE         NOT NULL CHECK (tanggal_lahir > DATE '1900-01-01'),
    status_civitas      VARCHAR(10)  NOT NULL
                        CHECK (status_civitas IN ('mahasiswa', 'dosen', 'staf')),
    unit                VARCHAR(100),                -- prodi (mahasiswa/dosen) atau unit kerja (staf)
    angkatan            SMALLINT,                    -- hanya mahasiswa
    status_keanggotaan  VARCHAR(10)  NOT NULL DEFAULT 'aktif'
                        CHECK (status_keanggotaan IN ('aktif', 'cuti', 'lulus', 'keluar', 'pensiun', 'nonaktif')),
    created_at          TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    -- sesuaikan pola ini dengan format NIM/NIP yang sebenarnya di kampus
    CONSTRAINT chk_civitas_nim_nip CHECK (nim_nip ~ '^[0-9]{8,20}$'),
    CONSTRAINT chk_civitas_angkatan CHECK (
        (status_civitas = 'mahasiswa' AND angkatan IS NOT NULL) OR
        (status_civitas <> 'mahasiswa' AND angkatan IS NULL)
    )
);
CREATE INDEX idx_civitas_nama   ON data_civitas (nama);
CREATE INDEX idx_civitas_status ON data_civitas (status_civitas, status_keanggotaan);

CREATE TRIGGER trg_civitas_updated BEFORE UPDATE ON data_civitas
    FOR EACH ROW EXECUTE FUNCTION fn_set_updated_at();
