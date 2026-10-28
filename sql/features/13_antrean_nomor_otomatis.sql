-- =====================================================================
-- FITUR   : Nomor antrean otomatis per poli per hari
-- URUTAN  : features/13
-- ISI     : fn_no_antrean_otomatis, trigger trg_no_antrean_otomatis
-- DEPEND  : tables/09_antrean
-- =====================================================================

-- A0. Nomor antrean otomatis (reset per poli per hari)
--     Kirim no_antrean = 0 saat INSERT, trigger akan mengisinya.
--     Advisory lock mencegah nomor ganda saat input bersamaan.
CREATE OR REPLACE FUNCTION fn_no_antrean_otomatis() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.no_antrean IS NULL OR NEW.no_antrean = 0 THEN
        PERFORM pg_advisory_xact_lock(NEW.poli_id, (NEW.tanggal - DATE '2000-01-01'));
        SELECT COALESCE(MAX(no_antrean), 0) + 1
          INTO NEW.no_antrean
          FROM antrean
         WHERE poli_id = NEW.poli_id AND tanggal = NEW.tanggal;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_no_antrean_otomatis
    BEFORE INSERT ON antrean
    FOR EACH ROW EXECUTE FUNCTION fn_no_antrean_otomatis();
