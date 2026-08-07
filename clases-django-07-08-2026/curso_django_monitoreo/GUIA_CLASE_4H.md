# Guion de clase — Django en 4 horas

Para proyectar en pantalla. Cada bloque tiene: **qué decir**, **qué escribir** y
**qué mostrar**.

| Bloque | Tema | Minutos |
|---|---|---|
| 1 | Qué es Django y primer proyecto | 45 |
| 2 | Modelos, migraciones y admin | 55 |
| — | *Descanso* | 10 |
| 3 | Vistas, templates, static y CRUD | 55 |
| 4 | API REST con Django REST Framework | 45 |
| 5 | Cierre: decoradores y correos | 20 |

Total: **3 h 50**, dejando 10 minutos de colchón para los problemas de
instalación, que siempre aparecen.

> **Si vas retrasado, corta el bloque 5.** Decoradores y correos son lo
> prescindible. El CRUD y la API no.

---

# Bloque 1 — Qué es Django y primer proyecto (45 min)

## 1.1 La idea, sin código (10 min)

Pregunta para abrir: *"¿Qué pasa cuando escribes una dirección en el navegador
y aparece una página?"*

Dibuja esto en la pizarra:

```text
Navegador  ->  URL  ->  Vista  ->  Modelo  ->  Base de datos
                          |
                       Template
                          |
                       Navegador
```

Django se llama **MTV**:

| Letra | Nombre | En cristiano |
|---|---|---|
| M | Model | Los datos y sus tablas |
| T | Template | Lo que el usuario ve |
| V | View | La lógica que une las dos cosas |

Frase para que se les quede:

> **La URL decide a qué vista ir. La vista busca los datos en el modelo y se
> los entrega al template. El template los pinta.**

Lo que Django te regala hecho: panel de administración, seguridad,
formularios, conexión a la base de datos y migraciones.

## 1.2 Empresas que lo usan (3 min)

Sirve para que no lo vean como un juguete:

- **Instagram** — han publicado charlas de cómo lo usan a gran escala
- **Eventbrite** — su equipo de ingeniería publicó cómo lo usan en producción
- **Mozilla**, **Pinterest**, **Disqus**, **The Washington Post**

## 1.3 Entorno e instalación (12 min)

**Mac**

```bash
python3 -m venv venv && source venv/bin/activate
```

**Windows**

```powershell
python -m venv venv ; .\venv\Scripts\Activate.ps1
```

```bash
pip install django mysqlclient djangorestframework python-dotenv
```

Aclara la trampa: el driver es **mysqlclient**, no `mysql-connector-python`.
Ese último sirvió en el día 8 con SQL a mano, pero Django exige el otro.

## 1.4 Crear el proyecto (10 min)

```bash
django-admin startproject monitoreo_red .
```

```bash
python manage.py startapp dispositivos
```

Explica la diferencia con una analogía:

> El **proyecto** es el edificio: la instalación eléctrica, el agua, la puerta
> principal. La **app** es un departamento: tiene su función propia.
> Un edificio puede tener muchos departamentos.

Muestra qué apareció y para qué sirve cada archivo:

| Archivo | Para qué |
|---|---|
| `manage.py` | El comando con el que se hace todo |
| `settings.py` | La configuración: base de datos, apps, idioma |
| `urls.py` | El mapa de direcciones |
| `models.py` | Las tablas |
| `views.py` | La lógica |

**Paso que todos olvidan:** registrar la app.

```python
INSTALLED_APPS = [
    ...
    "dispositivos",
]
```

Si no está ahí, Django ignora sus modelos y nada funciona.

## 1.5 Conectar a MySQL (10 min)

En `settings.py`:

```python
DATABASES = {
    "default": {
        "ENGINE": "django.db.backends.mysql",
        "NAME": os.getenv("DB_NAME"),
        "USER": os.getenv("DB_USER"),
        "PASSWORD": os.getenv("DB_PASSWORD"),
        "HOST": os.getenv("DB_HOST"),
        "PORT": os.getenv("DB_PORT"),
    }
}
```

Las credenciales van en el `.env`, nunca en el código. Es la misma regla que
ya está en el README del curso.

Crea la base:

```bash
mysql -u root -p -e "CREATE DATABASE monitoreo_red CHARACTER SET utf8mb4;"
```

