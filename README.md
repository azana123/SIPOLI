# SIPOLI

**Sistem Informasi Poliklinik** untuk civitas akademika Politeknik Negeri Malang. Aplikasi ini mengelola jadwal dokter, reservasi, antrean, pemeriksaan, resep obat, dan notifikasi untuk tiga peran: **admin**, **dokter**, dan **pasien**.

## Daftar isi

- [Tech stack](#tech-stack)
- [Arsitektur aplikasi](#arsitektur-aplikasi)
- [Struktur folder](#struktur-folder)
- [Prasyarat](#prasyarat)
- [Instalasi dari nol](#instalasi-dari-nol)
- [Konfigurasi lingkungan](#konfigurasi-lingkungan)
- [Alur aplikasi](#alur-aplikasi)
- [API](#api)
- [Integrasi frontend](#integrasi-frontend)
- [Penjadwal harian](#penjadwal-harian)
- [Deploy dengan Docker](#deploy-dengan-docker)
- [Aturan database](#aturan-database)
- [Troubleshooting](#troubleshooting)

## Tech stack

| Bagian | Dipakai |
|---|---|
| Bahasa | PHP 8.3 (`declare(strict_types=1)`) |
| Database | PostgreSQL (Neon di produksi) melalui PDO `pdo_pgsql` |
| Frontend | Tailwind CSS v4 + Flowbite, Font Awesome, JavaScript vanilla |
| Server lokal | `php -S localhost:8000` |
| Container | Docker multi-stage: `node:20-alpine` untuk build aset, `php:8.3-apache` untuk runtime |
| Hosting | SnapDeploy (free tier, auto-deploy dari GitHub) |
| Penjadwal | GitHub Actions cron yang memanggil script PHP langsung ke database |
| Keamanan | Session PHP, CSRF token, role guard, `password_hash`, lockout login |

Ekstensi PHP yang wajib ada: `pdo_pgsql` (koneksi ke PostgreSQL/Neon), `mbstring` (`mb_substr()` saat menyusun aturan resep), dan `calendar` (`cal_days_in_month()` di kalender reservasi). Ekstensi `ctype` sudah bawaan PHP dan dipakai untuk validasi input numerik.

## Arsitektur aplikasi

Aplikasi memisahkan tampilan, pintu masuk HTTP, aturan bisnis, dan aturan database:

- **Halaman tampilan** (`index.php`, `admin/`, `dokter/`, `users/`, `auth/`). Akses dibatasi oleh `middleware/check-role.php`. `index.php` dan halaman `auth/` boleh diakses tanpa login.
- **Pembacaan data** dilakukan halaman secara langsung lewat class di `services/query/`, tanpa melewati HTTP.
- **Penulisan data** melewati `api/*.php`. Endpoint ini memeriksa role dan CSRF, membaca input (JSON atau form), memanggil method di `services/*.php`, lalu menjawab JSON. Satu method service mewakili satu aksi.
- **Aturan terakhir** berada di database: constraint, trigger, dan function di `sql/`. Service tidak mengakali aturan tersebut.
- Setiap aksi yang mengubah data dijalankan di dalam satu transaksi (`api_transaction`).

## Struktur folder

```text
SIPOLI/
├── index.php                    Portal publik & beranda pasien
├── auth/                        Login, registrasi, logout
├── admin/                       Halaman admin
├── dokter/                      Halaman dokter
├── users/                       Halaman pasien (termasuk reservasi)
├── health/index.php             Health check, balas 200 JSON
├── config/
│   ├── bootstrap.php            .env, BASE_URL, session, helper e()/url()
│   └── database.php             Koneksi PDO ke PostgreSQL
├── middleware/
│   ├── check-role.php           Cek login & role (mode JSON untuk API)
│   └── csrf.php                 Token & verifikasi CSRF (form dan header API)
├── api/
│   ├── _boot.php                Inisialisasi API, transaksi, exception handler
│   ├── _input.php               Pembaca body JSON / form
│   ├── _response.php            Balasan JSON & terjemahan error database
│   ├── csrf.php                 Endpoint token CSRF
│   ├── admin/                   Endpoint khusus admin
│   ├── dokter/                  Endpoint khusus dokter
│   └── pasien/                  Endpoint khusus pasien
├── services/
│   ├── *.php                    Aturan bisnis / aksi tulis
│   ├── query/*.php              Query baca untuk halaman
│   └── _load.php                Loader seluruh service & query
├── assets/
│   ├── css/input.css            Sumber Tailwind
│   ├── css/style.css            Hasil build (sudah ikut repository)
│   ├── js/api.js                Helper request API & toast
│   ├── js/app.js                Interaksi UI
│   ├── js/flowbite-init.js      Inisialisasi komponen Flowbite
│   └── img/                     Aset gambar
├── sql/
│   ├── install.sql              Entry point instalasi schema
│   ├── 00_common.sql            Extension & fungsi util
│   ├── tables/                  20 tabel
│   ├── features/                28 modul trigger, function, dan view
│   └── seeds/                   Data awal poli & civitas
├── cron/
│   ├── pagi.php                 Job 07:00 WIB
│   └── malam.php                Job 21:00 WIB
├── .github/workflows/cron.yml   Penjadwal GitHub Actions
├── .env.example                 Template konfigurasi lingkungan
├── Dockerfile                   Build multi-stage untuk produksi
├── .dockerignore                Berkas yang tidak ikut ke image
└── package.json                 Dependensi Tailwind & Flowbite
```

## Prasyarat

- PHP 8.3 dengan ekstensi `pdo_pgsql`, `mbstring`, dan `calendar`
- PostgreSQL — server lokal atau project Neon
- Node.js 20+ dan npm
- `psql` CLI untuk menjalankan `sql/install.sql`
- Docker (opsional, untuk menjalankan image produksi di lokal)

## Instalasi dari nol

```bash
# 1. Ambil kode
git clone github.com/azana123/SIPOLI
cd SIPOLI

# 2. Buat file konfigurasi dari template, lalu isi nilainya
cp .env.example .env

# 3. Buat database kosong
createdb db_sipoli
# alternatif: psql -h localhost -U postgres -c 'CREATE DATABASE db_sipoli;'

# 4. Pasang schema, trigger, function, view, dan data awal
psql -h localhost -U postgres -d db_sipoli -f sql/install.sql

# 5. Pasang dependensi frontend dan build CSS
npm ci
npx tailwindcss -i ./assets/css/input.css -o ./assets/css/style.css

# 6. Jalankan server
php -S localhost:8000
```

Aplikasi siap dibuka di:

- `http://localhost:8000/index.php` — portal & beranda pasien
- `http://localhost:8000/api/csrf.php` — harus balas JSON
- `http://localhost:8000/health/index.php` — harus balas `{"status":"ok"}`

Catatan instalasi:

- `sql/install.sql` menggunakan perintah `\ir`, jadi harus dijalankan dengan `psql`, bukan editor SQL lain.
- Script instalasi membuat tabel baru. Untuk memasang ulang dari awal, buang database lama terlebih dahulu.
- Jika aplikasi diakses lewat subfolder, misalnya `http://localhost/SIPOLI`, isi `APP_BASE_URL=/SIPOLI` di `.env`.
- Untuk development dengan hot reload CSS: `npm run build:css`.

## Konfigurasi lingkungan

File `.env` tidak ikut repository dan tidak ikut image Docker. Gunakan `.env.example` sebagai panduan.

| Variabel | Fungsi |
|---|---|
| `APP_ENV` | `development` atau `production`. Mode production menolak `DB_HOST` / `DB_PASSWORD` yang kosong. |
| `APP_BASE_URL` | Prefix URL. Kosongkan jika aplikasi di root domain; isi `/SIPOLI` jika di subfolder. |
| `DB_HOST` | Host PostgreSQL. Untuk Neon, gunakan alamat pooler `*.neon.tech`. |
| `DB_PORT` | Port database, default `5432`. |
| `DB_NAME` | Nama database, contoh `db_sipoli`. |
| `DB_USER` | User database. |
| `DB_PASSWORD` | Password database. |
| `DB_SSLMODE` | `prefer` untuk lokal, `require` untuk Neon. |
| `DB_ENDPOINT` | Opsional, khusus Neon. Jika kosong, diambil dari bagian sebelum titik pertama pada `DB_HOST`. |
| `CRON_TOKEN` | Token rahasia untuk menjalankan `cron/pagi.php` dan `cron/malam.php` dari CLI. |

Saat `DB_HOST` mengandung `neon.tech`, koneksi otomatis menambahkan opsi `endpoint=...` yang dibutuhkan proxy Neon. Untuk PostgreSQL lokal, opsi ini tidak ditambahkan.

## Alur aplikasi

**Portal dan autentikasi.** `index.php` dapat dilihat tanpa login dan menampilkan jadwal serta dokter yang tersedia. Login dilakukan lewat `auth/login.php`. Setelah login, admin diarahkan ke `admin/dashboard.php`, dokter ke `dokter/dashboard.php`, dan pasien kembali ke `index.php`. Registrasi pasien membutuhkan NIM/NIP dan tanggal lahir yang cocok dengan data civitas.

**Admin.** Mengelola datamaster (pasien, tenaga layanan, poli, admin), membuat dan mempublikasikan jadwal, memantau reservasi, melakukan check-in, mencatat respons konfirmasi H-1, mengelola obat dan resep, serta mengirim pengumuman.

**Pasien.** Melihat jadwal, membuat reservasi, mengubah jadwal (maksimal sekali), membatalkan reservasi, mengonfirmasi kehadiran, dan melihat notifikasi.

**Kunjungan langsung (walk-in).** Untuk poli umum, admin dapat mendaftarkan pasien yang belum memiliki akun lewat form pasien offline. Data pasien dibuat lebih dulu tanpa `user_id`, lalu langsung dibuatkan antrean. Saat pasien melakukan registrasi, `fn_registrasi_pasien()` mencocokkan NIM/NIP dan tanggal lahir dengan data civitas, lalu menautkan akun baru ke data pasien yang sudah ada. Seluruh riwayat antrean dan pemeriksaan otomatis melekat.

**Hari pelayanan.** Job pagi membatalkan reservasi yang tidak dikonfirmasi, membuat antrean dari reservasi terkonfirmasi, mengirim pengingat H-1, dan memeriksa obat kadaluarsa. Job malam menutup hari. Dokter mencatat hasil pemeriksaan pada antrean dengan status draf atau selesai.

## API

Semua endpoint menjawab JSON dengan bentuk yang sama.

```json
{ "success": true, "message": "Berhasil.", "data": { ... } }
```

```json
{ "success": false, "message": "Kuota jadwal sudah penuh (5/5).", "errors": [] }
```

Untuk request yang mengubah data, ambil token CSRF terlebih dahulu lalu kirim sebagai header `X-CSRF-Token`:

```js
const token = await fetch('/api/csrf.php').then((r) => r.json());

await fetch('/api/pasien/reservasi.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-Token': token.data.csrf_token,
  },
  body: JSON.stringify({ action: 'create', jadwal_id: 12, keluhan: 'Sakit gigi' }),
});
```

### Daftar endpoint

| Endpoint | Method | Aksi | Role |
|---|---|---|---|
| `api/csrf.php` | GET | Ambil token CSRF | semua |
| `api/pasien/jadwal.php` | GET | Daftar jadwal & kalender | publik |
| `api/pasien/ringkasan.php` | GET | Ringkasan beranda | pasien |
| `api/pasien/reservasi.php` | GET, POST | `create`, `konfirmasi`, `batal`, `reschedule` | pasien |
| `api/pasien/notifikasi.php` | GET, POST | Tandai notifikasi dibaca | pasien |
| `api/admin/datamaster.php` | POST | `save_pasien`, `save_dokter`, `save_poli`, `save_admin`, `toggle_active` | admin |
| `api/admin/jadwal.php` | POST | `create`, `update`, `soft_delete` | admin |
| `api/admin/reservasi.php` | POST | `checkin`, `reschedule`, `batal_reservasi`, `batal_walkin` | admin |
| `api/admin/konfirmasi.php` | POST | Catat respons konfirmasi H-1 | admin |
| `api/admin/obat.php` | POST | `create_obat`, `atur_stok`, `toggle_active_obat` | admin |
| `api/admin/resep.php` | POST | Ubah status resep | admin |
| `api/admin/notifikasi.php` | POST | Tandai semua notifikasi dibaca | admin |
| `api/admin/kirim-informasi.php` | POST | Kirim pengumuman | admin |
| `api/admin/pasien-offline.php` | POST | Daftarkan pasien walk-in | admin |
| `api/dokter/pemeriksaan.php` | POST | `draft`, `selesai` | dokter |
| `api/dokter/jadwal.php` | POST | Ajukan perubahan jadwal | dokter |

Error dari database diterjemahkan di `api/_response.php`. Error trigger PostgreSQL (`SQLSTATE P0001`) dibaca pesannya, `23505` menjadi pesan "sudah terdaftar", dan seterusnya.

## Integrasi frontend

`assets/js/api.js` mengurus token CSRF, payload JSON, toast, dan redirect. Untuk menghubungkan form cukup menambahkan atribut `data-api-endpoint`:

```html
<form action="jadwal.php" method="POST"
      data-api-endpoint="/api/admin/jadwal.php"
      data-api-redirect="/admin/jadwal.php?tanggal=2026-11-17">
  <input type="hidden" name="action" value="create">
  ...
</form>
```

Untuk tombol, gunakan `data-api-click` dan `data-api-payload` berisi JSON:

```html
<button type="button" data-api-click
        data-api-endpoint="/api/pasien/reservasi.php"
        data-api-payload='{"action":"konfirmasi","reservasi_id":12,"hadir":true}'>
  Hadir
</button>
```

Script ini dimuat di `admin/components/navbar.php` dan `dokter/components/navbar.php` untuk halaman admin/dokter, serta lewat `users/components/api-script.php` untuk halaman pasien.

## Penjadwal harian

Database menyediakan `fn_job_pagi()` dan `fn_job_malam()`. Script `cron/pagi.php` dan `cron/malam.php` memanggilnya lewat CLI dan hanya berjalan jika Token cron di `.env` cocok dengan argumen yang diberikan:

```bash
php cron/pagi.php <CRON_TOKEN>
php cron/malam.php <CRON_TOKEN>
```

Penjadwalnya berada di `.github/workflows/cron.yml`, bukan di dalam container. Container free tier tidur setelah sekitar 15 menit tanpa traffic dan cron di dalam container tidak membangunkannya. Script GitHub Actions terhubung langsung ke database sehingga tidak perlu aplikasi bangun.

- `0 0 * * *` UTC = **07:00 WIB** → job pagi
- `0 14 * * *` UTC = **21:00 WIB** → job malam
- Bisa dijalankan manual lewat **Actions → SIPOLI Cron → Run workflow**

Job pagi, secara berurutan:

1. `fn_batalkan_tidak_konfirmasi()` — reservasi hari ini yang tidak dijawab dibatalkan dan pasien diberi notifikasi.
2. `fn_generate_antrean_harian()` — antrean hari H dibuat dari reservasi terkonfirmasi dengan nomor otomatis.
3. `fn_kirim_konfirmasi_h1()` — pengingat konfirmasi jadwal besok masuk ke inbox pasien.
4. `fn_cek_obat_kadaluarsa()` — peringatan obat kadaluarsa/hampir kadaluarsa dikirim ke admin.

Job malam: `fn_tutup_hari()` — antrean yang belum selesai dibatalkan dan reservasinya menjadi `tidak_hadir`.

## Deploy dengan Docker
Contoh ENVIRONMENT VARIABLE
```
# ======================================================================
# SIPOLI — contoh konfigurasi environment
# ======================================================================
# Salin file ini menjadi .env, lalu isi nilai sesuai environment kamu:
#   cp .env.example .env
#
# Di hosting (mis. SnapDeploy) dan GitHub Actions, isi nilai ini lewat
# dashboard Environment / Repository Secrets, bukan lewat file .env.

# ----------------------------------------------------------------------
# Aplikasi
# ----------------------------------------------------------------------
# development | production
APP_ENV=development

# Kosongkan jika aplikasi berada di root domain.
# Jika diakses lewat subfolder, misalnya http://localhost/SIPOLI, isi /SIPOLI.
APP_BASE_URL=

# ----------------------------------------------------------------------
# Database PostgreSQL
# ----------------------------------------------------------------------
# Untuk server PostgreSQL lokal, gunakan host biasa seperti localhost.
# Untuk Neon, gunakan alamat pooler, misalnya:
# ep-xxxx-pooler.c-4.ap-southeast-1.aws.neon.tech
DB_HOST=localhost
DB_PORT=5432
DB_NAME=db_sipoli
DB_USER=postgres
DB_PASSWORD=ganti_password_db_kamu

# prefer untuk lokal, require untuk Neon / server dengan SSL wajib.
DB_SSLMODE=prefer

# Opsional. Hanya dipakai untuk Neon. Jika dikosongkan, nilai diambil
# dari bagian sebelum titik pertama pada DB_HOST.
DB_ENDPOINT=

# ----------------------------------------------------------------------
# Penjadwal (cron)
# ----------------------------------------------------------------------
# Token untuk menjalankan cron/pagi.php dan cron/malam.php dari CLI.
# Gunakan string acak yang panjang, misalnya: openssl rand -hex 32
CRON_TOKEN=ganti_dengan_token_acak_panjang
```

`Dockerfile` memakai build dua tahap. Tahap Node memasang dependensi dan membangun `assets/css/style.css` dari `assets/css/input.css`. Tahap runtime memakai `php:8.3-apache`, memasang ekstensi `pdo_pgsql`, `mbstring`, dan `calendar`, lalu menyalin kode aplikasi beserta CSS hasil build dan `flowbite.min.js` yang dibutuhkan halaman.

```bash
docker build -t sipoli .
docker run -p 8080:80 \
  -e APP_ENV=production \
  -e APP_BASE_URL= \
  -e DB_HOST=... \
  -e DB_PORT=5432 \
  -e DB_NAME=... \
  -e DB_USER=... \
  -e DB_PASSWORD=... \
  -e DB_SSLMODE=require \
  sipoli
```

Buka `http://localhost:8080/index.php`, `http://localhost:8080/api/csrf.php`, dan `http://localhost:8080/health/index.php`.

Untuk SnapDeploy:

1. Push `Dockerfile` dan `.dockerignore` ke branch yang dipakai SnapDeploy.
2. Di menu container → **Environment**, isi variabel pada tabel konfigurasi lingkungan di atas. Kosongkan `APP_BASE_URL` karena aplikasi berada di root domain `*.containers.snapdeploy.app`.
3. Deploy, lalu cek build log sampai status `RUNNING`.
4. Periksa `/index.php`, `/api/csrf.php`, dan `/health/index.php`.

`.dockerignore` mengecualikan `.git`, `.github`, `node_modules`, `.env`, `.env.example`, dan file markdown. Kredensial tidak pernah dikirim lewat file — isi lewat dashboard hosting.

Konsekuensi free tier yang perlu diingat:

- Container tidur setelah sekitar 15 menit idle. Pengunjung browser membangunkannya dalam waktu sekitar 60 detik.
- Tidak ada persistent disk. File yang dibuat saat runtime hilang saat restart/redeploy. Semua data aplikasi tersimpan di database. Session PHP masih berbasis file, sehingga login bisa ter-logout setelah container restart.

## Aturan database

- Reservasi hanya untuk H-7 s.d. H-1, jadwal harus dipublikasikan, kuota dicek dengan lock.
- Satu pasien hanya satu reservasi aktif per jadwal; reschedule maksimal sekali.
- Nomor reservasi dan antrean dibuat otomatis.
- Status antrean dan reservasi saling sinkron.
- Perubahan stok obat tercatat di `log_stok_obat`.
- Login gagal lima kali mengunci akun selama 15 menit.
- Job pagi membatalkan reservasi yang tidak dikonfirmasi, membuat antrean, dan mengirim pengingat.

## Troubleshooting

| Gejala | Penanganan |
|---|---|
| Error `could not find driver` saat koneksi database | Pasang ekstensi `pdo_pgsql` pada PHP yang dipakai server/container. |
| Error `Call to undefined function cal_days_in_month()` | Pasang ekstensi `calendar`. |
| Error `Call to undefined function mb_substr()` | Pasang ekstensi `mbstring`. |
| Halaman tampil tanpa styling | Jalankan `npm ci` lalu `npx tailwindcss -i ./assets/css/input.css -o ./assets/css/style.css`. |
| Dropdown/carousel Flowbite tidak berfungsi | Pastikan `node_modules/flowbite/dist/flowbite.min.js` tersedia di document root. Build Docker sudah menyertakannya. |
| Koneksi lokal gagal dengan pesan `invalid command-line argument ... endpoint` | Pastikan `DB_HOST` bukan alamat Neon, atau hapus opsi `endpoint` yang ditambahkan manual. |
| Koneksi Neon gagal karena endpoint tidak dikenal | Isi `DB_ENDPOINT` di `.env` dengan ID endpoint Neon, tanpa akhiran `-pooler`. |
| Redirect salah saat di subfolder | Isi `APP_BASE_URL` di `.env` dengan prefix yang benar, misalnya `/SIPOLI`. |
| Cron Gagal: token tidak valid | Pastikan argumen cron sama persis dengan `CRON_TOKEN` di lingkungan yang dipakai script. |
