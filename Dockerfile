FROM php:8.3-apache

# Ekstensi yang dipakai aplikasi:
# - pdo_pgsql : koneksi ke PostgreSQL/Neon
# - mbstring  : mb_substr() saat menyusun aturan resep
# - calendar  : cal_days_in_month() di halaman kalender reservasi
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libpq-dev \
      libonig-dev \
 && docker-php-ext-install pdo_pgsql mbstring calendar \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 3000
