-- =====================================================================
-- TABEL   : jadwal_admin
-- URUTAN  : tables/20
-- ISI     : tabel jadwal_admin, index idx_jadwal_admin_tanggal
-- DEPEND  : tables/04_admin.sql
-- =====================================================================

-- Jadwal admin yang bertugas. Menentukan nomor siapa yang tampil di menu "Hubungi admin"
-- (lihat fn_kontak_admin). Beberapa admin boleh bertugas pada waktu yang sama,
-- tetapi satu admin tidak boleh punya dua jadwal yang beririsan.
CREATE TABLE jadwal_admin (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    admin_id     BIGINT      NOT NULL REFERENCES admin(id) ON DELETE CASCADE,
    tanggal      DATE        NOT NULL,
    jam_mulai    TIME        NOT NULL,
    jam_selesai  TIME        NOT NULL,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_jadwal_admin_jam CHECK (jam_selesai > jam_mulai),
    CONSTRAINT ex_jadwal_admin_bentrok EXCLUDE USING gist (
        admin_id WITH =,
        tsrange(tanggal + jam_mulai, tanggal + jam_selesai) WITH &&
    )
);

CREATE INDEX idx_jadwal_admin_tanggal ON jadwal_admin (tanggal);