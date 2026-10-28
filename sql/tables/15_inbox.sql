-- =====================================================================
-- TABEL   : inbox
-- URUTAN  : tables/15
-- ISI     : tabel inbox, index idx_inbox_user, index idx_inbox_belum_baca
-- DEPEND  : tables/01_users.sql
-- =====================================================================

-- inbox : notifikasi in-app
CREATE TABLE inbox (
    id               BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id          BIGINT       NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    tipe             VARCHAR(25)  NOT NULL
                     CHECK (tipe IN ('konfirmasi_kehadiran', 'info', 'permintaan_obat',
                                     'stok_minimum', 'obat_kadaluarsa')),
    judul            VARCHAR(150) NOT NULL,
    pesan            TEXT         NOT NULL,
    ref_tipe         VARCHAR(20),            -- reservasi, jadwal, obat, permintaan_obat
    ref_id           BIGINT,
    butuh_aksi       BOOLEAN      NOT NULL DEFAULT FALSE,
    aksi_selesai_at  TIMESTAMPTZ,
    dibaca_at        TIMESTAMPTZ,
    created_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_inbox_user       ON inbox (user_id, created_at DESC);

CREATE INDEX idx_inbox_belum_baca ON inbox (user_id) WHERE dibaca_at IS NULL;