Y arranca:

```bash
python manage.py migrate
```

```bash
python manage.py runserver
```

Abre <http://127.0.0.1:8000/>. Sale el cohete de Django. **Momento de aplauso.**

---

# Bloque 2 — Modelos, migraciones y admin (55 min)

## 2.1 De CREATE TABLE a clase (15 min)

Pon lado a lado lo del día 8 y lo de hoy.

**Antes (día 8, SQLite a mano):**

```sql
CREATE TABLE IF NOT EXISTS dispositivos (
    id_dispositivo INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    direccion_ip TEXT UNIQUE NOT NULL
)
```

**Ahora (Django):**

```python
class Dispositivo(models.Model):
    nombre = models.CharField(max_length=100)
    direccion_ip = models.GenericIPAddressField(unique=True)
    red = models.ForeignKey(Red, on_delete=models.CASCADE,
                            related_name="dispositivos")
```

La tabla de traducción:

| Base de datos | Django |
|---|---|
| tabla | clase |
| columna | atributo |
| fila | objeto |
| `INTEGER PRIMARY KEY` | Django lo pone solo (`id`) |
| `FOREIGN KEY` | `ForeignKey` |

Dos cosas que hay que explicar sí o sí:

- **`on_delete=models.CASCADE`**: si borro la red, se borran sus dispositivos.
  Django te *obliga* a decidirlo. No hay opción por defecto, y es a propósito.
- **`related_name="dispositivos"`**: es el nombre para ir al revés.
  Permite `red.dispositivos.all()`.

## 2.2 Herencia de modelos (15 min)

Problema: `Red` y `Dispositivo` repiten `creado_en` y `activo`.

**Herencia abstracta** — la solución:

```python
class RegistroBase(models.Model):
    creado_en = models.DateTimeField(auto_now_add=True)
    activo = models.BooleanField(default=True)

    class Meta:
        abstract = True      # <- sin esto Django crea una tabla inútil


class Red(RegistroBase):
    nombre = models.CharField(max_length=100)
```

Recalca: con `abstract = True` **no se crea tabla** para `RegistroBase`.
Solo se copian los campos a las hijas.

**Herencia proxy** — misma tabla, métodos extra:

```python
class DispositivoCritico(Dispositivo):
    class Meta:
        proxy = True         # <- tampoco crea tabla

    def necesita_revision(self):
        return self.total_errores() > 2
```

Sirve para añadir comportamiento sin tocar el modelo original.

> Existe un tercer tipo, **multi tabla**, que sí crea dos tablas unidas 1 a 1.
> Menciónalo y sigue: en 4 horas no da tiempo.

## 2.3 Migraciones (10 min)

```bash
python manage.py makemigrations
```

```bash
python manage.py migrate
```

La explicación que funciona:

> `makemigrations` **escribe la receta**. `migrate` **la cocina**.
> La receta es un archivo de Python que se sube a GitHub, para que
> tu compañero tenga exactamente las mismas tablas.

Y ahora el comando estrella:

```bash
python manage.py sqlmigrate dispositivos 0001
```

Muestra el `CREATE TABLE` real que Django generó. **Aquí cae la ficha**
de qué es un ORM.

Compruébalo en MySQL:

```bash
mysql -u root -p -e "USE monitoreo_red; SHOW TABLES;"
```

Las tablas se llaman `dispositivos_dispositivo`: `<app>_<modelo>`.

## 2.4 El panel de administración (15 min)

```bash
python manage.py createsuperuser
```

En `admin.py`:

```python
@admin.register(Dispositivo)
class DispositivoAdmin(admin.ModelAdmin):
    list_display = ["nombre", "direccion_ip", "red", "activo"]
    list_filter = ["activo", "red"]
    search_fields = ["nombre", "direccion_ip"]
```

Entra a <http://127.0.0.1:8000/admin/>.

**Este es el momento más impresionante de la clase.** Con 4 líneas hay un CRUD
completo, con buscador, filtros y validaciones. Déjalos jugar 5 minutos.

---

# Bloque 3 — Vistas, templates, static y CRUD (55 min)

## 3.1 URL, vista, template (12 min)

El recorrido completo, en tres archivos:

```python
# dispositivos/urls.py
path("", views.lista, name="lista")
```

