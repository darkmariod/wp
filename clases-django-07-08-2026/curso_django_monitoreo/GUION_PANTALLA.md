# Cómo dar la clase — montaje, orden de diapositivas y comandos

Guion operativo: qué va en cada pantalla, en qué orden pasar las diapositivas
y qué escribes tú en el Mac frente a lo que ellos escriben en Windows.

---

## 1. El montaje

| Pantalla | Qué va ahí | Para quién |
|---|---|---|
| **Tu Mac** | `CHULETA.md`, este archivo y el proyecto corriendo | Solo tú |
| **PC Windows (proyector)** | El `.pptx` y el editor con el código | Todos |

**La regla de oro:** tú lees del Mac, **escribes en el Windows**.
Nunca proyectes tu Mac: los comandos de macOS no les sirven y se confunden.

> Si puedes, ten `runserver` corriendo en el Mac contra tu MySQL. Si algo
> revienta en el Windows en vivo, cambias de pantalla, muestras que funciona y
> sigues explicando sin perder el hilo.

---

## 2. Dos avisos sobre el deck

**a) El orden de las diapositivas NO es el orden de la clase.**
La sección de Django está ordenada según los artículos originales
(correos → templates → herencia → decoradores). Para enseñar hay que saltar.
El orden correcto está en la tabla del punto 4. **Tenlo marcado antes de empezar.**

**b) La diapositiva 65 repite el patrón MTV, que ya diste en la 49.**
Cuando llegues a ella, sáltala o úsala como repaso rápido de 20 segundos.

---

## 3. Antes de la clase (haz esto la noche anterior)

En el **PC de Windows**, con el proyecto ya copiado:

```powershell
python -m venv venv ; .\venv\Scripts\Activate.ps1
```

```powershell
pip install -r requirements.txt
```

Si `mysqlclient` falla (pasa seguido en Windows), aplica el plan B de
[PROBLEMAS.md](PROBLEMAS.md) punto 1 **antes**, no en vivo.

```powershell
copy env.example.txt .env
```

```powershell
python manage.py migrate ; python manage.py cargar_datos ; python manage.py createsuperuser
```

```powershell
python manage.py runserver
```

Si abre <http://127.0.0.1:8000/> y ves la lista de dispositivos, estás listo.

**Deja un usuario del admin ya creado** (por ejemplo `profe` / `profe12345`).
Crear el superusuario en vivo consume 3 minutos y la contraseña no se ve al
escribirla, lo que siempre genera confusión.

---

## 4. El recorrido, bloque por bloque

### Bloque 1 — Qué es Django (45 min) · diapositivas **46 → 51**

| Diapositiva | Qué haces |
|---|---|
| 46 | Separador. "Empezamos Django." |
| 47 | ¿Qué es Django? Pregunta: *"¿qué han programado a mano hasta ahora?"* |
| 48 | Empresas. Responde al *"¿esto aguanta?"*. Menciona Instagram y Eventbrite |
| 49 | MTV. Dibújalo en la pizarra, no solo lo leas |
| 50 | El recorrido de una petición |
| 51 | Proyecto vs app. Usa la analogía del edificio y el departamento |

**Al teclado (Windows):** crear el entorno, instalar, `startproject`, `startapp`,
registrar la app en `INSTALLED_APPS`, configurar MySQL, `migrate`, `runserver`.

Cierra el bloque mostrando el cohete de Django. Es el primer momento de victoria.

---

### Bloque 2 — Modelos, migraciones y admin (55 min) · **52, 53, 72–80, 54**

| Diapositiva | Qué haces |
|---|---|
| 52 | De tabla SQL a clase. **Ponla al lado del `02.crear_tablas.py` del día 8** |
| 53 | Migraciones: la receta y el plato |
| 72 | El problema: campos duplicados |
| 73 | Las tres formas de heredar |
| 74, 75 | Herencia abstracta. Es la que van a usar |
| 76, 77 | Multi tabla. **Cuéntala rápido, no la escribas** |
| 78, 79 | Proxy |
| 80 | Cuál usar en cada caso |
| 54 | El panel de administración |

**Al teclado:** escribir `models.py`, `makemigrations`, `migrate`.

Y el momento clave del bloque:

```bash
python manage.py sqlmigrate dispositivos 0001
```

Muestra el `CREATE TABLE` que Django generó solo. **Aquí cae la ficha de qué es
un ORM.** No te lo saltes por tiempo.

