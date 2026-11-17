# SIPOLI

Sistem Informasi Poliklinik untuk civitas akademika Politeknik Negeri Malang. Aplikasi ini mengurus jadwal dokter, reservasi, antrean, pemeriksaan, resep obat, dan notifikasi — untuk tiga peran: **admin**, **dokter**, dan **pasien**.

## Tech stack

| Bagian | Dipakai |
|---|---|
| Bahasa | PHP 8.3 (`declare(strict_types=1)`) |
| Database | PostgreSQL (Neon di produksi), lewat PDO `pdo_pgsql` |
| Frontend | Tailwind CSS v4 + Flowbite, Font Awesome, JavaScript vanilla |
| Server lokal | `php -S localhost:8000` |
| Container | Docker `php:8.3-apache` + ekstensi `pdo_pgsql`, `mbstring`, `calendar` |
| Hosting | SnapDeploy (free tier, auto-deploy dari GitHub) |
| Penjadwal | GitHub Actions (`cron`) yang memanggil script PHP langsung ke database |
| Keamanan | Session PHP, CSRF token, role guard, `password_hash`, lockout login |

Kenapa ekstensi itu penting: `pdo_pgsql` untuk koneksi ke Neon, `mbstring` untuk `mb_substr()` saat menyusun aturan resep, dan `calendar` untuk `cal_days_in_month()` di kalender reservasi. Tanpa itu, aplikasi bisa jalan di lokal tetapi error di container.

## Folder `api/` dan `services/`

Sebelumnya semua tombol di halaman langsung memproses data lewat `POST` di dalam file tampilan. Akibatnya, urusan tampilan dan urusan data jadi campur, dan tiap halaman punya cara sendiri-sendiri dalam menangani error.

Sekarang dipisah:

- **`api/`** — pintu masuk HTTP. Tugasnya: cek siapa yang login, cek CSRF, baca input, panggil service, lalu balas JSON.
- **`services/`** — tempat semua aturan dan query database hidup. Satu method untuk satu aksi. Subfolder `services/query/` berisi query baca untuk halaman; halaman memanggilnya langsung lewat PHP, tanpa HTTP.
- **`sql/`** — aturan terakhir tetap di database (trigger, function, constraint). Service tidak boleh mengakali aturan ini.


## Struktur folder

```
SIPOLI/
├── index.php                 Halaman masuk untuk semua role
├── config/
│   ├── bootstrap.php         Baca .env, BASE_URL, session, helper e()/url()
│   ├── config.php            BASE_URL versi lama (masih dipakai sebagian halaman)
│   └── database.php          Koneksi PDO ke PostgreSQL
├── middleware/
│   ├── check-role.php        Cek login & role (bisa balas JSON untuk API)
│   └── csrf.php              Token CSRF, termasuk versi untuk API (header)
├── api/
│   ├── _boot.php             Inisialisasi API, transaksi, dan exception handler
│   ├── _input.php            Baca body JSON / form
│   ├── _response.php         Balasan JSON + terjemahan error database
│   ├── csrf.php              GET token CSRF untuk frontend
│   ├── admin/                Endpoint khusus admin
│   ├── dokter/               Endpoint khusus dokter
│   └── pasien/               Endpoint khusus pasien
├── services/                 Logika bisnis (aksi) + services/query/ (baca)
├── admin/, dokter/           Halaman tampilan admin & dokter
├── users/, pasien/           Halaman tampilan pasien
├── assets/                   CSS (Tailwind), JS, gambar
├── sql/                      Tabel, function, trigger, view, seed
├── cron/                     Job pagi & malam (CLI)
├── Dockerfile                Image PHP 8.3 + Apache untuk produksi
├── .dockerignore             Barang yang tidak ikut ke image
└── .github/workflows/cron.yml  Penjadwal job pagi & malam
```

## Menjalankan proyek

Butuh PHP dengan `pdo_pgsql`, PostgreSQL, dan Node.js.

