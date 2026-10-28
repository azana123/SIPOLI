-- =====================================================================
-- MODUL   : common (extension & fungsi util)
-- URUTAN  : 00
-- ISI     : btree_gist, fn_set_updated_at, fn_today
-- DEPEND  : -
-- =====================================================================

CREATE EXTENSION IF NOT EXISTS btree_gist;   -- untuk exclusion constraint jadwal dokter

-- Auto-update kolom updated_at
CREATE OR REPLACE FUNCTION fn_set_updated_at() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Tanggal hari ini menurut WIB (jangan pakai CURRENT_DATE, server Neon = UTC)
CREATE OR REPLACE FUNCTION fn_today() RETURNS DATE AS $$
    SELECT (NOW() AT TIME ZONE 'Asia/Jakarta')::date;
$$ LANGUAGE sql STABLE;

