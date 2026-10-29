# Database SIPOLI

## Struktur Folder
```
sql
├── 00_common.sql
├── README.md
├── features
│   ├── 01_autentikasi.sql
│   ├── 02_audit_log.sql
│   ├── 03_notifikasi_admin.sql
│   ├── 04_jadwal_validasi.sql
│   ├── 05_jadwal_ditutup.sql
│   ├── 06_jadwal_penugasan_dokter.sql
│   ├── 07_jadwal_generate.sql
│   ├── 08_reservasi_validasi.sql
│   ├── 09_reservasi_nomor_otomatis.sql
│   ├── 10_reservasi_sinkron_jadwal.sql
│   ├── 11_reservasi_konfirmasi.sql
│   ├── 12_reservasi_reschedule.sql
│   ├── 13_antrean_nomor_otomatis.sql
│   ├── 14_antrean_validasi.sql
│   ├── 15_antrean_sinkron_reservasi.sql
│   ├── 16_antrean_panggil.sql
│   ├── 17_pemeriksaan_validasi.sql
│   ├── 18_pemeriksaan_selesaikan_antrean.sql
│   ├── 19_stok_obat_core.sql
│   ├── 20_stok_resep.sql
│   ├── 21_stok_peringatan.sql
│   ├── 22_permintaan_obat.sql
│   ├── 23_job_reservasi.sql
│   ├── 24_job_antrean.sql
│   ├── 25_job_scheduler.sql
│   ├── 26_views_dashboard.sql
│   └── 27_validasi_civitas.sql
├── install.sql
├── seeds
│   ├── 01_poli.sql
│   └── 02_data_civitas.sql
└── tables
    ├── 01_users.sql
    ├── 02_poli.sql
    ├── 03_pasien.sql
    ├── 04_admin.sql
    ├── 05_dokter.sql
    ├── 06_jadwal.sql
    ├── 07_jadwal_template.sql
    ├── 08_reservasi.sql
    ├── 09_antrean.sql
    ├── 10_obat.sql
    ├── 11_pemeriksaan.sql
    ├── 12_resep_obat.sql
    ├── 13_log_stok_obat.sql
    ├── 14_permintaan_obat.sql
    ├── 15_inbox.sql
    ├── 16_php_sessions.sql
    ├── 17_audit_log.sql
    └── 18_data_civitas.sql
```
## Install
Jalankan install.sql atau paste satu persatu sesuai urutan dibawah

## Urutan eksekusi