```python
# dispositivos/views.py
def lista(request):
    dispositivos = Dispositivo.objects.all()
    return render(request, "dispositivos/lista.html",
                  {"dispositivos": dispositivos})
```

```django
{# dispositivos/templates/dispositivos/lista.html #}
{% for d in dispositivos %}
    <li>{{ d.nombre }}</li>
{% endfor %}
```

`render` recibe siempre lo mismo: **petición, plantilla, contexto**.
El contexto es un diccionario: sus llaves son las variables del template.

## 3.2 Templates: herencia y etiquetas (13 min)

`base.html` es la plantilla madre:

```django
<body>
    {% block contenido %}{% endblock %}
</body>
```

Las hijas la rellenan:

```django
{% extends "base.html" %}

{% block contenido %}
    <h1>Dispositivos</h1>
{% endblock %}
```

> Es lo mismo que la herencia de modelos, pero para HTML: escribes la cabecera
> y el menú **una sola vez**.

La regla de oro:

- `{{ variable }}` → **muestra** un valor
- `{% etiqueta %}` → **ejecuta** lógica

Detalles que confunden:

- El `for` de Django **no lleva dos puntos** y **sí lleva** `{% endfor %}`
- `{% empty %}` se muestra cuando la lista está vacía
- Los filtros se encadenan con `|`: `{{ promedio|floatformat:2|default:"—" }}`

## 3.3 Archivos estáticos (10 min)

Estático = todo lo que no cambia: CSS, imágenes, JavaScript.

**La ruta se repite a propósito:**

```text
dispositivos/static/dispositivos/estilos.css
             ^^^^^^ ^^^^^^^^^^^^
             carpeta  nombre de la app otra vez
```

¿Por qué? Porque Django junta los `static` de **todas** las apps en un solo
espacio. Si dos apps tuvieran `estilos.css` en la raíz, se pisarían.
La subcarpeta con el nombre de la app evita el choque. Es la misma razón por la
que los templates van en `templates/dispositivos/`.

En la plantilla:

```django
{% load static %}
<link rel="stylesheet" href="{% static 'dispositivos/estilos.css' %}">
```

**Nunca escribas `/static/...` a mano.** En producción los archivos cambian de
sitio y de nombre; `{% static %}` calcula la URL correcta sola.

Demostración en vivo: cambia un color en el CSS, recarga, se ve el cambio.

## 3.4 El CRUD completo (20 min)

CRUD = **C**rear, **R**ead (leer), **U**pdate (actualizar), **D**elete (borrar).

El truco que hay que entender: **una sola vista atiende el GET y el POST**.

```python
def crear(request):
    if request.method == "POST":
        formulario = DispositivoForm(request.POST)
        if formulario.is_valid():
            dispositivo = formulario.save()
            return redirect("dispositivos:detalle", pk=dispositivo.pk)
    else:
        formulario = DispositivoForm()

    return render(request, "dispositivos/formulario.html",
                  {"formulario": formulario})
```

> GET → te muestro el formulario vacío.
> POST → me mandaste datos, los valido y los guardo.

Editar es **idéntico**, con una sola palabra de diferencia:

```python
formulario = DispositivoForm(request.POST, instance=dispositivo)
```

`instance=` es lo que convierte un "crear" en un "editar".

Tres reglas para cerrar el bloque:

1. **`{% csrf_token %}` es obligatorio** en todo formulario POST. Sin él,
   error 403. Protege contra que otra web envíe formularios en tu nombre.
2. **Después de un POST exitoso, siempre `redirect`.** Si devuelves `render`,
   al recargar la página el navegador reenvía el formulario y se duplica.
3. **`get_object_or_404`** en vez de `.get()`: devuelve un 404 limpio en lugar
   de reventar con una excepción.

---

# Bloque 4 — API REST con Django REST Framework (45 min)

## 4.1 Qué es una API y para qué (8 min)

> Hasta ahora devolvimos **HTML**, para que lo lea una persona.
> Una API devuelve **JSON**, para que lo lea otro programa:
> una app de celular, un tablero, otro sistema.

Misma base de datos, misma lógica, distinta forma de entregar.

## 4.2 Instalar y registrar (5 min)

```bash
pip install djangorestframework
```

