-- =====================================================================
-- SEED   : data civitas akademika DUMMY
-- URUTAN : seeds/02
-- ISI    : 40 mahasiswa, 12 dosen, 10 staf (semua data fiktif)
-- DEPEND : tables/18_data_civitas.sql
-- =====================================================================
-- PERINGATAN: data ini hanya untuk pengembangan dan pengujian.
-- Nama, NIM, dan NIP dibuat acak; kemiripan dengan data asli adalah kebetulan.
-- JANGAN dipakai di production. Ganti dengan data resmi kampus.
--
-- Kasus uji yang sudah disiapkan:
--   * mahasiswa status 'lulus' (2 baris), 'cuti' (1), 'keluar' (1)  -> harus DITOLAK
--   * dosen status 'pensiun' (1)                                      -> harus DITOLAK
--   * staf status 'nonaktif' (1)                                      -> harus DITOLAK
--   * NIM/NIP di luar tabel ini                                       -> harus DITOLAK
-- Format NIM  : angkatan(4) + kode prodi(2) + urut(4)             = 10 digit
-- Format NIP  : tgl lahir(8) + TMT(6) + gender(1: L, 2: P) + urut(3) = 18 digit
-- =====================================================================

-- Mahasiswa
INSERT INTO data_civitas
    (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, unit, angkatan, status_keanggotaan)
