# ================================================================
# Stage 1: Build assets (Node)
# ================================================================
FROM node:22-alpine AS build

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ================================================================
# Stage 2: Runtime (PHP 8.4 + Nginx + Supervisor)
# ================================================================
FROM php:8.4-fpm-alpine

LABEL maintainer="Inmobiliaria Riobamba"
LABEL description="Inmobiliaria Riobamba — Laravel + Filament"

# ── System deps ──
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    bash \
    oniguruma-dev \
    libzip-dev \
    libxml2-dev \
    icu-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        xml \
        bcmath \
        intl

# ── Cleanup ──
RUN rm -rf /var/cache/apk/* /tmp/*

# ── Composer ──
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── App ──
WORKDIR /var/www/html

COPY --from=build /build/public/build /var/www/html/public/build
COPY . .

# ── Install PHP deps (production) ──
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && rm -rf bootstrap/cache/*.php \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ── Permisos Laravel ──
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    public/build \
    && chmod -R 775 storage bootstrap/cache

# ── Configs ──
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
