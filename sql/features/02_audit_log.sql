-- =====================================================================
-- FITUR   : Audit log perubahan data penting
-- URUTAN  : features/02
-- ISI     : fn_audit, trigger trg_audit_pemeriksaan, trigger trg_audit_resep_obat, trigger trg_audit_obat, trigger trg_audit_users, trigger trg_audit_pasien
-- DEPEND  : tables/17_audit_log, 01_users, 03_pasien, 10_obat, 11_pemeriksaan, 12_resep_obat
-- =====================================================================

CREATE OR REPLACE FUNCTION fn_audit() RETURNS TRIGGER AS $$
DECLARE
    v_user BIGINT := NULLIF(current_setting('app.user_id', true), '')::BIGINT;
    v_id   BIGINT;
BEGIN
    IF TG_OP = 'DELETE' THEN
        v_id := (to_jsonb(OLD) ->> 'id')::BIGINT;
    ELSE
        v_id := (to_jsonb(NEW) ->> 'id')::BIGINT;
    END IF;

    INSERT INTO audit_log (tabel, record_id, aksi, user_id, data_lama, data_baru)
    VALUES (TG_TABLE_NAME, v_id, TG_OP, v_user,
            CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN to_jsonb(OLD) - 'password_hash' END,
            CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN to_jsonb(NEW) - 'password_hash' END);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_audit_pemeriksaan AFTER INSERT OR UPDATE OR DELETE ON pemeriksaan
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_resep_obat  AFTER INSERT OR UPDATE OR DELETE ON resep_obat
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_obat        AFTER INSERT OR UPDATE OR DELETE ON obat
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_users       AFTER INSERT OR UPDATE OR DELETE ON users
    FOR EACH ROW EXECUTE FUNCTION fn_audit();

CREATE TRIGGER trg_audit_pasien      AFTER INSERT OR UPDATE OR DELETE ON pasien
    FOR EACH ROW EXECUTE FUNCTION fn_audit();