VALUES
    ('254107010001', 'Nadia Hidayat', 'P', '2001-05-03', 'mahasiswa', 'Sistem Informasi', 2020, 'lulus'),
    ('254107010002', 'Intan Kurniawan', 'P', '2001-04-04', 'mahasiswa', 'Teknik Elektro', 2021, 'aktif'),
    ('254107010003', 'Iqbal Santoso', 'L', '2002-03-04', 'mahasiswa', 'Teknik Elektro', 2021, 'aktif'),
    ('254107010004', 'Eko Saputra', 'L', '2001-09-22', 'mahasiswa', 'Teknik Informatika', 2021, 'aktif'),
    ('254107010005', 'Fitri Santoso', 'P', '2001-06-09', 'mahasiswa', 'Teknik Informatika', 2021, 'lulus'),
    ('254107010006', 'Citra Saputra', 'P', '2002-11-12', 'mahasiswa', 'Sistem Informasi', 2021, 'aktif'),
    ('254107010007', 'Tania Kusuma', 'P', '2001-12-23', 'mahasiswa', 'Akuntansi', 2021, 'aktif'),
    ('254107010008', 'Dian Permata', 'P', '2001-12-03', 'mahasiswa', 'Psikologi', 2021, 'aktif'),
    ('254107010009', 'Galih Permata', 'L', '2002-10-05', 'mahasiswa', 'Ilmu Komunikasi', 2021, 'aktif'),
    ('254107010010', 'Dewi Susanto', 'P', '2002-10-20', 'mahasiswa', 'Ilmu Komunikasi', 2021, 'aktif'),
    ('254107010011', 'Eka Anggraini', 'P', '2003-07-01', 'mahasiswa', 'Pendidikan Dokter Gigi', 2021, 'aktif'),
    ('254107020001', 'Farhan Kusuma', 'L', '2004-11-28', 'mahasiswa', 'Teknik Elektro', 2022, 'aktif'),
    ('254107020002', 'Ayu Saputra', 'P', '2004-04-01', 'mahasiswa', 'Teknik Informatika', 2022, 'cuti'),
    ('254107020003', 'Wulan Kusuma', 'P', '2003-04-28', 'mahasiswa', 'Sistem Informasi', 2022, 'aktif'),
    ('254107020004', 'Eka Pratama', 'P', '2003-11-19', 'mahasiswa', 'Akuntansi', 2022, 'aktif'),
    ('254107020005', 'Farhan Susanto', 'L', '2004-11-10', 'mahasiswa', 'Teknik Informatika', 2023, 'aktif'),
    ('254107020006', 'Gita Susanto', 'P', '2003-11-01', 'mahasiswa', 'Teknik Informatika', 2023, 'aktif'),
    ('254107020007', 'Maya Hidayat', 'P', '2004-03-09', 'mahasiswa', 'Sistem Informasi', 2023, 'aktif'),
    ('254107020008', 'Rizki Maharani', 'L', '2004-07-03', 'mahasiswa', 'Akuntansi', 2023, 'aktif'),
    ('254107020009', 'Reza Wibowo', 'L', '2003-05-22', 'mahasiswa', 'Akuntansi', 2023, 'aktif'),
    ('254107020010', 'Dimas Purnama', 'L', '2003-06-11', 'mahasiswa', 'Akuntansi', 2023, 'keluar'),
    ('254107020011', 'Sari Wibowo', 'P', '2004-03-11', 'mahasiswa', 'Ilmu Komunikasi', 2023, 'aktif'),
    ('254107020012', 'Dewi Anggraini', 'P', '2003-05-27', 'mahasiswa', 'Ilmu Komunikasi', 2023, 'aktif'),
    ('254107020013', 'Reza Anggraini', 'L', '2006-07-20', 'mahasiswa', 'Teknik Elektro', 2024, 'aktif'),
    ('254107020014', 'Lestari Utami', 'P', '2004-07-28', 'mahasiswa', 'Teknik Elektro', 2024, 'aktif'),
    ('254107020015', 'Hendra Santoso', 'L', '2004-01-25', 'mahasiswa', 'Sistem Informasi', 2024, 'aktif'),
    ('254107020016', 'Hendra Pratama', 'L', '2006-08-21', 'mahasiswa', 'Sistem Informasi', 2024, 'aktif'),
    ('254107020017', 'Gita Rahmawati', 'P', '2004-08-15', 'mahasiswa', 'Ilmu Komunikasi', 2024, 'aktif'),
    ('254107020018', 'Iqbal Susanto', 'L', '2005-02-07', 'mahasiswa', 'Ilmu Komunikasi', 2024, 'aktif'),
    ('254107020019', 'Gilang Hidayat', 'L', '2006-11-18', 'mahasiswa', 'Pendidikan Dokter Gigi', 2024, 'aktif'),
    ('254107020020', 'Wulan Purnama', 'P', '2006-09-13', 'mahasiswa', 'Sistem Informasi', 2025, 'aktif'),
    ('254107020021', 'Anisa Rahmawati', 'P', '2006-03-14', 'mahasiswa', 'Sistem Informasi', 2025, 'aktif'),
    ('254107020022', 'Rani Utami', 'P', '2007-05-27', 'mahasiswa', 'Akuntansi', 2025, 'aktif'),
    ('254107020023', 'Budi Kusuma', 'L', '2005-10-15', 'mahasiswa', 'Akuntansi', 2025, 'aktif'),
    ('254107020024', 'Agus Hidayat', 'L', '2007-10-10', 'mahasiswa', 'Akuntansi', 2025, 'aktif'),
    ('254107020025', 'Intan Hidayat', 'P', '2007-06-28', 'mahasiswa', 'Psikologi', 2025, 'aktif'),
    ('254107020026', 'Intan Saputra', 'P', '2006-06-15', 'mahasiswa', 'Ilmu Komunikasi', 2025, 'aktif'),
    ('254107020027', 'Dewi Utami', 'P', '2005-10-16', 'mahasiswa', 'Pendidikan Dokter Gigi', 2025, 'aktif'),
    ('254107020028', 'Sari Kurniawan', 'P', '2006-09-17', 'mahasiswa', 'Pendidikan Dokter Gigi', 2025, 'aktif'),
    ('254107020029', 'Bagus Pratama', 'L', '2007-01-15', 'mahasiswa', 'Pendidikan Dokter Gigi', 2025, 'aktif')
ON CONFLICT (nim_nip) DO NOTHING;

