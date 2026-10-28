-- =====================================================================
-- FITUR   : Notifikasi ke dokter saat ditugaskan ke jadwal
-- URUTAN  : features/06
-- ISI     : fn_notif_dokter_jadwal, trigger trg_notif_dokter_jadwal
-- DEPEND  : tables/05_dokter, 06_jadwal, 15_inbox
-- =====================================================================

-- E. Dokter ditugaskan ke jadwal -> notifikasi ke dokter
CREATE OR REPLACE FUNCTION fn_notif_dokter_jadwal() RETURNS TRIGGER AS $$
DECLARE
    v_user BIGINT;
BEGIN
    SELECT user_id INTO v_user FROM dokter WHERE id = NEW.dokter_id;
    INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id)
    VALUES (v_user, 'info', 'Penugasan jadwal praktik',
            format('Anda ditugaskan pada jadwal %s pukul %s-%s.',
                   to_char(NEW.tanggal, 'DD-MM-YYYY'),
                   to_char(NEW.jam_mulai, 'HH24:MI'),
                   to_char(NEW.jam_selesai, 'HH24:MI')),
            'jadwal', NEW.id);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_notif_dokter_jadwal
    AFTER UPDATE OF dokter_id ON jadwal
    FOR EACH ROW WHEN (NEW.dokter_id IS NOT NULL AND NEW.dokter_id IS DISTINCT FROM OLD.dokter_id)
    EXECUTE FUNCTION fn_notif_dokter_jadwal();
