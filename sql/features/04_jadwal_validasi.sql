-- =====================================================================
-- FITUR   : Validasi jadwal, hitung status buka/penuh, sinkron saat kuota berubah
-- URUTAN  : features/04
-- ISI     : fn_validasi_jadwal, trigger trg_validasi_jadwal, fn_hitung_status_jadwal, fn_jadwal_kuota_berubah, trigger trg_jadwal_kuota_berubah
-- DEPEND  : tables/02_poli, 05_dokter, 06_jadwal, 08_reservasi
-- =====================================================================

-- A. Validasi jadwal: kuota default dari poli, dokter harus sesuai poli,
--    kuota tidak boleh di bawah reservasi aktif
CREATE OR REPLACE FUNCTION fn_validasi_jadwal() RETURNS TRIGGER AS $$
DECLARE
    v_butuh        BOOLEAN;
    v_kuota_poli   SMALLINT;
    v_poli_dokter  SMALLINT;
    v_terpakai     INT;
BEGIN
    SELECT butuh_reservasi, kuota_harian INTO v_butuh, v_kuota_poli
      FROM poli WHERE id = NEW.poli_id;

    IF v_butuh THEN
        NEW.kuota := COALESCE(NEW.kuota, v_kuota_poli);
    ELSE
        NEW.kuota := NULL;
    END IF;

    IF NEW.dokter_id IS NOT NULL THEN
        SELECT poli_id INTO v_poli_dokter FROM dokter WHERE id = NEW.dokter_id;
        IF v_poli_dokter IS DISTINCT FROM NEW.poli_id THEN
            RAISE EXCEPTION 'Dokter (id=%) bukan dokter dari poli jadwal ini', NEW.dokter_id;
        END IF;
    END IF;

    IF TG_OP = 'UPDATE' AND NEW.kuota IS NOT NULL THEN
        SELECT COUNT(*) INTO v_terpakai
          FROM reservasi WHERE jadwal_id = NEW.id AND status <> 'dibatalkan';
        IF NEW.kuota < v_terpakai THEN
            RAISE EXCEPTION 'Kuota (%) tidak boleh di bawah jumlah reservasi aktif (%)',
                NEW.kuota, v_terpakai;
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validasi_jadwal
    BEFORE INSERT OR UPDATE OF poli_id, dokter_id, kuota ON jadwal
    FOR EACH ROW EXECUTE FUNCTION fn_validasi_jadwal();

-- B. Hitung ulang status buka <-> penuh (status 'tutup' tidak disentuh)
CREATE OR REPLACE FUNCTION fn_hitung_status_jadwal(p_jadwal_id BIGINT) RETURNS VOID AS $$
    UPDATE jadwal j
       SET status = CASE
               WHEN j.kuota IS NOT NULL
                AND (SELECT COUNT(*) FROM reservasi r
                      WHERE r.jadwal_id = j.id AND r.status <> 'dibatalkan') >= j.kuota
               THEN 'penuh' ELSE 'buka' END
     WHERE j.id = p_jadwal_id AND j.status <> 'tutup';
$$ LANGUAGE sql;

-- C. Kuota diubah -> status ikut diperbarui
CREATE OR REPLACE FUNCTION fn_jadwal_kuota_berubah() RETURNS TRIGGER AS $$
BEGIN
    PERFORM fn_hitung_status_jadwal(NEW.id);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_jadwal_kuota_berubah
    AFTER UPDATE OF kuota ON jadwal
    FOR EACH ROW WHEN (NEW.kuota IS DISTINCT FROM OLD.kuota)
    EXECUTE FUNCTION fn_jadwal_kuota_berubah();