```bash
# 1. Siapkan database
psql -h localhost -U postgres -d db_sipoli -f sql/install.sql

# 2. Isi .env
cp .env .env.local   # kalau mau, lalu sesuaikan

# 3. CSS
npm ci
npx tailwindcss -i ./assets/css/input.css -o ./assets/css/style.css

# 4. Jalankan
php -S localhost:8000
```

Isi `.env` yang penting: `APP_BASE_URL`, kredensial `DB_*`, dan `CRON_TOKEN` untuk job. Kalau aplikasi diakses lewat `http://localhost/SIPOLI`, isi `APP_BASE_URL=/SIPOLI`.

Bisa juga menjalankan versi container tanpa mengganggu environment lokal:

```bash
docker build -t sipoli .
docker run -p 8080:80 --env-file .env sipoli
```

Lalu buka `http://localhost:8080`.

## Cara pakai API

Semua endpoint balas JSON dengan bentuk yang sama:

```json
{ "success": true, "message": "Berhasil.", "data": { ... } }
```

Kalau gagal:

```json
{ "success": false, "message": "Kuota jadwal sudah penuh (5/5).", "errors": [] }
```

Untuk request yang mengubah data, ambil token dulu lalu kirim lewat header:

```js
const token = await fetch('/api/csrf.php').then(r => r.json());

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

| Endpoint | Aksi (`action`) | Role |
|---|---|---|
| `api/csrf.php` | GET token | semua |
| `api/pasien/ringkasan.php` | GET ringkasan beranda | pasien |
| `api/pasien/reservasi.php` | `create`, `konfirmasi`, `batal`, `reschedule` | pasien |
| `api/pasien/notifikasi.php` | tandai dibaca | pasien |
| `api/admin/datamaster.php` | `save_pasien`, `save_dokter`, `save_poli`, `save_admin`, `toggle_active` | admin |
| `api/admin/jadwal.php` | `create`, `update`, `soft_delete` | admin |
| `api/admin/reservasi.php` | `checkin`, `reschedule`, `batal_reservasi`, `batal_walkin` | admin |
| `api/admin/konfirmasi.php` | catat respons H-1 | admin |
| `api/admin/obat.php` | `create_obat`, `atur_stok`, `toggle_active_obat` | admin |
| `api/admin/resep.php` | ubah status resep | admin |
| `api/admin/notifikasi.php` | tandai semua dibaca | admin |
| `api/admin/kirim-informasi.php` | kirim pengumuman | admin |
| `api/admin/pasien-offline.php` | daftar pasien walk-in | admin |
| `api/dokter/pemeriksaan.php` | `draft`, `selesai` | dokter |
| `api/dokter/jadwal.php` | ajukan perubahan jadwal | dokter |

Error dari database sudah diterjemahkan di `api/_response.php`. Misalnya SQLSTATE `P0001` dari trigger dibaca pesannya, `23505` jadi pesan "sudah terdaftar", dan seterusnya.

## Job harian (cron)

Database sudah punya `fn_job_pagi()` dan `fn_job_malam()`. Script `cron/pagi.php` dan `cron/malam.php` tinggal memanggilnya lewat CLI:

```bash
php cron/pagi.php <CRON_TOKEN>
php cron/malam.php <CRON_TOKEN>
```

Script ini cuma bisa dijalankan dari CLI dan harus membawa token yang sama dengan `CRON_TOKEN` di `.env`.

### Apa yang dikerjakan job pagi (07:00 WIB)

Urutannya sengaja begini:

1. `fn_batalkan_tidak_konfirmasi()` — reservasi hari ini yang tidak dijawab dibatalkan, pasien dapat notifikasi.
2. `fn_generate_antrean_harian()` — antrean hari H dibuat dari reservasi terkonfirmasi, nomor otomatis.
3. `fn_kirim_konfirmasi_h1()` — pengingat konfirmasi untuk jadwal besok masuk ke inbox pasien.
4. `fn_cek_obat_kadaluarsa()` — peringatan obat kadaluarsa/hampir kadaluarsa ke admin.

### Apa yang dikerjakan job malam (21:00 WIB)

1. `fn_tutup_hari()` — antrean yang belum selesai dibatalkan, reservasinya jadi `tidak_hadir`.

### Kenapa dijalankan dari GitHub Actions

Container free SnapDeploy tidur setelah ±15 menit tanpa traffic, dan **cron job tidak membangunkan container yang sedang tidur**. Karena itu penjadwalnya tidak ditaruh di dalam container, melainkan di GitHub Actions. Script-nya langsung terhubung ke Neon, jadi tidak perlu aplikasi bangun.

Workflow ada di `.github/workflows/cron.yml`:

- `0 0 * * *` UTC = **07:00 WIB** → job pagi
- `0 14 * * *` UTC = **21:00 WIB** → job malam
- Bisa dijalankan manual lewat **Actions → SIPOLI Cron → Run workflow**

## Deploy dengan Docker ke SnapDeploy

`Dockerfile` memakai `php:8.3-apache`, install ekstensi yang disebut di tech stack, lalu menyalin kode ke `/var/www/html` dan membuka port 80. SnapDeploy akan memakai Dockerfile ini apa adanya.

`.dockerignore` menyembunyikan `.git`, `.github`, `node_modules`, `.env`, dan file markdown dari image. Kredensial **tidak** dikirim lewat `.env` — isi lewat dashboard SnapDeploy.

Langkah deploy:

1. Push `Dockerfile`, `.dockerignore`, dan `.github/workflows/cron.yml` ke branch yang dipakai SnapDeploy.
2. Di SnapDeploy → container → **Environment**, isi:
   ```text
   APP_ENV=development
   APP_BASE_URL=
   DB_HOST=...
   DB_PORT=5432
   DB_NAME=...
   DB_USER=...
   DB_PASSWORD=...
   DB_SSLMODE=require
   ```
   `APP_BASE_URL` dikosongkan karena aplikasi berada di root domain `*.containers.snapdeploy.app`.
3. Deploy, lalu cek build log sampai status `RUNNING`.
4. Buka `https://<nama>.containers.snapdeploy.app/index.php` dan `.../api/csrf.php`.

