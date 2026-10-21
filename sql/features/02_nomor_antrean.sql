-- =====================================================================
-- FITUR  : nomor antrean otomatis (reset per poli per hari)
-- CARA   : kirim no_antrean = 0 saat INSERT, trigger akan mengisinya.
--          Advisory lock mencegah nomor ganda saat input bersamaan.
-- DEPEND : tables/08_antrean
-- =====================================================================
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
