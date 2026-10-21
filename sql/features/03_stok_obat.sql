-- =====================================================================
-- FITUR  : pengurangan stok obat otomatis saat resep disimpan
-- CATATAN: stok tidak cukup -> exception -> seluruh submit pemeriksaan
--          di-rollback. Jangan kurangi stok lagi dari kode aplikasi.
-- DEPEND : tables/09_obat, tables/11_resep_obat, tables/12_log_stok_obat
-- =====================================================================
CREATE OR REPLACE FUNCTION fn_kurangi_stok_obat() RETURNS TRIGGER AS $$
BEGIN
    UPDATE obat
       SET stok = stok - NEW.jumlah
     WHERE id = NEW.obat_id
       AND stok >= NEW.jumlah;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Stok obat (id=%) tidak mencukupi untuk jumlah %',
            NEW.obat_id, NEW.jumlah;
    END IF;

    INSERT INTO log_stok_obat (obat_id, jenis, jumlah, resep_obat_id, keterangan)
    VALUES (NEW.obat_id, 'keluar', NEW.jumlah, NEW.id,
            'Resep pemeriksaan #' || NEW.pemeriksaan_id);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_kurangi_stok_obat
    AFTER INSERT ON resep_obat
    FOR EACH ROW EXECUTE FUNCTION fn_kurangi_stok_obat();