Dua konsekuensi free tier yang perlu diingat:

- Container tidur setelah ±15 menit idle. Pengunjung browser membangunkannya dalam ±60 detik.
- Tidak ada persistent disk. File yang dibuat saat runtime hilang saat restart/redeploy. Semua data aplikasi sudah disimpan di database, jadi aman. Session PHP masih berbasis file, jadi login bisa ter-logout setelah container restart.

## Integrasi di sisi frontend

POST handler lama sudah dihapus, dan form-formnya sudah dialihkan ke API lewat `assets/js/api.js`. Caranya: cukup tambahkan atribut `data-api-endpoint` pada `<form>`:

```html
<form action="jadwal.php" method="POST"
      data-api-endpoint="/api/admin/jadwal.php"
      data-api-redirect="/admin/jadwal.php?tanggal=2026-11-17">
    <input type="hidden" name="action" value="create">
    ...
</form>
```

Helper ini yang mengurus token CSRF, payload JSON, toast, dan reload setelah sukses. Untuk tombol (misalnya konfirmasi hadir), pakai `data-api-click` + `data-api-payload` berisi JSON.

Script sudah disertakan lewat `admin/components/navbar.php` dan `dokter/components/navbar.php`. Untuk halaman pasien, pakai `users/components/api-script.php`.

Catatan: halaman detail dan reservasi pasien masih memakai tampilan prototipe. Tombol aksinya sudah terhubung ke API lewat `id` dari query string (`detail-reservasi.php?id=...`), jadi daftar reservasi pasien perlu meneruskan `id` yang sebenarnya.

## Aturan database yang berlaku

- Reservasi hanya H-7 s.d. H-1, jadwal harus dipublikasikan, kuota dicek dengan lock.
- Satu pasien hanya satu reservasi aktif per jadwal; reschedule maksimal sekali.
- Nomor reservasi dan antrean dibuat otomatis.
- Status antrean dan reservasi saling sinkron.
- Perubahan stok obat tercatat di `log_stok_obat`.
- Login gagal lima kali mengunci akun 15 menit.
- Job pagi membatalkan reservasi yang tidak dikonfirmasi, membuat antrean, dan mengirim pengingat.
