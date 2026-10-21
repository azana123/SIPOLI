-- =====================================================================
-- FITUR  : kuota reservasi Gigi & Konseling
-- ISI    : validasi kuota sebelum insert + sinkron status jadwal
-- DEPEND : tables/06_jadwal, tables/07_reservasi
-- =====================================================================

-- A. Validasi kuota (jadwal di-lock agar tidak terjadi race condition)
CREATE OR REPLACE FUNCTION fn_cek_kuota_reservasi() RETURNS TRIGGER AS $$
DECLARE
    v_kuota    SMALLINT;
    v_status   VARCHAR(10);
    v_butuh    BOOLEAN;
    v_terpakai INT;
BEGIN
    SELECT j.kuota, j.status, p.butuh_reservasi
      INTO v_kuota, v_status, v_butuh
      FROM jadwal j
      JOIN poli p ON p.id = j.poli_id
     WHERE j.id = NEW.jadwal_id
     FOR UPDATE OF j;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Jadwal tidak ditemukan';
    END IF;

    IF v_butuh IS NOT TRUE THEN
        RAISE EXCEPTION 'Poli pada jadwal ini tidak menerima reservasi';
    END IF;

    IF v_status = 'tutup' THEN
        RAISE EXCEPTION 'Jadwal sudah ditutup';
    END IF;

    SELECT COUNT(*) INTO v_terpakai
      FROM reservasi
     WHERE jadwal_id = NEW.jadwal_id
       AND status <> 'dibatalkan';

    IF v_terpakai >= v_kuota THEN
        RAISE EXCEPTION 'Kuota jadwal sudah penuh (% dari %)', v_terpakai, v_kuota;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_cek_kuota_reservasi
    BEFORE INSERT ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_cek_kuota_reservasi();

-- B. Sinkron jadwal.status (buka <-> penuh). Status 'tutup' tidak diubah.
CREATE OR REPLACE FUNCTION fn_sinkron_status_jadwal() RETURNS TRIGGER AS $$
DECLARE
    v_jadwal_id BIGINT := COALESCE(NEW.jadwal_id, OLD.jadwal_id);
    v_kuota     SMALLINT;
    v_terpakai  INT;
BEGIN
    SELECT kuota INTO v_kuota FROM jadwal WHERE id = v_jadwal_id;

    SELECT COUNT(*) INTO v_terpakai
      FROM reservasi
     WHERE jadwal_id = v_jadwal_id AND status <> 'dibatalkan';

    UPDATE jadwal
       SET status = CASE WHEN v_terpakai >= v_kuota THEN 'penuh' ELSE 'buka' END
     WHERE id = v_jadwal_id
       AND status <> 'tutup';

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sinkron_status_jadwal
    AFTER INSERT OR UPDATE OF status OR DELETE ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_sinkron_status_jadwal();
