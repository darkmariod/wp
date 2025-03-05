FROM php:8.3-fpm

# Instalar extensiones necesarias para WordPress
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    zip unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Configuración opcional
COPY php.ini /usr/local/etc/php/

WORKDIR /var/www/html
