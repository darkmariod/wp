# Puesta en producción

Mi Escuelita — Plataforma digital para el colegio Pestalozzi.

## Requisitos

- PHP 8.3+
- Composer 2
- Node 20+
- Base de datos: SQLite (desarrollo) o MySQL 8 (producción)

## Instalación

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Base de datos (SQLite en desarrollo)
touch database/database.sqlite
php artisan migrate --seed

# Build de assets
npm run build
```

## Configuración MySQL para producción

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aula_pestalozzi
DB_USERNAME=root
DB_PASSWORD=tu_password
```

## Cola de trabajos

Las notificaciones (email) usan la cola. En producción:

```bash
php artisan queue:work --daemon
```

## Tareas programadas (scheduler)

El scheduler publica contenido programado cada minuto y hace respaldo diario.

En producción, agrégale esto al crontab del servidor:

```cron
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Ver las tareas activas:

```bash
php artisan schedule:list
```

## Respaldo manual

```bash
php artisan backup:database
# Opcional: ruta y cuántos conservar
php artisan backup:database --path=/var/backups --keep=30
```

## Sirviendo la app

```bash
php artisan serve --port=8123        # desarrollo
# Producción: usa el worker de PHP-FPM / el servidor web que prefieras.
```

## Enlaces útiles

- Panel de administración (staff): `/admin`
- Portal familiar: `/mi-escuelita`
- Política de privacidad: `/privacidad`

## Seguridad

- Los archivos de evidencia viven en `storage/app/private` y solo se sirven
  vía `/storage-privado/{id}` pasando por la policy del dueño. Nunca se
  publican a la web.
- Los roles tienen sus propias puertas (`familia` / `staff`) con límites de
  peticiones (rate limiting).
- No se almacenan calificaciones, porcentajes, rankings ni asistencia (no
  negociables del proyecto).
