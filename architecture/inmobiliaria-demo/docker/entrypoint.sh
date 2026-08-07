#!/bin/bash
set -e

# ── Esperar a MySQL ──
if [ -n "$DB_HOST" ]; then
    echo "→ Esperando a MySQL ($DB_HOST:$DB_PORT)..."
    for i in $(seq 1 30); do
        if nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; then
            echo "✓ MySQL está listo"
            break
        fi
        if [ "$i" -eq 30 ]; then
            echo "⚠️  MySQL no respondió después de 30 intentos, continuando..."
        fi
        sleep 2
    done
fi

# ── .env (solo si no existe) ──
if [ ! -f .env ]; then
    echo "→ Creando .env desde .env.example..."
    cp .env.example .env
fi

# ── APP_KEY ──
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    echo "→ Generando APP_KEY..."
    php artisan key:generate --force
fi

# ── Migraciones + seed ──
echo "→ Corriendo migraciones..."
php artisan migrate --force || echo "⚠️  Migrations failed, check DB connection"

echo "→ Sembrando datos demo..."
php artisan db:seed --force || echo "⚠️  Seed failed (might be duplicate data)"

# ── Cache ──
echo "→ Optimizando..."
php artisan optimize || true
php artisan view:cache || true

# ── Storage link ──
php artisan storage:link --force || true

echo "✓ App lista — arrancando servicios..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
