-- =====================================================================
-- FITUR   : Reschedule reservasi (maksimal 1x)
-- URUTAN  : features/12
-- ISI     : fn_reschedule_reservasi
-- DEPEND  : tables/06_jadwal, 08_reservasi, 15_inbox; features/08_reservasi_validasi
-- =====================================================================

-- Reschedule hanya 1x, hanya ke jadwal poli yang sama
CREATE OR REPLACE FUNCTION fn_reschedule_reservasi(p_reservasi_id BIGINT, p_jadwal_baru_id BIGINT)
RETURNS VOID AS $$
DECLARE
    r          reservasi%ROWTYPE;
    v_poli_lama SMALLINT;
    v_poli_baru SMALLINT;
    v_tgl_baru  DATE;
BEGIN
    SELECT * INTO r FROM reservasi WHERE id = p_reservasi_id FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Reservasi tidak ditemukan';
    END IF;
    IF r.status NOT IN ('menunggu', 'dikonfirmasi') THEN
        RAISE EXCEPTION 'Reservasi berstatus % tidak dapat di-reschedule', r.status;
    END IF;
    IF r.reschedule_count >= 1 THEN
        RAISE EXCEPTION 'Reschedule hanya dapat dilakukan 1 kali';
    END IF;
    IF p_jadwal_baru_id = r.jadwal_id THEN
        RAISE EXCEPTION 'Jadwal baru sama dengan jadwal sekarang';
    END IF;

    SELECT poli_id INTO v_poli_lama FROM jadwal WHERE id = r.jadwal_id;
    SELECT poli_id, tanggal INTO v_poli_baru, v_tgl_baru FROM jadwal WHERE id = p_jadwal_baru_id;
    IF v_poli_baru IS DISTINCT FROM v_poli_lama THEN
        RAISE EXCEPTION 'Reschedule harus ke jadwal pada poli yang sama';
    END IF;

    -- trigger fn_cek_kuota_reservasi memvalidasi kuota, publikasi, dan jendela H-7..H-1
    UPDATE reservasi
       SET jadwal_awal_id        = r.jadwal_id,
           jadwal_id             = p_jadwal_baru_id,
           reschedule_count      = reschedule_count + 1,
           status                = CASE WHEN v_tgl_baru = fn_today() + 1
                                        THEN 'dikonfirmasi' ELSE 'menunggu' END,
           dikonfirmasi_at       = CASE WHEN v_tgl_baru = fn_today() + 1 THEN NOW() END,
           konfirmasi_diminta_at = NULL
     WHERE id = r.id;

    UPDATE inbox SET aksi_selesai_at = NOW(), dibaca_at = COALESCE(dibaca_at, NOW())
     WHERE tipe = 'konfirmasi_kehadiran' AND ref_tipe = 'reservasi' AND ref_id = r.id
       AND aksi_selesai_at IS NULL;
END;
$$ LANGUAGE plpgsql;
