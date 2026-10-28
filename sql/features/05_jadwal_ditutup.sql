-- =====================================================================
-- FITUR   : Jadwal ditutup: batalkan reservasi aktif + notifikasi pasien
-- URUTAN  : features/05
-- ISI     : fn_jadwal_ditutup, trigger trg_jadwal_ditutup
-- DEPEND  : tables/06_jadwal, 08_reservasi, 03_pasien, 15_inbox
-- =====================================================================

-- D. Jadwal ditutup -> reservasi aktif dibatalkan + pasien diberi tahu
CREATE OR REPLACE FUNCTION fn_jadwal_ditutup() RETURNS TRIGGER AS $$
BEGIN
    WITH batal AS (
        UPDATE reservasi
           SET status = 'dibatalkan', alasan_batal = 'jadwal_ditutup'
         WHERE jadwal_id = NEW.id AND status IN ('menunggu', 'dikonfirmasi')
        RETURNING id, pasien_id, no_reservasi
    )
    INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id)
    SELECT p.user_id, 'info', 'Reservasi dibatalkan',
           format('Reservasi %s dibatalkan karena jadwal tanggal %s ditutup oleh poliklinik. Silakan buat reservasi baru.',
                  b.no_reservasi, to_char(NEW.tanggal, 'DD-MM-YYYY')),
           'reservasi', b.id
      FROM batal b
      JOIN pasien p ON p.id = b.pasien_id
     WHERE p.user_id IS NOT NULL;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_jadwal_ditutup
    AFTER UPDATE OF status ON jadwal
    FOR EACH ROW WHEN (NEW.status = 'tutup' AND OLD.status <> 'tutup')
    EXECUTE FUNCTION fn_jadwal_ditutup();
