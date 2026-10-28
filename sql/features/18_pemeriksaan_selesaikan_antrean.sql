-- =====================================================================
-- FITUR   : Submit pemeriksaan -> antrean & reservasi selesai -> panggil berikutnya
-- URUTAN  : features/18
-- ISI     : fn_selesaikan_antrean, trigger trg_selesaikan_antrean
-- DEPEND  : tables/08_reservasi, 09_antrean, 11_pemeriksaan; features/16_antrean_panggil
-- =====================================================================

-- B. Submit pemeriksaan -> antrean & reservasi selesai -> otomatis panggil pasien berikutnya
CREATE OR REPLACE FUNCTION fn_selesaikan_antrean() RETURNS TRIGGER AS $$
BEGIN
    UPDATE antrean
       SET status = 'selesai',
           waktu_selesai = NOW(),
           dokter_id = COALESCE(dokter_id, NEW.dokter_id)
     WHERE id = NEW.antrean_id;

    UPDATE reservasi
       SET status = 'selesai'
     WHERE id = (SELECT reservasi_id FROM antrean WHERE id = NEW.antrean_id);

    PERFORM fn_panggil_antrean_berikutnya(NEW.dokter_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_selesaikan_antrean
    AFTER INSERT ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_selesaikan_antrean();
