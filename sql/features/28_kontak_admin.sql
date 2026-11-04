-- =====================================================================
-- FITUR   : Kontak admin yang sedang bertugas (menu "Hubungi Admin")
-- URUTAN  : features/28
-- ISI     : fn_kontak_admin
-- DEPEND  : 00_common.sql, tables/01_users, tables/04_admin, tables/20_jadwal_admin
-- =====================================================================

-- Mengembalikan daftar admin yang sedang bertugas pada jam saat ini (WIB).
-- Jika tidak ada jadwal_admin yang cocok dengan jam sekarang, fallback ke
-- jadwal admin hari ini yang terdekat/masih buka.
CREATE OR REPLACE FUNCTION fn_kontak_admin()
RETURNS TABLE (
    admin_id    BIGINT,
    nama        VARCHAR(100),
    no_hp       VARCHAR(20),
    jam_mulai   TIME,
    jam_selesai TIME,
    sedang_jaga BOOLEAN
) AS $$
    SELECT a.id AS admin_id,
           a.nama,
           a.no_hp,
           ja.jam_mulai,
           ja.jam_selesai,
           ((NOW() AT TIME ZONE 'Asia/Jakarta')::time BETWEEN ja.jam_mulai AND ja.jam_selesai) AS sedang_jaga
      FROM jadwal_admin ja
      JOIN admin a ON a.id = ja.admin_id
      JOIN users u ON u.id = a.user_id
     WHERE ja.tanggal = fn_today()
       AND u.is_active = TRUE
       AND a.no_hp IS NOT NULL
     ORDER BY sedang_jaga DESC, ja.jam_mulai ASC;
$$ LANGUAGE sql STABLE;