-- Dosen
INSERT INTO data_civitas
    (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, unit, angkatan, status_keanggotaan)
VALUES
    ('198411212006072001', 'Dr. Nadia Lestari', 'P', '1984-11-21', 'dosen', 'Manajemen', NULL, 'aktif'),
    ('197811042016092002', 'Lestari Firmansyah', 'P', '1978-11-04', 'dosen', 'Akuntansi', NULL, 'aktif'),
    ('196612232018012003', 'Maya Setiawan', 'P', '1966-12-23', 'dosen', 'Akuntansi', NULL, 'aktif'),
    ('197407112005091004', 'Kevin Nugroho', 'L', '1974-07-11', 'dosen', 'Teknik Informatika', NULL, 'aktif'),
    ('197010042008011005', 'Dr. Eko Anggraini', 'L', '1970-10-04', 'dosen', 'Psikologi', NULL, 'aktif'),
    ('196507192009062006', 'Sari Firmansyah', 'P', '1965-07-19', 'dosen', 'Pendidikan Dokter Gigi', NULL, 'aktif'),
    ('198907222012031007', 'Fajar Purnama', 'L', '1989-07-22', 'dosen', 'Teknik Informatika', NULL, 'aktif'),
    ('197303082005012008', 'Lestari Purnama', 'P', '1973-03-08', 'dosen', 'Ilmu Komunikasi', NULL, 'aktif'),
    ('197306052007032009', 'Dr. Nadia Anggraini', 'P', '1973-06-05', 'dosen', 'Psikologi', NULL, 'aktif'),
    ('198601212012081010', 'Budi Permata', 'L', '1986-01-21', 'dosen', 'Teknik Elektro', NULL, 'aktif'),
    ('198706262012092011', 'Lestari Setiawan', 'P', '1987-06-26', 'dosen', 'Manajemen', NULL, 'aktif'),
    ('197608212019092012', 'Wulan Kurniawan', 'P', '1976-08-21', 'dosen', 'Akuntansi', NULL, 'pensiun')
ON CONFLICT (nim_nip) DO NOTHING;

-- Staf / karyawan (10 baris)
INSERT INTO data_civitas
    (nim_nip, nama, jenis_kelamin, tanggal_lahir, status_civitas, unit, angkatan, status_keanggotaan)
VALUES
    ('197511142012081001', 'Ahmad Maharani', 'L', '1975-11-14', 'staf', 'Bagian Akademik', NULL, 'aktif'),
    ('199501212013022002', 'Maya Maharani', 'P', '1995-01-21', 'staf', 'Bagian Keuangan', NULL, 'aktif'),
    ('197412242011012003', 'Intan Nugroho', 'P', '1974-12-24', 'staf', 'Perpustakaan', NULL, 'aktif'),
    ('198801132020092004', 'Anisa Hidayat', 'P', '1988-01-13', 'staf', 'Pusat Teknologi Informasi', NULL, 'aktif'),
    ('197203242020061005', 'Galih Rahmawati', 'L', '1972-03-24', 'staf', 'Kemahasiswaan', NULL, 'aktif'),
    ('197112192014032006', 'Maya Wibowo', 'P', '1971-12-19', 'staf', 'Bagian Umum', NULL, 'aktif'),
    ('198103132014122007', 'Ayu Lestari', 'P', '1981-03-13', 'staf', 'Bagian Akademik', NULL, 'aktif'),
    ('198306262013122008', 'Anisa Pratama', 'P', '1983-06-26', 'staf', 'Bagian Keuangan', NULL, 'aktif'),
    ('198009052013011009', 'Dimas Anggraini', 'L', '1980-09-05', 'staf', 'Perpustakaan', NULL, 'aktif'),
    ('199606032019051010', 'Gilang Purnama', 'L', '1996-06-03', 'staf', 'Pusat Teknologi Informasi', NULL, 'nonaktif')
ON CONFLICT (nim_nip) DO NOTHING;
