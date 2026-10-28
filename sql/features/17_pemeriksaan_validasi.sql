-- =====================================================================
-- FITUR   : Validasi pemeriksaan (antrean, dokter, data turunan)
-- URUTAN  : features/17
-- ISI     : fn_validasi_pemeriksaan, trigger trg_validasi_pemeriksaan
-- DEPEND  : tables/05_dokter, 09_antrean, 11_pemeriksaan
-- =====================================================================

-- A. Validasi: antrean harus sedang diperiksa, dokter sesuai poli,
--    pasien/poli/tanggal diturunkan dari antrean (tidak bisa dipalsukan dari aplikasi)
CREATE OR REPLACE FUNCTION fn_validasi_pemeriksaan() RETURNS TRIGGER AS $$
DECLARE
    a             antrean%ROWTYPE;
    v_poli_dokter SMALLINT;
BEGIN
    SELECT * INTO a FROM antrean WHERE id = NEW.antrean_id FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Antrean tidak ditemukan';
    END IF;
    IF a.status <> 'diperiksa' THEN
        RAISE EXCEPTION 'Antrean belum dipanggil atau sudah selesai (status: %)', a.status;
    END IF;

    SELECT poli_id INTO v_poli_dokter FROM dokter WHERE id = NEW.dokter_id;
    IF v_poli_dokter IS DISTINCT FROM a.poli_id THEN
        RAISE EXCEPTION 'Dokter tidak bertugas pada poli antrean ini';
    END IF;
    IF a.dokter_id IS NOT NULL AND a.dokter_id <> NEW.dokter_id THEN
        RAISE EXCEPTION 'Antrean ini sedang ditangani dokter lain';
    END IF;

    NEW.pasien_id := a.pasien_id;
    NEW.poli_id   := a.poli_id;
    NEW.tanggal   := a.tanggal;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validasi_pemeriksaan
    BEFORE INSERT ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_validasi_pemeriksaan();