Termina en el admin y déjalos jugar 5 minutos. Es lo que más los engancha.

---

### Bloque 3 — Vistas, templates, static y CRUD (55 min) · **65–69, 55**

| Diapositiva | Qué haces |
|---|---|
| 65 | Repaso de MTV en 20 segundos (ya la diste en la 49) |
| 66 | ¿Qué es un template? |
| 67 | La función `render` y sus tres parámetros |
| 68 | Dónde busca Django los templates |
| 69 | `render` devuelve un `HttpResponse` |
| 55 | Archivos estáticos. Explica por qué la carpeta repite el nombre de la app |
| — | **Salta la 70 y la 71**: `get_template` va en el bloque 5, y las vistas basadas en clases no las usamos |

**Al teclado:** `urls.py`, la vista `lista`, `base.html`, el template hijo, el CSS.

Demostración obligada con `static`: cambia un color del CSS, recarga, se ve.
Simple y convence.

Luego el CRUD completo. Los tres puntos que hay que repetir:

1. Una sola vista atiende GET y POST
2. `{% csrf_token %}` o error 403
3. Después de un POST exitoso, siempre `redirect`

---

### Bloque 4 — API con DRF (45 min) · diapositiva **56**

Solo hay una diapositiva de API, y basta: este bloque es teclado y navegador.

**Al teclado:** `serializers.py`, `api.py`, el router en `urls.py`.

Y luego abre <http://127.0.0.1:8000/api/dispositivos/> en el navegador.
La interfaz navegable de DRF hace sola el trabajo de convencer: se puede crear
y borrar desde ahí. **Este es el segundo gran momento de la clase.**

Cierra con honestidad: en clase pusimos `AllowAny` para probar rápido;
en producción eso jamás.

---

### Bloque 5 — Cierre (20 min) · **81–87, 57–64, 88**

Este bloque se **cuenta**, no se escribe. Es el primero en caerse si vas tarde.

| Diapositiva | Qué haces |
|---|---|
| 83, 84 | Decoradores: el problema y la solución. Salta 81, 82, 85, 86 |
| 87 | Buenas prácticas |
| 57 | Correos: el panorama |
| 60 | `send_mail` |
| 61, 62 | HTML con plantilla. Enlaza con la 70 (`get_template`) |
| 88 | Fuentes y cierre |

Pulsa "Enviar alerta" en el detalle y muestra el correo apareciendo en la
terminal. Buen cierre visual, cuesta 30 segundos.

---

## 5. Mac y Windows, lado a lado

Lo único que cambia de verdad son estas líneas. Todo lo demás
(`python manage.py ...`) es idéntico en los dos sistemas.

| Acción | Tu Mac | El Windows del aula |
|---|---|---|
| Crear entorno | `python3 -m venv venv` | `python -m venv venv` |
| Activar | `source venv/bin/activate` | `.\venv\Scripts\Activate.ps1` |
| Desactivar | `deactivate` | `deactivate` |
| Copiar el .env | `cp env.example.txt .env` | `copy env.example.txt .env` |
| Ver archivos | `ls` | `dir` |
| Borrar carpeta | `rm -rf venv` | `Remove-Item -Recurse venv` |
| Encadenar comandos | `&&` | `;` |
| Python | casi siempre `python3` | casi siempre `python` |

**El clásico de Windows.** Si al activar sale que la ejecución de scripts está
deshabilitada:

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

Tenlo escrito y a la mano: aparece en casi todas las clases.

---

## 6. Lo que NO debes hacer

- **No escribas código leyendo del Mac sin mirar la pantalla.** Los `python3`
  y las rutas con `/` se te van a colar. Ten la tabla del punto 5 abierta.
- **No dejes la instalación para el momento.** Si `mysqlclient` falla en vivo
  se te van 20 de los 240 minutos.
- **No inventes la contraseña del admin en vivo.** Ten el usuario ya creado.
- **No sigas el orden del deck.** Usa la tabla del punto 4.
- **No expliques la herencia multi tabla escribiéndola.** Cuéntala y sigue.

---

## 7. Si vas retrasado, corta en este orden

1. El bloque 5 completo (decoradores y correos)
2. La herencia proxy (diapositivas 78, 79)
3. El reporte con agregaciones
4. La validación propia del formulario

**Nunca cortes:** los modelos, las migraciones, el admin, el CRUD ni la API.
Eso es la clase.
