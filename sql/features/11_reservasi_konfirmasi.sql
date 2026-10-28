-- =====================================================================
-- FITUR   : Konfirmasi kehadiran pasien
-- URUTAN  : features/11
-- ISI     : fn_konfirmasi_reservasi
-- DEPEND  : tables/08_reservasi, 15_inbox
-- =====================================================================

-- Hasil: 'dikonfirmasi' | 'perlu_reschedule' | 'dibatalkan'
CREATE OR REPLACE FUNCTION fn_konfirmasi_reservasi(p_reservasi_id BIGINT, p_hadir BOOLEAN)
RETURNS TEXT AS $$
DECLARE
    r       reservasi%ROWTYPE;
    v_hasil TEXT;
BEGIN
    SELECT * INTO r FROM reservasi WHERE id = p_reservasi_id FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Reservasi tidak ditemukan';
    END IF;
    IF r.status NOT IN ('menunggu', 'dikonfirmasi') THEN
        RAISE EXCEPTION 'Reservasi berstatus % tidak dapat dikonfirmasi', r.status;
    END IF;

    IF p_hadir THEN
        UPDATE reservasi SET status = 'dikonfirmasi', dikonfirmasi_at = NOW()
         WHERE id = r.id;
        v_hasil := 'dikonfirmasi';
    ELSIF r.reschedule_count >= 1 THEN
        UPDATE reservasi SET status = 'dibatalkan', alasan_batal = 'pasien'
         WHERE id = r.id;
        v_hasil := 'dibatalkan';
    ELSE
        RETURN 'perlu_reschedule';          -- UI menampilkan menu reschedule
    END IF;

    UPDATE inbox SET aksi_selesai_at = NOW(), dibaca_at = COALESCE(dibaca_at, NOW())
     WHERE tipe = 'konfirmasi_kehadiran' AND ref_tipe = 'reservasi' AND ref_id = r.id
       AND aksi_selesai_at IS NULL;

    RETURN v_hasil;
END;
$$ LANGUAGE plpgsql;
