# Curso de Django — Monitoreo de red

Proyecto de la clase de **4 horas**. Construimos una aplicación web con Django
conectada a **MySQL**, con CRUD completo, panel de administración y una API REST.

Usamos las mismas tablas del día 8 (dispositivos, redes, monitoreos), pero ahora
con Django en lugar de SQL escrito a mano.

---

## Guías

| Archivo | Para qué sirve |
|---|---|
| [CHULETA.md](CHULETA.md) | Todos los comandos en una hoja. Tenla al lado. |
| [GUIA_CLASE_4H.md](GUIA_CLASE_4H.md) | El guion de las 4 horas, para proyectar en pantalla |
| [EJERCICIOS.md](EJERCICIOS.md) | Ejercicios fáciles con soluciones |
| [PROBLEMAS.md](PROBLEMAS.md) | Los errores que van a salir y cómo resolverlos |

---

## Versiones (importante)

| Paquete | Versión | Por qué esa |
|---|---|---|
| Python | 3.12 o 3.13 | Django 5.2 no soporta 3.14 oficialmente |
| Django | 5.2 LTS | **No uses Django 6.x**: DRF todavía no funciona con esa versión |
| mysqlclient | 2.2.8 | Es el driver que exige Django. `mysql-connector-python` NO sirve aquí |
| djangorestframework | 3.17.2 | Para la API |

---

## Instalación

### 1. Crear y activar el entorno virtual

**Mac / Linux**

```bash
python3 -m venv venv && source venv/bin/activate
```

**Windows (PowerShell)**

```powershell
python -m venv venv ; .\venv\Scripts\Activate.ps1
```

Cuando está activo, el prompt muestra `(venv)` al inicio.

### 2. Instalar las dependencias

```bash
pip install -r requirements.txt
```

> Si `mysqlclient` falla al instalar en Windows, mira
> [PROBLEMAS.md](PROBLEMAS.md), punto 1.

### 3. Configurar las credenciales

**Mac / Linux**

```bash
cp env.example.txt .env
```

**Windows**

```powershell
copy env.example.txt .env
```

Abre el `.env` y pon tu contraseña real de MySQL.

### 4. Crear la base de datos en MySQL

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS monitoreo_red CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Crear las tablas

```bash
python manage.py migrate
```

### 6. Cargar datos de ejemplo

```bash
python manage.py cargar_datos
```

### 7. Crear el usuario del admin

```bash
python manage.py createsuperuser
```

### 8. Levantar el servidor

```bash
python manage.py runserver
```

Abre <http://127.0.0.1:8000/>

---

## Qué hay en cada dirección

| URL | Qué muestra |
|---|---|
| `/dispositivos/` | Lista con buscador |
| `/dispositivos/nuevo/` | Formulario de alta |
| `/dispositivos/1/` | Detalle con sus monitoreos |
| `/dispositivos/reporte/` | Agregaciones por red |
| `/admin/` | Panel de administración |
| `/api/dispositivos/` | La API REST navegable |

---

## Estructura del proyecto

```text
curso_django_monitoreo/
├── manage.py                 <- el comando de todo
├── .env                      <- tus credenciales (no se sube)
├── requirements.txt
│
├── monitoreo_red/            <- configuración del PROYECTO
│   ├── settings.py           <- base de datos, apps, templates, correo
│   └── urls.py               <- rutas principales + router de la API
│
├── templates/
│   └── base.html             <- plantilla madre de todas
│
└── dispositivos/             <- la APP
    ├── models.py             <- las tablas
    ├── views.py              <- el CRUD
    ├── forms.py              <- los formularios
    ├── urls.py               <- las rutas de la app
    ├── admin.py              <- el panel
    ├── serializers.py        <- JSON para la API
    ├── api.py                <- los ViewSets
    ├── decoradores.py
    ├── correos.py
    ├── migrations/           <- el historial de cambios de la base
    ├── static/dispositivos/  <- CSS
    └── templates/dispositivos/
```

Regla que confunde a todos: **proyecto** es la configuración,
**app** es la funcionalidad. Un proyecto puede tener muchas apps.
