-- =====================================================================
-- FITUR   : Sinkron status jadwal (buka/penuh) saat reservasi berubah
-- URUTAN  : features/10
-- ISI     : fn_sinkron_status_jadwal, trigger trg_sinkron_status_jadwal
-- DEPEND  : tables/08_reservasi; features/04_jadwal_validasi (fn_hitung_status_jadwal)
-- =====================================================================

-- D. Sinkron status jadwal (menangani INSERT, UPDATE status/jadwal_id, DELETE)
CREATE OR REPLACE FUNCTION fn_sinkron_status_jadwal() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP IN ('INSERT', 'UPDATE') THEN
        PERFORM fn_hitung_status_jadwal(NEW.jadwal_id);
    END IF;
    IF TG_OP = 'DELETE'
       OR (TG_OP = 'UPDATE' AND OLD.jadwal_id IS DISTINCT FROM NEW.jadwal_id) THEN
        PERFORM fn_hitung_status_jadwal(OLD.jadwal_id);   -- jadwal lama saat reschedule
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sinkron_status_jadwal
    AFTER INSERT OR UPDATE OF status, jadwal_id OR DELETE ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_sinkron_status_jadwal();
