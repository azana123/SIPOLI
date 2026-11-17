-- =====================================================================
-- FITUR   : View untuk dashboard & layar antrean
-- URUTAN  : features/26
-- ISI     : v_jadwal_tersedia, v_antrean_hari_ini, v_riwayat_pemeriksaan, v_obat_perlu_perhatian
-- DEPEND  : semua tabel utama; fn_today
-- =====================================================================

-- Jadwal yang bisa dipilih pasien di form reservasi
CREATE OR REPLACE VIEW v_jadwal_tersedia AS
SELECT j.id, j.poli_id, p.nama AS poli, j.dokter_id, d.nama AS dokter,
       j.tanggal, j.jam_mulai, j.jam_selesai, j.kuota,
       j.kuota - COUNT(r.id) AS sisa_kuota
  FROM jadwal j
  JOIN poli p ON p.id = j.poli_id
  LEFT JOIN dokter d ON d.id = j.dokter_id
  LEFT JOIN reservasi r ON r.jadwal_id = j.id AND r.status <> 'dibatalkan'
 WHERE p.butuh_reservasi AND j.dipublikasi AND j.status = 'buka'
   AND j.tanggal BETWEEN fn_today() + 1 AND fn_today() + 7
 GROUP BY j.id, p.nama, d.nama;

-- Layar antrean & dashboard admin/dokter
CREATE OR REPLACE VIEW v_antrean_hari_ini AS
SELECT a.id, a.poli_id, po.nama AS poli, a.no_antrean, a.status,
       a.pasien_id, ps.nim_nip, ps.nama AS pasien,
       a.dokter_id, d.nama AS dokter, j.jam_mulai, a.reservasi_id,
       a.waktu_daftar, a.waktu_mulai, a.waktu_selesai
  FROM antrean a
  JOIN pasien ps ON ps.id = a.pasien_id
  JOIN poli   po ON po.id = a.poli_id
  LEFT JOIN dokter d ON d.id = a.dokter_id
  LEFT JOIN jadwal j ON j.id = a.jadwal_id
 WHERE a.tanggal = fn_today();

-- Histori / rekam jejak pemeriksaan semua pasien
CREATE OR REPLACE VIEW v_riwayat_pemeriksaan AS
SELECT pm.id, pm.tanggal, ps.id AS pasien_id, ps.nim_nip, ps.nama AS pasien,
       po.nama AS poli, d.nama AS dokter,
       pm.keluhan, pm.diagnosa, pm.tindakan, pm.tindak_lanjut,
       (SELECT string_agg(o.nama_obat || ' x' || ro.jumlah
                          || COALESCE(' (' || ro.aturan_pakai || ')', ''), '; ')
          FROM resep_obat ro JOIN obat o ON o.id = ro.obat_id
         WHERE ro.pemeriksaan_id = pm.id) AS resep
  FROM pemeriksaan pm
  JOIN pasien ps ON ps.id = pm.pasien_id
  JOIN poli   po ON po.id = pm.poli_id
  JOIN dokter d  ON d.id  = pm.dokter_id;

-- Obat yang perlu perhatian admin
CREATE OR REPLACE VIEW v_obat_perlu_perhatian AS
SELECT o.id, o.kode_obat, o.nama_obat, o.stok, o.stok_minimum, o.tanggal_kadaluarsa,
       CASE
           WHEN o.tanggal_kadaluarsa < fn_today()      THEN 'kadaluarsa'
           WHEN o.stok = 0                             THEN 'habis'
           WHEN o.stok <= o.stok_minimum               THEN 'stok_minimum'
           ELSE 'hampir_kadaluarsa'
       END AS alasan
  FROM obat o
 WHERE o.is_active
   AND (o.stok <= o.stok_minimum
        OR o.tanggal_kadaluarsa <= fn_today() + 30);
