-- =====================================================================
-- FITUR   : Helper notifikasi ke semua admin aktif
-- URUTAN  : features/03
-- ISI     : fn_notif_admin
-- DEPEND  : tables/15_inbox, 01_users
-- =====================================================================

CREATE OR REPLACE FUNCTION fn_notif_admin(
    p_tipe VARCHAR, p_judul VARCHAR, p_pesan TEXT, p_ref_tipe VARCHAR, p_ref_id BIGINT
) RETURNS VOID AS $$
    INSERT INTO inbox (user_id, tipe, judul, pesan, ref_tipe, ref_id)
    SELECT id, p_tipe, p_judul, p_pesan, p_ref_tipe, p_ref_id
      FROM users
     WHERE role = 'admin' AND is_active;
$$ LANGUAGE sql;
