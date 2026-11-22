FROM php:8.2-apache

# Konfigurasi Apache & Workdir
WORKDIR /var/www/html

# Salin seluruh file proyek
COPY . /var/www/html/

# Atur permission agar www-data (Apache) dapat menulis file upload dan posts.json
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/uploads \
    && chmod 664 /var/www/html/posts.json

# Expose port HTTP standar
EXPOSE 80
