FROM php:8.2-apache

# Aktifkan AllowOverride All agar .htaccess di uploads/ diproses oleh Apache
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && a2enmod rewrite

WORKDIR /var/www/html
