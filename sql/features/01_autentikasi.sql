-- =====================================================================
-- FITUR   : Autentikasi: lockout login & konsistensi role akun
-- URUTAN  : features/01
-- ISI     : fn_login_gagal, fn_login_berhasil, fn_cek_role_user, trigger trg_pasien_role, trigger trg_dokter_role, trigger trg_admin_role
-- DEPEND  : tables/01_users, 03_pasien, 04_admin, 05_dokter
-- =====================================================================

-- Dipanggil PHP setelah password salah. 5x gagal -> kunci 15 menit.
CREATE OR REPLACE FUNCTION fn_login_gagal(p_user_id BIGINT) RETURNS VOID AS $$
    UPDATE users
       SET failed_login_count = failed_login_count + 1,
           locked_until = CASE WHEN failed_login_count + 1 >= 5
                               THEN NOW() + INTERVAL '15 minutes'
                               ELSE locked_until END
     WHERE id = p_user_id;
$$ LANGUAGE sql;

CREATE OR REPLACE FUNCTION fn_login_berhasil(p_user_id BIGINT) RETURNS VOID AS $$
    UPDATE users
       SET failed_login_count = 0, locked_until = NULL, last_login_at = NOW()
     WHERE id = p_user_id;
$$ LANGUAGE sql;

-- Pastikan admin.user_id / dokter.user_id / pasien.user_id menunjuk user dengan role yang benar
CREATE OR REPLACE FUNCTION fn_cek_role_user() RETURNS TRIGGER AS $$
DECLARE
    v_role VARCHAR(10);
BEGIN
    IF NEW.user_id IS NULL THEN
        RETURN NEW;
    END IF;
    SELECT role INTO v_role FROM users WHERE id = NEW.user_id;
    IF v_role IS DISTINCT FROM TG_ARGV[0] THEN
        RAISE EXCEPTION 'User id=% berperan %, seharusnya %', NEW.user_id, v_role, TG_ARGV[0];
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_pasien_role BEFORE INSERT OR UPDATE OF user_id ON pasien
    FOR EACH ROW EXECUTE FUNCTION fn_cek_role_user('pasien');

CREATE TRIGGER trg_dokter_role BEFORE INSERT OR UPDATE OF user_id ON dokter
    FOR EACH ROW EXECUTE FUNCTION fn_cek_role_user('dokter');

CREATE TRIGGER trg_admin_role  BEFORE INSERT OR UPDATE OF user_id ON admin
    FOR EACH ROW EXECUTE FUNCTION fn_cek_role_user('admin');
