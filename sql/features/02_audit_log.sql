-- =====================================================================
-- FITUR   : Audit log perubahan data penting (tanpa isi klinis sensitif)
-- URUTAN  : features/02
-- ISI     : fn_audit, trigger audit pada pemeriksaan, resep_obat, obat, users, pasien, admin
-- DEPEND  : tables/17_audit_log, 01_users, 03_pasien, 04_admin, 10_obat, 11_pemeriksaan, 12_resep_obat
-- =====================================================================

-- Mencatat siapa (user_id) mengubah baris mana, kapan, dan kolom apa yang berubah.
-- Kolom yang tidak boleh tersimpan diberikan sebagai argumen trigger dan dibuang dari
-- data_lama/data_baru (password_hash selalu dibuang). Nama kolom sensitif yang berubah
-- tetap muncul di kolom_berubah, jadi terlihat "diagnosa diubah" tanpa membuka isinya.
--
-- Pelaku diambil dari set_config('app.user_id', ...). Panggil lewat db_transaksi() di PHP
-- (config/database.php), kalau tidak user_id akan kosong.
CREATE OR REPLACE FUNCTION fn_audit() RETURNS TRIGGER AS $$
DECLARE
    v_user  BIGINT := NULLIF(current_setting('app.user_id', true), '')::BIGINT;
    v_id    BIGINT;
    v_lama  JSONB;
    v_baru  JSONB;
    v_kolom TEXT[];
BEGIN
    IF TG_OP = 'DELETE' THEN
        v_id := (to_jsonb(OLD) ->> 'id')::BIGINT;
    ELSE
        v_id := (to_jsonb(NEW) ->> 'id')::BIGINT;
    END IF;

    IF TG_OP IN ('UPDATE', 'DELETE') THEN
        v_lama := to_jsonb(OLD) - 'password_hash' - TG_ARGV;
    END IF;
    IF TG_OP IN ('INSERT', 'UPDATE') THEN
        v_baru := to_jsonb(NEW) - 'password_hash' - TG_ARGV;
    END IF;

    IF TG_OP = 'UPDATE' THEN
        SELECT COALESCE(array_agg(n.key ORDER BY n.key), '{}')
          INTO v_kolom
          FROM jsonb_each(to_jsonb(NEW)) n
         WHERE n.key NOT IN ('updated_at', 'last_login_at')
           AND n.value IS DISTINCT FROM (to_jsonb(OLD) -> n.key);

        IF cardinality(v_kolom) = 0 THEN
            RETURN NULL;       -- tidak ada perubahan berarti
        END IF;
    END IF;

    INSERT INTO audit_log (tabel, record_id, aksi, user_id, kolom_berubah, data_lama, data_baru)
    VALUES (TG_TABLE_NAME, v_id, TG_OP, v_user, v_kolom, v_lama, v_baru);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Tanda kesehatan dan isi pemeriksaan tidak disimpan di audit.
CREATE TRIGGER trg_audit_pemeriksaan AFTER INSERT OR UPDATE OR DELETE ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_audit(
        'keluhan', 'diagnosa', 'tindakan', 'catatan', 'tindak_lanjut',
        'tekanan_darah', 'suhu', 'nadi', 'berat_badan', 'tinggi_badan');

CREATE TRIGGER trg_audit_resep_obat  AFTER INSERT OR UPDATE OR DELETE ON resep_obat
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_obat        AFTER INSERT OR UPDATE OR DELETE ON obat
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_users       AFTER INSERT OR UPDATE OR DELETE ON users
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_pasien      AFTER INSERT OR UPDATE OR DELETE ON pasien
    FOR EACH ROW EXECUTE FUNCTION fn_audit('riwayat_alergi', 'golongan_darah');

CREATE TRIGGER trg_audit_admin       AFTER INSERT OR UPDATE OR DELETE ON admin
    FOR EACH ROW EXECUTE FUNCTION fn_audit();