# Ejercicios

Fáciles y en orden. Cada uno se resuelve tocando uno o dos archivos.
Las soluciones están al final.

---

## Bloque 1 — Conexión y proyecto

**1.1** Cambia `DB_NAME` en el `.env` a `base_que_no_existe` y ejecuta
`python manage.py migrate`. Lee el error. Luego devuélvelo a `monitoreo_red`.

**1.2** Pon una contraseña incorrecta en el `.env` y ejecuta `migrate`.
¿El error es el mismo que el anterior? ¿En qué se diferencian?

**1.3** Quita `"dispositivos"` de `INSTALLED_APPS` y ejecuta
`python manage.py makemigrations`. ¿Qué responde? Vuelve a ponerlo.

**1.4** Ejecuta `python manage.py sqlmigrate dispositivos 0001` y busca en la
salida el `CREATE TABLE` de la tabla de dispositivos.

---

## Bloque 2 — Modelos

**2.1** Agrega al modelo `Dispositivo` un campo `marca` de texto, máximo 50
caracteres, que pueda quedar vacío. Genera y aplica la migración.

> Pista: para que pueda quedar vacío en texto se usa `blank=True`.
> `null=True` es para la base de datos; `blank=True` es para los formularios.

**2.2** Agrega al modelo `Monitoreo` un campo `observacion` de texto largo
opcional.

**2.3** Haz que los dispositivos se ordenen por dirección IP en vez de por
nombre.

**2.4** Agrega un método `esta_lento()` al modelo `Dispositivo` que devuelva
`True` si su latencia promedio pasa de 40 ms. Úsalo en el template de detalle.

---

## Bloque 3 — ORM en el shell

Abre `python manage.py shell` y resuelve:

**3.1** Cuenta cuántos dispositivos hay.

**3.2** Trae solo los dispositivos de la "Red administrativa".

**3.3** Trae los dispositivos cuyo nombre contenga "servidor", sin importar
mayúsculas.

**3.4** Trae los monitoreos con más de 1 error.

**3.5** Calcula el promedio de latencia de TODOS los monitoreos.

**3.6** Cuenta cuántos dispositivos tiene cada red.

**3.7** Imprime el SQL que genera la consulta del ejercicio 3.2.

---

## Bloque 4 — CRUD y templates

**4.1** En la lista, muestra también la fecha de creación de cada dispositivo,
con formato `dd/mm/aaaa`.

**4.2** Haz que la fila se vea distinta cuando el dispositivo está inactivo
(por ejemplo, en gris).

**4.3** Agrega al buscador la opción de buscar también por dirección IP, no
solo por nombre.

**4.4** Crea el CRUD de **Redes**: lista, alta, edición y borrado.
Ya tienes `RedForm` hecho en `forms.py`.

**4.5** Agrega un botón "Duplicar" que cree una copia del dispositivo con el
nombre "(copia)" al final.

---

## Bloque 5 — Static

**5.1** Cambia el color azul de la cabecera por uno verde. Recarga y compruébalo.

**5.2** Agrega un archivo `dispositivos/static/dispositivos/logo.svg` y
muéstralo en la cabecera con `{% static %}`.

**5.3** Escribe la ruta del CSS a mano (`/static/dispositivos/estilos.css`) en
vez de usar `{% static %}`. ¿Funciona? ¿Por qué es mala idea igual?

---

## Bloque 6 — API

**6.1** Abre `/api/dispositivos/` en el navegador y crea un dispositivo desde
el formulario que trae DRF.

**6.2** Agrega el campo `creado_en` al `MonitoreoSerializer`.

**6.3** Crea un `@action` llamado `inactivos` que devuelva solo los
dispositivos con `activo=False`.

**6.4** Haz que la API se pueda filtrar por red: `/api/dispositivos/?red=1`.

**6.5** Cambia el tamaño de página de la API de 10 a 5.

---

# Soluciones

### 1.1 y 1.2

Con base inexistente: `Unknown database 'base_que_no_existe'`.
Con contraseña mala: `Access denied for user 'root'@'localhost'`.

La diferencia importa: el primero dice que **la conexión funcionó** pero la base
no existe. El segundo dice que **ni siquiera pudo entrar**.

