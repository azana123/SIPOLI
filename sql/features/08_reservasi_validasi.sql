-- =====================================================================
-- FITUR   : Validasi kuota, publikasi, jendela H-7..H-1, dan transisi status reservasi
-- URUTAN  : features/08
-- ISI     : fn_cek_kuota_reservasi, trigger trg_cek_kuota_reservasi, fn_validasi_status_reservasi, trigger trg_validasi_status_reservasi
-- DEPEND  : tables/02_poli, 06_jadwal, 08_reservasi
-- =====================================================================

-- A. Validasi kuota + jendela reservasi
CREATE OR REPLACE FUNCTION fn_cek_kuota_reservasi() RETURNS TRIGGER AS $$
DECLARE
    v_kuota    SMALLINT;
    v_status   VARCHAR(10);
    v_butuh    BOOLEAN;
    v_tanggal  DATE;
    v_pub      BOOLEAN;
    v_terpakai INT;
BEGIN
    SELECT COALESCE(j.kuota, p.kuota_harian), j.status, p.butuh_reservasi, j.tanggal, j.dipublikasi
      INTO v_kuota, v_status, v_butuh, v_tanggal, v_pub
      FROM jadwal j
      JOIN poli p ON p.id = j.poli_id
     WHERE j.id = NEW.jadwal_id
     FOR UPDATE OF j;                       -- lock jadwal: cegah race condition

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Jadwal tidak ditemukan';
    END IF;
    IF v_butuh IS NOT TRUE THEN
        RAISE EXCEPTION 'Poli pada jadwal ini tidak menerima reservasi';
    END IF;
    IF NOT v_pub THEN
        RAISE EXCEPTION 'Jadwal belum dipublikasikan';
    END IF;
    IF v_status = 'tutup' THEN
        RAISE EXCEPTION 'Jadwal sudah ditutup';
    END IF;
    IF v_tanggal < fn_today() + 1 OR v_tanggal > fn_today() + 7 THEN
        RAISE EXCEPTION 'Reservasi hanya dapat dibuat untuk H-7 sampai H-1 (jadwal tanggal %)', v_tanggal;
    END IF;

    SELECT COUNT(*) INTO v_terpakai
      FROM reservasi
     WHERE jadwal_id = NEW.jadwal_id AND status <> 'dibatalkan';

    IF v_terpakai >= v_kuota THEN
        RAISE EXCEPTION 'Kuota jadwal sudah penuh (% dari %)', v_terpakai, v_kuota;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_cek_kuota_reservasi
    BEFORE INSERT OR UPDATE OF jadwal_id ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_cek_kuota_reservasi();

-- C. Guard transisi status reservasi + timestamp pembatalan
CREATE OR REPLACE FUNCTION fn_validasi_status_reservasi() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = OLD.status THEN
        RETURN NEW;
    END IF;
    IF OLD.status IN ('dibatalkan', 'selesai', 'tidak_hadir') THEN
        RAISE EXCEPTION 'Reservasi berstatus % tidak dapat diubah lagi', OLD.status;
    END IF;
    IF NEW.status = 'dibatalkan' THEN
        NEW.dibatalkan_at := NOW();
        NEW.alasan_batal  := COALESCE(NEW.alasan_batal, 'admin');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validasi_status_reservasi
    BEFORE UPDATE OF status ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_validasi_status_reservasi();
