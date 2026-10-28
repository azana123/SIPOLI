-- =====================================================================
-- FITUR   : Peringatan stok minimum & obat hampir kadaluarsa
-- URUTAN  : features/21
-- ISI     : fn_peringatan_stok_minimum, trigger trg_peringatan_stok_minimum, fn_cek_obat_kadaluarsa
-- DEPEND  : tables/10_obat, 15_inbox; features/03_notifikasi_admin
-- =====================================================================

-- E. Stok menyentuh batas minimum / habis -> notifikasi ke semua admin (sekali saat melintasi batas)
CREATE OR REPLACE FUNCTION fn_peringatan_stok_minimum() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.stok <= NEW.stok_minimum AND OLD.stok > OLD.stok_minimum)
       OR (NEW.stok = 0 AND OLD.stok > 0) THEN
        PERFORM fn_notif_admin(
            'stok_minimum',
            CASE WHEN NEW.stok = 0 THEN 'Stok obat habis' ELSE 'Stok obat menyentuh batas minimum' END,
            format('%s (%s) tersisa %s %s, batas minimum %s.',
                   NEW.nama_obat, NEW.kode_obat, NEW.stok, NEW.satuan, NEW.stok_minimum),
            'obat', NEW.id);
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_peringatan_stok_minimum
    AFTER UPDATE OF stok, stok_minimum ON obat
    FOR EACH ROW WHEN (NEW.is_active)
    EXECUTE FUNCTION fn_peringatan_stok_minimum();

-- E. Peringatan obat mendekati kadaluarsa (maks 1 notifikasi per hari)
CREATE OR REPLACE FUNCTION fn_cek_obat_kadaluarsa(p_hari INT DEFAULT 30) RETURNS INT AS $$
DECLARE
    v_n      INT;
    v_daftar TEXT;
BEGIN
    SELECT COUNT(*),
           string_agg(nama_obat || ' (' || to_char(tanggal_kadaluarsa, 'DD-MM-YYYY') || ')',
                      ', ' ORDER BY tanggal_kadaluarsa)
      INTO v_n, v_daftar
      FROM obat
     WHERE is_active AND stok > 0
       AND tanggal_kadaluarsa IS NOT NULL
       AND tanggal_kadaluarsa <= fn_today() + p_hari;

    IF v_n > 0 AND NOT EXISTS (
        SELECT 1 FROM inbox
         WHERE tipe = 'obat_kadaluarsa'
           AND created_at >= (fn_today()::timestamp AT TIME ZONE 'Asia/Jakarta')
    ) THEN
        PERFORM fn_notif_admin('obat_kadaluarsa', 'Obat mendekati kadaluarsa',
                               v_n || ' obat kadaluarsa dalam ' || p_hari || ' hari: ' || v_daftar,
                               'obat', NULL);
    END IF;
    RETURN v_n;
END;
$$ LANGUAGE plpgsql;
