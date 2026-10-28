-- =====================================================================
-- FITUR   : Jalur tunggal perubahan stok + tambah stok oleh admin
-- URUTAN  : features/19
-- ISI     : fn_ubah_stok, fn_tambah_stok_obat
-- DEPEND  : tables/10_obat, 13_log_stok_obat
-- =====================================================================

-- A. Satu-satunya jalur perubahan stok. Mengunci baris obat, validasi, update, dan log.
CREATE OR REPLACE FUNCTION fn_ubah_stok(
    p_obat_id BIGINT, p_delta INT, p_resep_obat_id BIGINT, p_admin_id BIGINT, p_ket TEXT
) RETURNS INT AS $$
DECLARE
    v_sebelum INT;
    v_exp     DATE;
    v_aktif   BOOLEAN;
    v_nama    VARCHAR(150);
    v_sesudah INT;
BEGIN
    SELECT stok, tanggal_kadaluarsa, is_active, nama_obat
      INTO v_sebelum, v_exp, v_aktif, v_nama
      FROM obat WHERE id = p_obat_id FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Obat id=% tidak ditemukan', p_obat_id;
    END IF;
    IF p_delta = 0 THEN
        RETURN v_sebelum;
    END IF;

    IF p_delta < 0 THEN                          -- pengeluaran: cek kelayakan obat
        IF NOT v_aktif THEN
            RAISE EXCEPTION 'Obat "%" sudah tidak aktif', v_nama;
        END IF;
        IF v_exp IS NOT NULL AND v_exp < fn_today() THEN
            RAISE EXCEPTION 'Obat "%" sudah kadaluarsa (%)', v_nama, v_exp;
        END IF;
        IF v_sebelum + p_delta < 0 THEN
            RAISE EXCEPTION 'Stok obat "%" tidak mencukupi (tersedia %, diminta %)',
                v_nama, v_sebelum, -p_delta;
        END IF;
    END IF;

    v_sesudah := v_sebelum + p_delta;
    UPDATE obat SET stok = v_sesudah WHERE id = p_obat_id;

    INSERT INTO log_stok_obat (obat_id, jenis, jumlah, stok_sebelum, stok_sesudah,
                               resep_obat_id, admin_id, keterangan)
    VALUES (p_obat_id, CASE WHEN p_delta > 0 THEN 'masuk' ELSE 'keluar' END, ABS(p_delta),
            v_sebelum, v_sesudah, p_resep_obat_id, p_admin_id, p_ket);

    RETURN v_sesudah;
END;
$$ LANGUAGE plpgsql;

-- B. Admin menambah stok (form "tambah stok"). Jangan UPDATE obat.stok langsung dari PHP.
CREATE OR REPLACE FUNCTION fn_tambah_stok_obat(
    p_obat_id BIGINT, p_jumlah INT, p_admin_id BIGINT, p_ket TEXT DEFAULT NULL
) RETURNS INT AS $$
BEGIN
    IF p_jumlah IS NULL OR p_jumlah <= 0 THEN
        RAISE EXCEPTION 'Jumlah penambahan stok harus lebih dari 0';
    END IF;
    RETURN fn_ubah_stok(p_obat_id, p_jumlah, NULL, p_admin_id,
                        COALESCE(p_ket, 'Penambahan stok oleh admin'));
END;
$$ LANGUAGE plpgsql;
