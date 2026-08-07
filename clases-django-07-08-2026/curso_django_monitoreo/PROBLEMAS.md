# Errores que van a aparecer en clase

Ordenados por probabilidad real. Con el mensaje exacto y la causa.

---

## 1. `mysqlclient` no se instala en Windows

```text
error: Microsoft Visual C++ 14.0 or greater is required
```

**Causa:** pip intenta compilarlo porque no encontró una rueda lista.

**Solución rápida (la que uso en clase):**

```bash
pip install --only-binary :all: mysqlclient
```

**Si aun así falla,** usa el otro driver, que es Python puro:

```bash
pip install pymysql
```

Y en `monitoreo_red/__init__.py` agrega:

```python
import pymysql

pymysql.install_as_MySQLdb()
```

Con esas dos líneas Django cree que está usando `mysqlclient` y todo sigue igual.
**Ten esto preparado antes de la clase**: es el problema número uno.

---

## 2. `No module named 'django'`

**Causa:** el entorno virtual no está activo.

**Mac**

```bash
source venv/bin/activate
```

**Windows**

```powershell
.\venv\Scripts\Activate.ps1
```

El prompt debe mostrar `(venv)`. Si abres una terminal nueva, hay que activar
otra vez: la activación **no se hereda**.

---

## 3. Windows no deja activar el entorno

```text
No se puede cargar el archivo ... Activate.ps1 porque la ejecución de scripts
está deshabilitada en este sistema.
```

**Solución:**

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

Responde `S` y vuelve a activar.

---

## 4. `Unknown database 'monitoreo_red'`

**Causa:** la conexión funcionó, pero la base no existe.

```bash
mysql -u root -p -e "CREATE DATABASE monitoreo_red CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 5. `Access denied for user 'root'@'localhost'`

**Causa:** usuario o contraseña mal en el `.env`.

Compruébalo por fuera de Django:

```bash
mysql -u root -p
```

Si por ahí tampoco entra, el problema es MySQL, no Django. Y revisa que el
`.env` no tenga comillas ni espacios de más:

```text
DB_PASSWORD=miclave        correcto
DB_PASSWORD="miclave"      mal: las comillas se toman como parte del valor
DB_PASSWORD = miclave      mal: los espacios también
```

---

## 6. `No installed app with label 'dispositivos'`

**Causa:** falta registrar la app.

```python
INSTALLED_APPS = [
    ...
    "dispositivos",
]
```

---

## 7. `TemplateDoesNotExist: dispositivos/lista.html`

**Causa:** la ruta del template está mal. Debe ser:

```text
dispositivos/templates/dispositivos/lista.html
             ^^^^^^^^^ ^^^^^^^^^^^^
```

El nombre de la app se repite **a propósito**. Es lo que evita que dos apps
con el mismo nombre de archivo se pisen.

---

## 8. El CSS no se ve

Revisa las tres cosas, en este orden:

1. ¿Está `{% load static %}` **arriba del todo** en la plantilla?
2. ¿La ruta es `dispositivos/static/dispositivos/estilos.css`?
3. ¿Está `"django.contrib.staticfiles"` en `INSTALLED_APPS`?

Y recuerda: `Ctrl+Shift+R` para recargar sin caché. Muchas veces "no se aplica
el CSS" es simplemente el navegador guardando la versión vieja.

---

## 9. `CSRF verification failed. Request aborted.` (error 403)

**Causa:** falta `{% csrf_token %}` dentro del formulario.

```django
<form method="post">
    {% csrf_token %}
    ...
</form>
```

Va **dentro** de la etiqueta `<form>`, no fuera.

---

## 10. `NoReverseMatch: 'detalle' is not a valid view function or pattern name`

**Causa:** falta el espacio de nombres de la app.

```django
{% url 'detalle' d.pk %}                 mal
{% url 'dispositivos:detalle' d.pk %}    bien
```

Porque en `dispositivos/urls.py` está definido `app_name = "dispositivos"`.

---

## 11. Cambié el modelo y no pasa nada

Faltan las migraciones:

```bash
python manage.py makemigrations
python manage.py migrate
```

`makemigrations` escribe la receta, `migrate` la aplica. Hacen falta las dos.

---

## 12. `You are trying to add a non-nullable field without a default`

**Causa:** agregaste un campo obligatorio a una tabla que ya tiene filas.
Django no sabe qué poner en las filas existentes.

**Opciones:**

- Ponle `default=` en el modelo
- O `null=True, blank=True`
- O elige la opción 1 que ofrece la terminal y escribe un valor

---

## 13. `cannot import name 'cc_delim_re' from 'django.utils.cache'`

**Causa real y verificada:** instalaste **Django 6.x**, y Django REST Framework
todavía no es compatible con esa versión.

**Solución:**

```bash
pip install "Django>=5.2,<6.0"
```

Django 5.2 es LTS. Es lo que debe usarse en esta clase.

---

## 14. Los acentos se ven como `Ã±`

**Causa:** la base no se creó con `utf8mb4`.

```sql
ALTER DATABASE monitoreo_red CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 15. `That port is already in use`

Ya hay un `runserver` corriendo. Usa otro puerto:

```bash
python manage.py runserver 8001
```

---

## Comandos de rescate

Volver a cargar los datos de ejemplo desde cero:

```bash
python manage.py cargar_datos --limpiar
```

Empezar la base COMPLETAMENTE de cero (borra todo):

```bash
mysql -u root -p -e "DROP DATABASE monitoreo_red; CREATE DATABASE monitoreo_red CHARACTER SET utf8mb4;"
```

```bash
python manage.py migrate && python manage.py cargar_datos && python manage.py createsuperuser
```

Ver si Django está sano sin levantar nada:

```bash
python manage.py check
```
