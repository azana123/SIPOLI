-- =====================================================================
-- FITUR   : Nomor reservasi otomatis + auto-konfirmasi bila dibuat di H-1
-- URUTAN  : features/09
-- ISI     : fn_siapkan_reservasi, trigger trg_siapkan_reservasi
-- DEPEND  : tables/06_jadwal, 08_reservasi
-- =====================================================================

-- B. Nomor reservasi otomatis + auto-konfirmasi bila dibuat tepat di H-1
--    (pasien yang baru booking di H-1 tidak perlu diminta konfirmasi lagi)
CREATE OR REPLACE FUNCTION fn_siapkan_reservasi() RETURNS TRIGGER AS $$
DECLARE
    v_tanggal DATE;
BEGIN
    IF NEW.no_reservasi IS NULL OR NEW.no_reservasi = '' THEN
        NEW.no_reservasi := 'RSV-' || to_char(fn_today(), 'YYMMDD') || '-'
                            || lpad(nextval('seq_no_reservasi')::text, 5, '0');
    END IF;

    SELECT tanggal INTO v_tanggal FROM jadwal WHERE id = NEW.jadwal_id;
    IF v_tanggal = fn_today() + 1 THEN
        NEW.status := 'dikonfirmasi';
        NEW.dikonfirmasi_at := NOW();
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_siapkan_reservasi
    BEFORE INSERT ON reservasi
    FOR EACH ROW EXECUTE FUNCTION fn_siapkan_reservasi();
