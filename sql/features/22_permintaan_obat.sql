-- =====================================================================
-- FITUR   : Resep -> permintaan obat + notifikasi admin apotek
-- URUTAN  : features/22
-- ISI     : fn_buat_permintaan_obat, trigger trg_buat_permintaan_obat
-- DEPEND  : tables/11_pemeriksaan, 12_resep_obat, 14_permintaan_obat; features/03_notifikasi_admin
-- =====================================================================

-- F. Resep pertama pada suatu pemeriksaan -> buat permintaan obat + notifikasi admin apotek
CREATE OR REPLACE FUNCTION fn_buat_permintaan_obat() RETURNS TRIGGER AS $$
DECLARE
    v_id     BIGINT;
    v_pasien VARCHAR(100);
BEGIN
    INSERT INTO permintaan_obat (pemeriksaan_id)
    VALUES (NEW.pemeriksaan_id)
    ON CONFLICT (pemeriksaan_id) DO NOTHING
    RETURNING id INTO v_id;

    IF v_id IS NOT NULL THEN
        SELECT ps.nama INTO v_pasien
          FROM pemeriksaan pm JOIN pasien ps ON ps.id = pm.pasien_id
         WHERE pm.id = NEW.pemeriksaan_id;

        PERFORM fn_notif_admin('permintaan_obat', 'Permintaan obat baru',
                               format('Resep untuk pasien %s menunggu disiapkan.', v_pasien),
                               'permintaan_obat', v_id);
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_buat_permintaan_obat
    AFTER INSERT ON resep_obat
    FOR EACH ROW EXECUTE FUNCTION fn_buat_permintaan_obat();
