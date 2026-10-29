-- =====================================================================
-- FITUR   : Validasi NIM/NIP civitas akademika (sistem tertutup)
-- URUTAN  : features/27
-- ISI     : fn_validasi_civitas_pasien, trigger trg_validasi_civitas, fk_pasien_civitas, fn_cek_civitas, fn_registrasi_pasien
-- DEPEND  : tables/01_users, tables/03_pasien, tables/18_data_civitas
-- =====================================================================

-- Pasien hanya boleh berasal dari data_civitas. Constraint ini ditambahkan di sini
-- (bukan di tabel pasien) karena data_civitas dibuat setelah pasien.
-- ON UPDATE CASCADE: jika NIM/NIP di data_civitas dikoreksi, pasien ikut berubah.
-- Gagal jika sudah ada pasien yang NIM/NIP-nya belum ada di data_civitas.
ALTER TABLE pasien
    ADD CONSTRAINT fk_pasien_civitas
    FOREIGN KEY (nim_nip) REFERENCES data_civitas (nim_nip) ON UPDATE CASCADE;

-- A. Trigger pada pasien: tolak NIM/NIP yang tidak terdaftar atau tidak aktif.
--    Identitas (nama, jenis kelamin, tanggal lahir, status civitas) selalu diambil
--    dari data_civitas, sehingga PHP cukup mengirim nim_nip (kolom lain boleh dikosongkan).
CREATE OR REPLACE FUNCTION fn_validasi_civitas_pasien() RETURNS TRIGGER AS $$
DECLARE
    c data_civitas%ROWTYPE;
BEGIN
    SELECT * INTO c FROM data_civitas WHERE nim_nip = NEW.nim_nip;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'NIM/NIP % tidak terdaftar sebagai civitas akademika', NEW.nim_nip;
    END IF;

    IF (TG_OP = 'INSERT' OR NEW.nim_nip IS DISTINCT FROM OLD.nim_nip)
       AND c.status_keanggotaan <> 'aktif' THEN
        RAISE EXCEPTION 'NIM/NIP % tidak berstatus aktif (status: %)', NEW.nim_nip, c.status_keanggotaan;
    END IF;

    NEW.nama           := c.nama;
    NEW.jenis_kelamin  := c.jenis_kelamin;
    NEW.tanggal_lahir  := c.tanggal_lahir;
    NEW.status_civitas := c.status_civitas;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validasi_civitas
    BEFORE INSERT OR UPDATE OF nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas ON pasien
    FOR EACH ROW EXECUTE FUNCTION fn_validasi_civitas_pasien();

-- B. Langkah 1 form registrasi: cek NIM/NIP + tanggal lahir.
--    Mengembalikan 0 baris jika tidak cocok / tidak aktif (tampilkan pesan generik di UI,
--    jangan beri tahu mana yang salah agar NIM tidak bisa ditebak).
CREATE OR REPLACE FUNCTION fn_cek_civitas(p_nim_nip VARCHAR, p_tanggal_lahir DATE)
RETURNS TABLE (nama VARCHAR, status_civitas VARCHAR, unit VARCHAR, sudah_terdaftar BOOLEAN) AS $$
    SELECT c.nama, c.status_civitas, c.unit,
           EXISTS (SELECT 1 FROM pasien p WHERE p.nim_nip = c.nim_nip AND p.user_id IS NOT NULL)
      FROM data_civitas c
     WHERE c.nim_nip = p_nim_nip
       AND c.tanggal_lahir = p_tanggal_lahir
       AND c.status_keanggotaan = 'aktif';
$$ LANGUAGE sql STABLE;

-- C. Langkah 2 form registrasi: buat akun + data pasien dalam satu transaksi.
--    Tanggal lahir berfungsi sebagai faktor kedua agar orang lain tidak bisa
--    mendaftar memakai NIM/NIP yang bukan miliknya.
--    Jika pasien walk-in sudah dibuat admin (belum punya akun), akunnya ditautkan.
--    password_hash dibuat di PHP (password_hash()), BUKAN password asli.
--    Mengembalikan pasien.id.
CREATE OR REPLACE FUNCTION fn_registrasi_pasien(
    p_nim_nip VARCHAR, p_tanggal_lahir DATE, p_username VARCHAR, p_password_hash VARCHAR
) RETURNS BIGINT AS $$
DECLARE
    c        data_civitas%ROWTYPE;
    v_user   BIGINT;
    v_pasien BIGINT;
BEGIN
    SELECT * INTO c
      FROM data_civitas
     WHERE nim_nip = p_nim_nip
       AND tanggal_lahir = p_tanggal_lahir
       AND status_keanggotaan = 'aktif';

    IF NOT FOUND THEN
        RAISE EXCEPTION 'NIM/NIP atau tanggal lahir tidak cocok dengan data civitas akademika';
    END IF;

    SELECT id, user_id INTO v_pasien, v_user
      FROM pasien WHERE nim_nip = c.nim_nip FOR UPDATE;

    IF FOUND AND v_user IS NOT NULL THEN
        RAISE EXCEPTION 'NIM/NIP ini sudah memiliki akun. Silakan login atau hubungi admin';
    END IF;

    INSERT INTO users (username, password_hash, role)
    VALUES (p_username, p_password_hash, 'pasien')
    RETURNING id INTO v_user;

    IF v_pasien IS NULL THEN
        INSERT INTO pasien (user_id, nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas)
        VALUES (v_user, c.nim_nip, c.nama, c.jenis_kelamin, c.tanggal_lahir, c.status_civitas)
        RETURNING id INTO v_pasien;
    ELSE
        UPDATE pasien SET user_id = v_user WHERE id = v_pasien;
    END IF;

    RETURN v_pasien;
END;
$$ LANGUAGE plpgsql;
