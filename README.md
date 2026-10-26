# SIPOLI

```text
my-project/
├── admin/                  # Halaman dan komponen UI khusus Admin
│   ├── components/         # Potongan UI admin (header, footer, sidebar)
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php
│   ├── dashboard.php       # Halaman utama panel admin
│   └── users.php           # Halaman manajemen pengguna
│
├── user/                   # Halaman dan komponen UI khusus User
│   ├── components/         # Potongan UI user (header, footer, navbar)
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── navbar.php
│   ├── dashboard.php       # Halaman utama panel user
│   └── profile.php         # Halaman profil user
│
├── auth/                   # Sistem autentikasi (masuk dan keluar akun)
│   ├── login.php           # Form antarmuka login
│   ├── logout.php          # Skrip pemutus sesi (logout)
│   └── process-login.php   # Skrip pemrosesan data login
│
├── middleware/             # Pengecekan hak akses dan session
│   ├── auth.php            # Memastikan pengguna sudah login
│   ├── check_admin.php     # Memastikan pengguna memiliki role admin
│   └── check_user.php      # Memastikan pengguna memiliki role user
│
├── assets/                 # Aset statis aplikasi
│   ├── css/
│   │   ├── input.css       # File input untuk Tailwind CSS
│   │   └── style.css       # File output CSS hasil kompilasi
│   ├── js/                 # Skrip JavaScript kustom
│   └── images/             # Direktori gambar dan ikon
│
├── config/
│   └── database.php        # Konfigurasi koneksi ke database PDO/MySQLi
│
├── includes/
│   └── functions.php       # Kumpulan fungsi PHP yang sering digunakan (helpers)
│
├── index.php               # Landing page publik (Halaman beranda)
├── package.json            # Konfigurasi package/dependencies (Node.js)
└── tailwind.config.js      # File konfigurasi Tailwind CSS