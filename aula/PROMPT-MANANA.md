# PROMPT — Continuación Pestalozzi (día 2)

## ESTADO REAL AL CERRAR EL DÍA 1

Proyecto: `/Users/mariopazmino/Desktop/moodle-pestalozzi/aula`
Stack: Laravel 13.30 · Filament 5.7.8 · Livewire 4.4.4 · Tailwind v4 · MySQL
**Ya NO hay React ni Inertia.** El portal de familias es Blade.

Usuarios de prueba (contraseña `password`):
`admin@pestalozzi.test` · `guia@pestalozzi.test` (Teffy Haro, guía) · `familia@pestalozzi.test`

### Hecho y verificado

- Panel Filament en `/admin` funcionando (CRUD completo en los 6 resources:
  `DeleteAction` + `DeleteBulkAction` + filtros + acción de publicar).
- React/Inertia/Breeze eliminados. Controllers de `MiEscuelita` migrados de
  `Inertia::render(...)` a `view(...)`.
- 6 vistas Blade en `resources/views/mi-escuelita/`: home, experiencias-index,
  experiencias-show, mis-experiencias, notificaciones, privacidad.
- 65 tests / 177 assertions en verde.
- Endpoints sanos: `/` 200 · `/login` 200 · `/admin/login` 200 · `/privacidad` 200
  · `/mi-escuelita` 302 · `/admin` 302.

### Pendiente (esto es el trabajo de hoy)

- Estructura de aulas: **no se tocó**. La DB tiene 1 ambiente "Inicial 1 (3-6)"
  y 1 niño. No existe la columna `areas.is_required`.
- Panel en español: **no se tocó**. `config('app.locale')` sigue en `en` y
  ninguno de los 6 resources define labels.

---

## PASO 0 — ASEGURAR EL TRABAJO (hacer primero, antes que nada)

Hay un changeset grande **sin commitear**: 19+ archivos modificados,
`app/Http/Middleware/HandleInertiaRequests.php` borrado, `composer.json` y
`package.json` tocados.

1. Revisar `git status` y `git diff` completos.
2. Commitear la migración React → Blade con un mensaje convencional, por ejemplo:
   `refactor: eliminar React/Inertia y migrar el portal de familias a Blade`
3. Limpiar restos: `composer.json` todavía requiere `tightenco/ziggy` y
   `laravel/breeze`, que ya no se usan. Quitarlos, correr `composer update`
   de esos paquetes y verificar que los 65 tests sigan verdes.
4. **No hacer `git push`.** Mostrar el resultado en local para revisión.

---

## PASO 1 — ESTRUCTURA REAL DE AULAS

### Modelo de negocio

| Aula | Edades | Materias | Contenido | Calificaciones |
|---|---|---|---|---|
| **Mentes Absorbentes** | 3 a 6 años | Obligatorias: Lectura, Matemática, Inglés | Carga de contenido | **No hay** |
| **Mentes Razonadoras** | 6 a 9 años | Obligatorias las mismas 3 + materias abiertas | Solo lectura y ejercicios | **No hay** |

**15 alumnos por aula, 30 en total. No existen calificaciones en ningún lado:**
no crear modelos, campos ni pantallas de notas.

### Tareas

1. **Migración nueva** (no editar las ya corridas): agregar a `areas` la columna
   booleana `is_required` con default `false`.

2. **Seeder de estructura real.** Reescribir `database/seeders/DatabaseSeeder.php`:
   - 2 environments:
     - `Mentes Absorbentes` — `age_range` = "3 a 6 años"
     - `Mentes Razonadoras` — `age_range` = "6 a 9 años"
   - Areas obligatorias: Lectura, Matemática, Inglés (`is_required = true`)
   - Areas abiertas de ejemplo (`is_required = false`)
   - 15 children por environment (30 en total), cada uno con su Family
   - Contenido de ejemplo publicado en ambas aulas, tipos `reading` y `task`

   `Environment.age_range` **ya existe** como columna string. No hace falta
   migración para eso.

