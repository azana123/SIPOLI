-- =====================================================================
-- FITUR   : Stok berkurang/dikoreksi otomatis dari resep
-- URUTAN  : features/20
-- ISI     : fn_kurangi_stok_obat, trigger trg_kurangi_stok_obat, fn_koreksi_stok_resep, trigger trg_koreksi_stok_resep
-- DEPEND  : tables/12_resep_obat; features/19_stok_obat_core
-- =====================================================================

-- C. Resep disimpan -> stok berkurang
CREATE OR REPLACE FUNCTION fn_kurangi_stok_obat() RETURNS TRIGGER AS $$
BEGIN
    PERFORM fn_ubah_stok(NEW.obat_id, -NEW.jumlah, NEW.id, NULL,
                         'Resep pemeriksaan #' || NEW.pemeriksaan_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_kurangi_stok_obat
    AFTER INSERT ON resep_obat
    FOR EACH ROW EXECUTE FUNCTION fn_kurangi_stok_obat();

-- D. Resep diedit / dihapus -> stok dikembalikan lalu dihitung ulang
--    (saat DELETE, resep_obat_id diisi NULL karena barisnya sudah hilang -> cegah FK error)
CREATE OR REPLACE FUNCTION fn_koreksi_stok_resep() RETURNS TRIGGER AS $$
BEGIN
    PERFORM fn_ubah_stok(OLD.obat_id, OLD.jumlah,
                         CASE WHEN TG_OP = 'UPDATE' THEN OLD.id END, NULL,
                         'Koreksi resep pemeriksaan #' || OLD.pemeriksaan_id);
    IF TG_OP = 'UPDATE' THEN
        PERFORM fn_ubah_stok(NEW.obat_id, -NEW.jumlah, NEW.id, NULL,
                             'Resep (revisi) pemeriksaan #' || NEW.pemeriksaan_id);
        RETURN NEW;
    END IF;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_koreksi_stok_resep
    AFTER UPDATE OF obat_id, jumlah OR DELETE ON resep_obat
    FOR EACH ROW EXECUTE FUNCTION fn_koreksi_stok_resep();