### 1.3

`No installed app with label 'dispositivos'`. Sin registrar la app en
`INSTALLED_APPS`, Django ni la mira.

### 2.1 y 2.2

```python
class Dispositivo(RegistroBase):
    ...
    marca = models.CharField(max_length=50, blank=True)


class Monitoreo(models.Model):
    ...
    observacion = models.TextField(blank=True)
```

```bash
python manage.py makemigrations
python manage.py migrate
```

### 2.3

```python
class Meta:
    ordering = ["direccion_ip"]
```

### 2.4

```python
def esta_lento(self):
    promedio = self.promedio_latencia()
    return promedio is not None and promedio > 40
```

```django
{% if dispositivo.esta_lento %}<span class="pastilla">Lento</span>{% endif %}
```

Ojo: en los templates los métodos se llaman **sin paréntesis**.

### 3.1 a 3.7

```python
from dispositivos.models import Dispositivo, Monitoreo, Red
from django.db.models import Avg, Count

Dispositivo.objects.count()

Dispositivo.objects.filter(red__nombre="Red administrativa")

Dispositivo.objects.filter(nombre__icontains="servidor")

Monitoreo.objects.filter(errores__gt=1)

Monitoreo.objects.aggregate(Avg("latencia_ms"))

Red.objects.annotate(cantidad=Count("dispositivos")).values("nombre", "cantidad")

print(Dispositivo.objects.filter(red__nombre="Red administrativa").query)
```

### 4.1

```django
<td>{{ dispositivo.creado_en|date:"d/m/Y" }}</td>
```

### 4.2

```django
<tr class="{% if not dispositivo.activo %}fila-inactiva{% endif %}">
```

```css
.fila-inactiva { opacity: .5; }
```

### 4.3

```python
from django.db.models import Q

if busqueda:
    dispositivos = dispositivos.filter(
        Q(nombre__icontains=busqueda) | Q(direccion_ip__icontains=busqueda)
    )
```

`Q` permite combinar condiciones. La barra `|` es un **O**, el `&` es un **Y**.

### 4.4

Copia las vistas de dispositivos cambiando el modelo y el formulario:

```python
def redes_lista(request):
    return render(request, "dispositivos/redes_lista.html",
                  {"redes": Red.objects.all()})


def redes_crear(request):
    if request.method == "POST":
        formulario = RedForm(request.POST)
        if formulario.is_valid():
            formulario.save()
            return redirect("dispositivos:redes_lista")
    else:
        formulario = RedForm()
    return render(request, "dispositivos/formulario.html",
                  {"formulario": formulario, "titulo": "Nueva red"})
```

Y agrega las rutas en `urls.py`. Reutiliza `formulario.html`: sirve igual.

### 4.5

```python
def duplicar(request, pk):
    original = get_object_or_404(Dispositivo, pk=pk)

    Dispositivo.objects.create(
        nombre=f"{original.nombre} (copia)",
        direccion_ip=f"{original.direccion_ip}1",   # la IP es única
        red=original.red,
    )

    return redirect("dispositivos:lista")
```

Truco conocido para duplicar en Django: poner `original.pk = None` y guardar.
Aquí no sirve directo porque `direccion_ip` es única.

### 5.3

Funciona en desarrollo, pero es mala idea porque en producción se ejecuta
`collectstatic`, los archivos cambian de ubicación y muchas veces se les añade
un código al nombre (`estilos.a3f9c2.css`) para el control de caché.
`{% static %}` calcula el nombre correcto; la ruta escrita a mano se rompe.

### 6.2

```python
fields = [..., "creado_en"]
```

### 6.3

```python
@action(detail=False, methods=["get"])
def inactivos(self, request):
    consulta = self.get_queryset().filter(activo=False)
    return Response(self.get_serializer(consulta, many=True).data)
```

### 6.4

```python
def get_queryset(self):
    consulta = super().get_queryset()

    red = self.request.query_params.get("red")
    if red:
        consulta = consulta.filter(red_id=red)

    return consulta
```

### 6.5

```python
REST_FRAMEWORK = {
    ...
    "PAGE_SIZE": 5,
}
```