```python
INSTALLED_APPS = [
    ...
    "rest_framework",
]
```

## 4.3 Serializer (12 min)

> Un **serializer** es un traductor de dos vías:
> objeto de Python → JSON, y JSON → objeto validado.
> Es a la API lo que el formulario es a la página web.

```python
class DispositivoSerializer(serializers.ModelSerializer):
    red_nombre = serializers.CharField(source="red.nombre", read_only=True)

    class Meta:
        model = Dispositivo
        fields = ["id", "nombre", "direccion_ip", "red", "red_nombre"]
```

`ModelSerializer` los deduce del modelo, igual que `ModelForm`.

## 4.4 ViewSet y router (12 min)

```python
class DispositivoViewSet(viewsets.ModelViewSet):
    queryset = Dispositivo.objects.all()
    serializer_class = DispositivoSerializer
```

```python
router = DefaultRouter()
router.register("dispositivos", DispositivoViewSet)

urlpatterns = [path("api/", include(router.urls))]
```

**Con esas líneas ya existen las cinco operaciones:**

| Método | URL | Hace |
|---|---|---|
| GET | `/api/dispositivos/` | listar |
| POST | `/api/dispositivos/` | crear |
| GET | `/api/dispositivos/1/` | ver uno |
| PUT | `/api/dispositivos/1/` | actualizar |
| DELETE | `/api/dispositivos/1/` | borrar |

No escribimos ninguna: se heredan de `ModelViewSet`.

## 4.5 Probarla (8 min)

Abre <http://127.0.0.1:8000/api/dispositivos/> en el navegador.
DRF trae una **interfaz visual navegable**: se puede crear y borrar desde ahí.
Es lo que más les va a gustar.

Rutas propias con `@action`:

```python
@action(detail=False, methods=["get"])
def criticos(self, request):
    ...
```

`detail=False` → `/api/dispositivos/criticos/`
`detail=True` → `/api/dispositivos/1/alertar/`

Aviso honesto para cerrar: en la clase pusimos `AllowAny` para poder probar
rápido. **En producción eso jamás.** Ahí van permisos y autenticación.

---

# Bloque 5 — Cierre: decoradores y correos (20 min)

Si el tiempo aprieta, este bloque se cuenta sin escribir código.

## 5.1 Decoradores (10 min)

Problema: la misma validación repetida en 5 vistas.

```python
def confirmar_borrado(funcion):
    @wraps(funcion)
    def envoltura(request, *args, **kwargs):
        if request.method != "POST":
            return redirect("dispositivos:lista")
        return funcion(request, *args, **kwargs)
    return envoltura
```

```python
@confirmar_borrado
def borrar(request, pk):
    ...
```

> Un decorador es un **portero**: envuelve la función y decide si la deja pasar.
> La validación se escribe una vez y se usa en todas partes.

## 5.2 Correos (10 min)

```python
EMAIL_BACKEND = "django.core.mail.backends.console.EmailBackend"
```

Con eso, el correo **se imprime en la terminal**: se ve el resultado sin
servidor SMTP, sin contraseñas y sin internet. Perfecto para clase.

```python
send_mail("Asunto", "Cuerpo", remitente, ["destino@correo.com"])
```

Con HTML y plantilla:

```python
plantilla = get_template("dispositivos/correo_alerta.html")
contenido = plantilla.render({"dispositivo": dispositivo})

mensaje = EmailMultiAlternatives(asunto, cuerpo, remitente, [destino])
mensaje.attach_alternative(contenido, "text/html")
mensaje.send()
```

Fíjate: **el mismo `get_template` de los templates web**. Es la misma
herramienta, usada para generar el HTML de un correo.

Pulsa el botón "Enviar alerta" en el detalle y muestra el correo apareciendo
en la terminal donde corre `runserver`. Buen cierre visual.

---

# Cierre en cinco frases

1. La URL manda a una vista; la vista busca en el modelo y pinta un template.
2. Una clase es una tabla; `makemigrations` escribe la receta y `migrate` la cocina.
3. El admin te da un CRUD completo casi gratis.
4. `{% static %}` siempre; nunca la ruta a mano.
5. La misma información se sirve como HTML para personas y como JSON para programas.

Luego pasa a [EJERCICIOS.md](EJERCICIOS.md).
