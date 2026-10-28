-- =====================================================================
-- FITUR   : Validasi pembuatan antrean + guard transisi status
-- URUTAN  : features/14
-- ISI     : fn_validasi_antrean, trigger trg_01_validasi_antrean, fn_transisi_antrean, trigger trg_transisi_antrean
-- DEPEND  : tables/02_poli, 06_jadwal, 08_reservasi, 09_antrean
-- =====================================================================

-- A. Validasi saat insert. Nama diawali 01_ agar jalan SEBELUM trg_no_antrean_otomatis
--    (trigger BEFORE dieksekusi urut abjad).
CREATE OR REPLACE FUNCTION fn_validasi_antrean() RETURNS TRIGGER AS $$
DECLARE
    v_butuh BOOLEAN;
    rv      RECORD;
BEGIN
    SELECT butuh_reservasi INTO v_butuh FROM poli WHERE id = NEW.poli_id;

    IF NEW.reservasi_id IS NULL THEN
        IF v_butuh THEN
            RAISE EXCEPTION 'Poli ini hanya melayani pasien dengan reservasi';
        END IF;
        IF NEW.admin_id IS NULL THEN
            RAISE EXCEPTION 'Antrean walk-in harus dibuat oleh admin';
        END IF;
    ELSE
        SELECT r.pasien_id, r.status, j.id AS jadwal_id, j.poli_id, j.tanggal
          INTO rv
          FROM reservasi r
          JOIN jadwal j ON j.id = r.jadwal_id
         WHERE r.id = NEW.reservasi_id;

        IF NOT FOUND THEN
            RAISE EXCEPTION 'Reservasi tidak ditemukan';
        END IF;
        IF rv.status <> 'dikonfirmasi' THEN
            RAISE EXCEPTION 'Reservasi belum dikonfirmasi (status: %)', rv.status;
        END IF;
        IF rv.tanggal <> fn_today() THEN
            RAISE EXCEPTION 'Antrean hanya dapat dibuat pada hari H reservasi (%)', rv.tanggal;
        END IF;
        -- data diturunkan dari reservasi agar selalu konsisten
        NEW.pasien_id := rv.pasien_id;
        NEW.poli_id   := rv.poli_id;
        NEW.jadwal_id := rv.jadwal_id;
        NEW.tanggal   := rv.tanggal;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_01_validasi_antrean
    BEFORE INSERT ON antrean
    FOR EACH ROW EXECUTE FUNCTION fn_validasi_antrean();

-- B. Guard transisi status + isi waktu otomatis
CREATE OR REPLACE FUNCTION fn_transisi_antrean() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = OLD.status THEN
        RETURN NEW;
    END IF;
    IF NOT ((OLD.status = 'menunggu'  AND NEW.status IN ('diperiksa', 'batal'))
         OR (OLD.status = 'diperiksa' AND NEW.status IN ('selesai', 'batal'))) THEN
        RAISE EXCEPTION 'Transisi status antrean % -> % tidak valid', OLD.status, NEW.status;
    END IF;

    IF NEW.status = 'diperiksa' THEN
        NEW.waktu_mulai := NOW();
    ELSE
        NEW.waktu_selesai := COALESCE(NEW.waktu_selesai, NOW());
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_transisi_antrean
    BEFORE UPDATE OF status ON antrean
    FOR EACH ROW EXECUTE FUNCTION fn_transisi_antrean();
