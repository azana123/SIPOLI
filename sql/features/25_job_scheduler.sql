-- =====================================================================
-- FITUR   : Pintu masuk scheduler (fn_job_pagi, fn_job_malam)
-- URUTAN  : features/25
-- ISI     : fn_job_pagi, fn_job_malam
-- DEPEND  : features/21, 23, 24
-- =====================================================================

-- F. Pintu masuk untuk scheduler (dipanggil dari cron/pagi.php & cron/malam.php)
--    Urutan penting: batalkan dulu -> buat antrean -> baru kirim konfirmasi H-1
CREATE OR REPLACE FUNCTION fn_job_pagi() RETURNS JSONB AS $$
DECLARE
    v_batal INT; v_antrean INT; v_konfirmasi INT; v_kadaluarsa INT;
BEGIN
    v_batal      := fn_batalkan_tidak_konfirmasi();
    v_antrean    := fn_generate_antrean_harian();
    v_konfirmasi := fn_kirim_konfirmasi_h1();
    v_kadaluarsa := fn_cek_obat_kadaluarsa();
    RETURN jsonb_build_object('dibatalkan', v_batal, 'antrean_dibuat', v_antrean,
                              'konfirmasi_dikirim', v_konfirmasi, 'obat_kadaluarsa', v_kadaluarsa);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_job_malam() RETURNS JSONB AS $$
BEGIN
    RETURN jsonb_build_object('antrean_ditutup', fn_tutup_hari());
END;
$$ LANGUAGE plpgsql;
