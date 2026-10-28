-- =====================================================================
-- FITUR   : Job: generate antrean harian & tutup hari
-- URUTAN  : features/24
-- ISI     : fn_generate_antrean_harian, fn_tutup_hari
-- DEPEND  : tables/08_reservasi, 09_antrean; features/14_antrean_validasi
-- =====================================================================

-- C. Buat antrean hari H dari reservasi terkonfirmasi, urut jam jadwal lalu waktu booking
CREATE OR REPLACE FUNCTION fn_generate_antrean_harian(p_tanggal DATE DEFAULT fn_today())
RETURNS INT AS $$
DECLARE
    v_n INT;
BEGIN
    INSERT INTO antrean (tanggal, no_antrean, pasien_id, poli_id, jadwal_id, reservasi_id, admin_id)
    SELECT j.tanggal, 0, r.pasien_id, j.poli_id, j.id, r.id, NULL
      FROM reservasi r
      JOIN jadwal j ON j.id = r.jadwal_id
     WHERE j.tanggal = p_tanggal
       AND r.status = 'dikonfirmasi'
       AND NOT EXISTS (SELECT 1 FROM antrean a WHERE a.reservasi_id = r.id)
     ORDER BY j.jam_mulai, r.created_at;
    GET DIAGNOSTICS v_n = ROW_COUNT;
    RETURN v_n;
END;
$$ LANGUAGE plpgsql;

-- D. Tutup hari: antrean yang belum selesai dibatalkan, reservasinya jadi tidak_hadir
CREATE OR REPLACE FUNCTION fn_tutup_hari(p_tanggal DATE DEFAULT fn_today()) RETURNS INT AS $$
DECLARE
    v_n INT;
BEGIN
    UPDATE antrean SET status = 'batal'
     WHERE tanggal = p_tanggal AND status IN ('menunggu', 'diperiksa');
    GET DIAGNOSTICS v_n = ROW_COUNT;

    UPDATE reservasi r SET status = 'tidak_hadir'
      FROM jadwal j
     WHERE j.id = r.jadwal_id AND j.tanggal = p_tanggal AND r.status = 'dikonfirmasi';

    RETURN v_n;
END;
$$ LANGUAGE plpgsql;
