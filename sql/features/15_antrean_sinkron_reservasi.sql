-- =====================================================================
-- FITUR   : Status antrean -> status reservasi (hadir / tidak_hadir)
-- URUTAN  : features/15
-- ISI     : fn_sinkron_reservasi_dari_antrean, trigger trg_sinkron_reservasi_dari_antrean
-- DEPEND  : tables/08_reservasi, 09_antrean
-- =====================================================================

-- C. Status antrean -> status reservasi (dipanggil = hadir, batal = tidak_hadir)
CREATE OR REPLACE FUNCTION fn_sinkron_reservasi_dari_antrean() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.reservasi_id IS NOT NULL THEN
        IF NEW.status = 'diperiksa' THEN
            UPDATE reservasi SET status = 'hadir'
             WHERE id = NEW.reservasi_id AND status = 'dikonfirmasi';
        ELSIF NEW.status = 'batal' THEN
            UPDATE reservasi SET status = 'tidak_hadir'
             WHERE id = NEW.reservasi_id AND status IN ('dikonfirmasi', 'hadir');
        END IF;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sinkron_reservasi_dari_antrean
    AFTER UPDATE OF status ON antrean
    FOR EACH ROW WHEN (NEW.status IN ('diperiksa', 'batal') AND NEW.status <> OLD.status)
    EXECUTE FUNCTION fn_sinkron_reservasi_dari_antrean();