3. **Restricción de tipos por aula.** En `ContentResource`, cuando el ambiente
   sea Mentes Razonadoras, el campo `type` debe ofrecer solo `reading` y `task`.
   Reactivo en el formulario **y validado del lado del servidor**.

   `app/Models/Content.php` ya define `TYPE_READING` y `TYPE_TASK` (además de
   EXPERIENCE, VIDEO, DOCUMENT, GALLERY, ANNOUNCEMENT) y los estados
   DRAFT / SCHEDULED / PUBLISHED / ARCHIVED. **Reusarlos, no inventar tipos.**

4. Buscar en el repo `grade`, `calificacion`, `nota`, `score` y quitar lo que aplique.

---

## PASO 2 — PANEL EN ESPAÑOL

Son **dos causas independientes**. Arreglar una sola deja el trabajo a medias.

### Causa 1 — locale

`config('app.locale')` es `en`. Filament ya trae el español instalado en
`vendor/filament/*/resources/lang/es/`, solo hay que activarlo.

Poner `APP_LOCALE=es` en `.env` y `.env.example`, y cambiar el default en
`config/app.php` de `'en'` a `'es'`. Dejar `APP_FALLBACK_LOCALE=es`.

Traduce: Search, Per page, New, Sign out, breadcrumbs (List/Create/Edit),
validaciones y notificaciones.

### Causa 2 — labels de resources

Ninguno define labels, así que Filament los deriva del nombre de la clase
(`ChildResource` → "children"). Agregar a cada uno:

```php
protected static ?string $modelLabel
protected static ?string $pluralModelLabel
protected static ?string $navigationLabel   // = el plural
```

| Resource | Singular | Plural |
|---|---|---|
| ChildResource | Niño | Niños |
| FamilyResource | Familia | Familias |
| UserResource | Usuario | Usuarios |
| EnvironmentResource | Ambiente | Ambientes |
| AreaResource | Área | Áreas |
| ContentResource | Contenido | Contenidos |

Respetar las tildes. **No tocar `$navigationGroup`**: ya dice "Gestión de
Familias" y "Gestión Académica", están bien.

### Causa 3 — labels sueltos

Recorrer los 6 resources y traducir cualquier `->label()` que siga en inglés,
más los títulos de `Section::make()` y los textos de acciones y filtros.

---

## VERIFICACIÓN (obligatoria, con evidencia real)

Los tests verdes **no alcanzan**: ya pasó en este proyecto que los 65 tests
pasaban mientras el formulario de crear contenido tiraba 500.

1. `php artisan optimize:clear`
2. `php artisan migrate:fresh --seed`
3. `php artisan test` → 65 en verde (ajustar los que dependan del seeder viejo,
   sin bajar cobertura)
4. Levantar el servidor y golpear los endpoints:
   `/` · `/login` · `/admin/login` · `/privacidad` · `/mi-escuelita` · `/admin`
5. Abrir `/admin` en **ventana privada**. El caché de Firefox muestra renders
   muertos y hace parecer que el panel está roto. Entrar como
   `guia@pestalozzi.test` / `password`.
6. Confirmar en pantalla:
   - Menú en español: Niños · Ambientes · Contenidos
   - Los 2 ambientes con su rango de edad
   - 30 niños repartidos 15 y 15
   - Crear un contenido tipo lectura en Mentes Razonadoras y publicarlo
   - Editarlo y eliminarlo (CRUD completo)
7. Entrar como `familia@pestalozzi.test` a `/mi-escuelita/experiencias` y
   confirmar que **el contenido publicado en el paso 6 aparece ahí**.

   Ese circuito ya funciona: `ExperienceController` consulta
   `Content::published()` filtrando por el ambiente del niño. No hay que
   construirlo, solo verificarlo.

---

## RESTRICCIONES

- Identificadores, clases y tablas en inglés. Copy de UI y comentarios en
  español (convención del repo).
- **No hacer `git push`.** Mostrar el resultado en local primero.
- Si aparece la necesidad de tocar algo fuera de este alcance, **preguntar antes**.
