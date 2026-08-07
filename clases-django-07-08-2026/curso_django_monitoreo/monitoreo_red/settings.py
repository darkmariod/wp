"""Configuración del proyecto monitoreo_red.

Generado con 'django-admin startproject' (Django 6.1) y adaptado para la clase:
  - Credenciales y base de datos desde el archivo .env
  - MySQL en lugar de SQLite
  - Carpeta templates/ a nivel de proyecto
  - Correo por consola (no necesitamos un servidor SMTP en clase)
"""

import os
from pathlib import Path

from dotenv import load_dotenv

BASE_DIR = Path(__file__).resolve().parent.parent

# Lee el archivo .env que está junto a manage.py
load_dotenv(BASE_DIR / ".env")


# --- Seguridad --------------------------------------------------------------
# En un proyecto real la SECRET_KEY también va en el .env.
SECRET_KEY = os.getenv(
    "SECRET_KEY",
    "django-insecure-clave-solo-para-la-clase-no-usar-en-produccion",
)

DEBUG = os.getenv("DEBUG", "True") == "True"

ALLOWED_HOSTS = ["localhost", "127.0.0.1"]


# --- Aplicaciones -----------------------------------------------------------
INSTALLED_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",
    # Django REST Framework, para la API.
    "rest_framework",
    # Nuestra app. Sin esta línea, Django ignora sus modelos.
    "dispositivos",
]

# Configuración mínima de la API para la clase.
REST_FRAMEWORK = {
    # AllowAny: sin login, para poder probar rápido. En producción NO.
    "DEFAULT_PERMISSION_CLASSES": ["rest_framework.permissions.AllowAny"],
    "DEFAULT_PAGINATION_CLASS":
        "rest_framework.pagination.PageNumberPagination",
    "PAGE_SIZE": 10,
}

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
    "django.contrib.sessions.middleware.SessionMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
    "django.contrib.auth.middleware.AuthenticationMiddleware",
    "django.contrib.messages.middleware.MessageMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
]

ROOT_URLCONF = "monitoreo_red.urls"

TEMPLATES = [
    {
        "BACKEND": "django.template.backends.django.DjangoTemplates",
        # DIRS: carpeta de templates del proyecto (los comunes: base.html).
        "DIRS": [BASE_DIR / "templates"],
        # APP_DIRS: además busca en cada app, en app/templates/app/
        "APP_DIRS": True,
        "OPTIONS": {
            "context_processors": [
                "django.template.context_processors.request",
                "django.contrib.auth.context_processors.auth",
                "django.contrib.messages.context_processors.messages",
            ],
        },
    },
]

WSGI_APPLICATION = "monitoreo_red.wsgi.application"


# --- Base de datos ----------------------------------------------------------
# Las mismas variables que ya usa el README del curso.
DATABASES = {
    "default": {
        "ENGINE": "django.db.backends.mysql",
        "NAME": os.getenv("DB_NAME", "monitoreo_red"),
        "USER": os.getenv("DB_USER", "root"),
        "PASSWORD": os.getenv("DB_PASSWORD", ""),
        "HOST": os.getenv("DB_HOST", "localhost"),
        "PORT": os.getenv("DB_PORT", "3306"),
        "OPTIONS": {
            "charset": "utf8mb4",
        },
    }
}


AUTH_PASSWORD_VALIDATORS = [
    {"NAME": "django.contrib.auth.password_validation."
             "UserAttributeSimilarityValidator"},
    {"NAME": "django.contrib.auth.password_validation.MinimumLengthValidator"},
    {"NAME": "django.contrib.auth.password_validation.CommonPasswordValidator"},
    {"NAME": "django.contrib.auth.password_validation.NumericPasswordValidator"},
]


# --- Idioma y zona horaria --------------------------------------------------
LANGUAGE_CODE = "es"

TIME_ZONE = "America/Guayaquil"

USE_I18N = True

USE_TZ = True


# --- Archivos estáticos -----------------------------------------------------
STATIC_URL = "static/"

DEFAULT_AUTO_FIELD = "django.db.models.BigAutoField"


# --- Correo -----------------------------------------------------------------
# En clase usamos el backend de CONSOLA: el correo se imprime en la terminal.
# Así se ve el resultado sin servidor SMTP, sin contraseñas y sin internet.
EMAIL_BACKEND = "django.core.mail.backends.console.EmailBackend"

DEFAULT_FROM_EMAIL = os.getenv("DEFAULT_FROM_EMAIL", "alertas@monitoreo.local")

# Para enviar de VERDAD por Gmail, se comenta la línea de arriba y se usa esto
# (es la configuración clásica de Django, la misma del artículo de PyWombat):
#
# EMAIL_BACKEND = "django.core.mail.backends.smtp.EmailBackend"
# EMAIL_HOST = "smtp.gmail.com"
# EMAIL_PORT = 587
# EMAIL_USE_TLS = True
# EMAIL_HOST_USER = os.getenv("EMAIL_HOST_USER")
# EMAIL_HOST_PASSWORD = os.getenv("EMAIL_HOST_PASSWORD")
#
# Gmail exige una "contraseña de aplicación", no la contraseña normal.
