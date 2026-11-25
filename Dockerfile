# ======================================================================
# SIPOLI — image produksi: PHP 8.3 + Apache
# ======================================================================
# ----------------------------------------------------------------------
# Tahap 1: siapkan aset frontend (Tailwind CSS v4 + Flowbite)
# ----------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

# Salin sumber aplikasi agar Tailwind bisa memindai class di file PHP/JS.
COPY . .
RUN npx tailwindcss -i ./assets/css/input.css -o ./assets/css/style.css --minify

# ----------------------------------------------------------------------
# Tahap 2: runtime
# ----------------------------------------------------------------------
FROM php:8.3-apache

# pdo_pgsql : koneksi ke PostgreSQL / Neon
# mbstring  : mb_substr() saat menyusun aturan resep
# calendar  : cal_days_in_month() di kalender reservasi
# ctype     : sudah bawaan PHP (digunakan untuk validasi input numerik)
# curl      : dipakai HEALTHCHECK
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libpq-dev \
      libonig-dev \
      curl \
 && docker-php-ext-install pdo_pgsql mbstring calendar \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html/

# Halaman-halaman memuat /node_modules/flowbite/dist/flowbite.min.js.
# Sertakan tepat file itu saja (tanpa membawa seluruh node_modules).
COPY --from=assets /app/assets/css/style.css /var/www/html/assets/css/style.css
COPY --from=assets /app/node_modules/flowbite/dist/flowbite.min.js /var/www/html/node_modules/flowbite/dist/flowbite.min.js

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/health/index.php > /dev/null || exit 1

CMD ["apache2-foreground"]
