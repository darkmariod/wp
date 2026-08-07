# Chuleta — todos los comandos en una hoja

Tenla abierta en el Mac mientras proyectas.

---

## Entorno virtual

| Acción | Mac / Linux | Windows (PowerShell) |
|---|---|---|
| Crear | `python3 -m venv venv` | `python -m venv venv` |
| Activar | `source venv/bin/activate` | `.\venv\Scripts\Activate.ps1` |
| Desactivar | `deactivate` | `deactivate` |
| Copiar el .env | `cp env.example.txt .env` | `copy env.example.txt .env` |

> En Windows, si PowerShell bloquea el script de activación:
> `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`

---

## Instalación

```bash
pip install django mysqlclient djangorestframework python-dotenv
```

```bash
pip install -r requirements.txt
```

```bash
pip freeze > requirements.txt
```

---

## Crear el proyecto desde cero

```bash
django-admin startproject monitoreo_red .
```

```bash
python manage.py startapp dispositivos
```

> El punto final del primer comando evita una carpeta repetida.
> Después hay que registrar la app en `INSTALLED_APPS`.

---

## El día a día

```bash
python manage.py runserver
```

```bash
python manage.py makemigrations
```

```bash
python manage.py migrate
```

```bash
python manage.py createsuperuser
```

```bash
python manage.py shell
```

```bash
python manage.py cargar_datos --limpiar
```

---

## Ver el SQL que Django genera

```bash
python manage.py sqlmigrate dispositivos 0001
```

Este comando es oro en clase: muestra el `CREATE TABLE` real
que sale de las clases de `models.py`.

---

## MySQL

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS monitoreo_red CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

```bash
mysql -u root -p -e "USE monitoreo_red; SHOW TABLES;"
```

```bash
mysql -u root -p -e "USE monitoreo_red; DESCRIBE dispositivos_dispositivo;"
```

> Django nombra las tablas `<app>_<modelo>` en minúsculas.

---

## El ORM en el shell

```python
from dispositivos.models import Dispositivo, Red, Monitoreo

Dispositivo.objects.all()
Dispositivo.objects.count()
Dispositivo.objects.first()

Dispositivo.objects.get(pk=1)
Dispositivo.objects.filter(activo=True)
Dispositivo.objects.filter(nombre__icontains="router")
Dispositivo.objects.exclude(activo=True)
Dispositivo.objects.order_by("-creado_en")

d = Dispositivo.objects.create(
    nombre="Nuevo", direccion_ip="10.0.0.1", red=Red.objects.first()
)

d.nombre = "Cambiado"
d.save()

d.delete()

# Navegar relaciones
d.red.nombre            # hacia adelante
d.monitoreos.all()      # hacia atrás (related_name)
red.dispositivos.all()

# Agregaciones
from django.db.models import Avg, Count, Sum
Monitoreo.objects.aggregate(Avg("latencia_ms"))
Red.objects.annotate(cantidad=Count("dispositivos"))

# Ver el SQL de cualquier consulta
print(Dispositivo.objects.filter(activo=True).query)
```

---

## Filtros del ORM que más se usan

| Filtro | Significa |
|---|---|
| `nombre="Router"` | igual exacto |
| `nombre__iexact="router"` | igual, sin importar mayúsculas |
| `nombre__contains="Rou"` | contiene |
| `nombre__icontains="rou"` | contiene, sin mayúsculas |
| `nombre__startswith="Ro"` | empieza con |
| `errores__gt=2` | mayor que |
| `errores__gte=2` | mayor o igual |
| `errores__lt=2` | menor que |
| `latencia_ms__isnull=True` | es NULL |
| `red__nombre="Red administrativa"` | filtra por la tabla relacionada |

---

## La API

| Método y URL | Qué hace |
|---|---|
| `GET /api/dispositivos/` | Lista |
| `POST /api/dispositivos/` | Crea |
| `GET /api/dispositivos/1/` | Ve uno |
| `PUT /api/dispositivos/1/` | Actualiza |
| `DELETE /api/dispositivos/1/` | Borra |
| `GET /api/dispositivos/?buscar=router` | Filtra |
| `GET /api/dispositivos/criticos/` | Ruta propia |
| `POST /api/dispositivos/1/alertar/` | Ruta propia con id |

Probar desde la terminal:

```bash
curl http://127.0.0.1:8000/api/dispositivos/
```

O simplemente abrir esa URL en el navegador: DRF trae una interfaz visual.

---

## Etiquetas de plantilla más usadas

```django
{% extends "base.html" %}
{% block contenido %} ... {% endblock %}

{% load static %}
<link rel="stylesheet" href="{% static 'dispositivos/estilos.css' %}">

{% url 'dispositivos:detalle' dispositivo.pk %}

{% for d in dispositivos %} ... {% empty %} ... {% endfor %}
{% if d.activo %} ... {% else %} ... {% endif %}

{% csrf_token %}

{{ valor|floatformat:2 }}
{{ valor|default:"sin datos" }}
{{ fecha|date:"d/m/Y H:i" }}
{{ texto|upper }}
{{ lista|length }}
```

Regla: `{{ }}` **muestra** un valor, `{% %}` **ejecuta** lógica.
