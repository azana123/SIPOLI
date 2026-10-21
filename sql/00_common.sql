-- =====================================================================
-- MODUL  : common
-- ISI    : fungsi util yang dipakai banyak tabel
-- DEPEND : -
-- =====================================================================

-- Auto-update kolom updated_at
CREATE OR REPLACE FUNCTION fn_set_updated_at() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
