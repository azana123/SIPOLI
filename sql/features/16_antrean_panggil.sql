-- =====================================================================
-- FITUR   : Panggil pasien berikutnya untuk satu dokter
-- URUTAN  : features/16
-- ISI     : fn_panggil_antrean_berikutnya
-- DEPEND  : tables/05_dokter, 06_jadwal, 09_antrean
-- =====================================================================

-- D. Panggil pasien berikutnya untuk satu dokter.
--    Return id antrean, atau NULL jika dokter masih menangani pasien / antrean kosong.
CREATE OR REPLACE FUNCTION fn_panggil_antrean_berikutnya(
    p_dokter_id BIGINT, p_tanggal DATE DEFAULT fn_today()
) RETURNS BIGINT AS $$
DECLARE
    v_poli SMALLINT;
    v_id   BIGINT;
BEGIN
    SELECT poli_id INTO v_poli FROM dokter WHERE id = p_dokter_id;
    IF v_poli IS NULL THEN
        RAISE EXCEPTION 'Dokter tidak ditemukan';
    END IF;

    IF EXISTS (SELECT 1 FROM antrean WHERE dokter_id = p_dokter_id AND status = 'diperiksa') THEN
        RETURN NULL;
    END IF;

    SELECT a.id INTO v_id
      FROM antrean a
      LEFT JOIN jadwal j ON j.id = a.jadwal_id
     WHERE a.poli_id = v_poli
       AND a.tanggal = p_tanggal
       AND a.status  = 'menunggu'
       AND (j.dokter_id IS NULL OR j.dokter_id = p_dokter_id)
     ORDER BY a.no_antrean
     LIMIT 1
     FOR UPDATE OF a SKIP LOCKED;

    IF v_id IS NULL THEN
        RETURN NULL;
    END IF;

    UPDATE antrean SET status = 'diperiksa', dokter_id = p_dokter_id WHERE id = v_id;
    RETURN v_id;
END;
$$ LANGUAGE plpgsql;
