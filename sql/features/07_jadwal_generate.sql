-- =====================================================================
-- FITUR   : Generate jadwal dari template mingguan
-- URUTAN  : features/07
-- ISI     : fn_generate_jadwal
-- DEPEND  : tables/06_jadwal, 07_jadwal_template
-- =====================================================================

-- F. Generate jadwal dari template. Jadwal hasil generate belum dipublikasi.
CREATE OR REPLACE FUNCTION fn_generate_jadwal(p_dari DATE, p_sampai DATE) RETURNS INT AS $$
DECLARE
    v_n INT;
BEGIN
    INSERT INTO jadwal (poli_id, dokter_id, tanggal, jam_mulai, jam_selesai, kuota)
    SELECT t.poli_id, t.dokter_id, d::date, t.jam_mulai, t.jam_selesai, t.kuota
      FROM jadwal_template t
      JOIN generate_series(p_dari::timestamp, p_sampai::timestamp, INTERVAL '1 day') d
        ON EXTRACT(ISODOW FROM d) = t.hari
     WHERE t.is_active
    ON CONFLICT DO NOTHING;
    GET DIAGNOSTICS v_n = ROW_COUNT;
    RETURN v_n;
END;
$$ LANGUAGE plpgsql;
