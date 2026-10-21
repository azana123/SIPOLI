-- =====================================================================
-- FITUR  : submit pemeriksaan -> antrean (dan reservasi) otomatis selesai
-- DEPEND : tables/07_reservasi, tables/08_antrean, tables/10_pemeriksaan
-- =====================================================================
CREATE OR REPLACE FUNCTION fn_selesaikan_antrean() RETURNS TRIGGER AS $$
BEGIN
    UPDATE antrean
       SET status = 'selesai',
           waktu_selesai = NOW()
     WHERE id = NEW.antrean_id;

    UPDATE reservasi
       SET status = 'selesai'
     WHERE id = (SELECT reservasi_id FROM antrean WHERE id = NEW.antrean_id);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_selesaikan_antrean
    AFTER INSERT ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_selesaikan_antrean();