| # | File | Isi |
|---|---|---|
| 0 | `00_common.sql` | Extension btree_gist, fn_set_updated_at, fn_today |
| 1 | `tables/01_users.sql` | users: akun login semua peran |
| 2 | `tables/02_poli.sql` | poli: Umum, Gigi, Konseling |
| 3 | `tables/03_pasien.sql` | pasien: data pasien civitas akademika |
| 4 | `tables/04_admin.sql` | admin: profil admin |
| 5 | `tables/05_dokter.sql` | dokter: profil dokter & psikolog |
| 6 | `tables/06_jadwal.sql` | jadwal: jadwal praktik per tanggal |
| 7 | `tables/07_jadwal_template.sql` | jadwal_template: template jadwal mingguan |
| 8 | `tables/08_reservasi.sql` | reservasi: reservasi Gigi & Konseling |
| 9 | `tables/09_antrean.sql` | antrean: antrean semua poli |
| 10 | `tables/10_obat.sql` | obat: inventaris obat |
| 11 | `tables/11_pemeriksaan.sql` | pemeriksaan: rekam medis |
| 12 | `tables/12_resep_obat.sql` | resep_obat: detail obat per pemeriksaan |
| 13 | `tables/13_log_stok_obat.sql` | log_stok_obat: riwayat stok masuk/keluar |
| 14 | `tables/14_permintaan_obat.sql` | permintaan_obat: permintaan penyiapan obat ke apotek |
| 15 | `tables/15_inbox.sql` | inbox: notifikasi in-app |
| 16 | `tables/16_php_sessions.sql` | php_sessions: sesi PHP di database |
| 17 | `tables/17_audit_log.sql` | audit_log: jejak perubahan data penting |
| 18 | `tables/18_data_civitas.sql` | data_civitas: data mockup dari civitas akademika polinema |
| 19 | `features/01_autentikasi.sql` | Autentikasi: lockout login & konsistensi role akun |
| 20 | `features/02_audit_log.sql` | Audit log perubahan data penting |
| 21 | `features/03_notifikasi_admin.sql` | Helper notifikasi ke semua admin aktif |
| 22 | `features/04_jadwal_validasi.sql` | Validasi jadwal, hitung status buka/penuh, sinkron saat kuota berubah |
| 23 | `features/05_jadwal_ditutup.sql` | Jadwal ditutup: batalkan reservasi aktif + notifikasi pasien |
| 24 | `features/06_jadwal_penugasan_dokter.sql` | Notifikasi ke dokter saat ditugaskan ke jadwal |
| 25 | `features/07_jadwal_generate.sql` | Generate jadwal dari template mingguan |
| 26 | `features/08_reservasi_validasi.sql` | Validasi kuota, publikasi, jendela H-7..H-1, dan transisi status reservasi |
| 27 | `features/09_reservasi_nomor_otomatis.sql` | Nomor reservasi otomatis + auto-konfirmasi bila dibuat di H-1 |
| 28 | `features/10_reservasi_sinkron_jadwal.sql` | Sinkron status jadwal (buka/penuh) saat reservasi berubah |
| 29 | `features/11_reservasi_konfirmasi.sql` | Konfirmasi kehadiran pasien |
| 30 | `features/12_reservasi_reschedule.sql` | Reschedule reservasi (maksimal 1x) |
| 31 | `features/13_antrean_nomor_otomatis.sql` | Nomor antrean otomatis per poli per hari |
| 32 | `features/14_antrean_validasi.sql` | Validasi pembuatan antrean + guard transisi status |
| 33 | `features/15_antrean_sinkron_reservasi.sql` | Status antrean -> status reservasi (hadir / tidak_hadir) |
| 34 | `features/16_antrean_panggil.sql` | Panggil pasien berikutnya untuk satu dokter |
| 35 | `features/17_pemeriksaan_validasi.sql` | Validasi pemeriksaan (antrean, dokter, data turunan) |
| 36 | `features/18_pemeriksaan_selesaikan_antrean.sql` | Submit pemeriksaan -> antrean & reservasi selesai -> panggil berikutnya |
| 37 | `features/19_stok_obat_core.sql` | Jalur tunggal perubahan stok + tambah stok oleh admin |
| 38 | `features/20_stok_resep.sql` | Stok berkurang/dikoreksi otomatis dari resep |
| 39 | `features/21_stok_peringatan.sql` | Peringatan stok minimum & obat hampir kadaluarsa |
| 40 | `features/22_permintaan_obat.sql` | Resep -> permintaan obat + notifikasi admin apotek |
| 41 | `features/23_job_reservasi.sql` | Job: kirim konfirmasi H-1 & batalkan yang tidak konfirmasi |
| 42 | `features/24_job_antrean.sql` | Job: generate antrean harian & tutup hari |
| 43 | `features/25_job_scheduler.sql` | Pintu masuk scheduler (fn_job_pagi, fn_job_malam) |
| 44 | `features/26_views_dashboard.sql` | View untuk dashboard & layar antrean |
| 45 | `features/27_validasi_civitas.sql` | Validasi pasien apakah termasuk civitas akademika/tidak |
| 46 | `seeds/01_poli.sql` | Seed 3 poli (kuota contoh) |

Aturan: semua `tables/` dijalankan sebelum `features/` (function SQL dan trigger butuh tabelnya), dan `seeds/` terakhir. Urutan di dalam `features/` sudah disusun menurut dependensi; fitur yang memanggil fitur lain mencantumkannya di baris `DEPEND`.
