# PROMPT — Pestalozzi día 3: avatares, tablas y CRUD completo

## ESTADO REAL VERIFICADO

Proyecto: `/Users/mariopazmino/Desktop/moodle-pestalozzi/aula`
Stack: Laravel 13.30 · Filament 5.7.8 · **Livewire 4.4.4** · Tailwind v4 · MySQL
Sin React ni Inertia. Portal de familias en Blade + componentes Livewire.

Usuarios de prueba (contraseña `password`):
`admin@pestalozzi.test` · `guia@pestalozzi.test` (Teffy Haro, guía) · `familia@pestalozzi.test`

### Funciona y está verificado en navegador

- Panel Filament en `/admin` con CRUD completo en los 6 resources
  (`DeleteAction` + `DeleteBulkAction` + filtros + acción de publicar).
- Portal de familias: login, home, listado de experiencias con filtros por área
  (Livewire, reactivo), detalle de experiencia con formulario de evidencia
  (fotos hasta 5, video, documento, comentario).
- Componentes Livewire: `ExperienciaLista`, `ExperienciaDetalle`, `Historial`.
- 65 tests / 177 assertions en verde.

### Bugs corregidos en el día 2 (ya aplicados, sin commitear)

1. **Menú mobile muerto.** `resources/views/layouts/app.blade.php` usaba
   `<template x-if>` **dentro de un `<svg>`**. El navegador parsea el contenido
   del svg en el namespace SVG, donde `<template>` no existe como elemento, así
   que `template.content` queda `undefined` y Alpine revienta con
   `Cannot read properties of undefined (reading 'cloneNode')`.
   Se reemplazó por un único `<path>` con `:d` bindeado. También se corrigió
   `aria-expanded`, que estaba hardcodeado en `"false"`.

2. **500 en el detalle de experiencia.** `ExperienciaDetalle.php` declaraba
   `getMostrarFormularioProperty()`, la convención de **Livewire 2**, eliminada
   en Livewire 3+. El blade la invocaba como `$mostrarFormulario` y tiraba
   `Undefined variable`. Se migró a `#[Computed] public function mostrarFormulario()`
   y en el blade a `$this->mostrarFormulario`, igual que las otras dos computed
   del mismo archivo. Era el único magic getter viejo del proyecto.

> **Lección:** los 65 tests estaban en verde mientras esa página tiraba 500.
> Verificar SIEMPRE en navegador con sesión real, no solo con la suite.

### Pendiente

- **Estructura de aulas: 0%.** La DB sigue con 1 ambiente "Inicial 1 (3-6)" y
  1 niño. No existe la columna `areas.is_required`.
- **Panel en español: 0%.** `config('app.locale')` sigue en `en` y ninguno de
  los 6 resources define labels.
- **Avatares: no existen.** Ni `users` ni `children` tienen columna de imagen.

---

## PASO 0 — COMMITEAR (hacer primero, es urgente)

Hay **76 archivos sin commitear**, 13 de ellos sin trackear, y **cero commits
nuevos desde `cc79b1e`**. Esto incluye toda la migración a Livewire y los dos
bugfixes de arriba. Si algo se rompe, no hay a dónde volver.

1. Revisar `git status` y `git diff`.
2. Commitear en al menos dos pasos, con mensajes convencionales:
   - `refactor: migrar el portal de familias de React/Inertia a Blade + Livewire`
   - `fix: menú mobile con Alpine fuera del svg y computed property de Livewire 4`
3. Limpiar restos: `composer.json` todavía requiere `tightenco/ziggy` y
   `laravel/breeze`, inútiles tras sacar React. Quitarlos y verificar tests.
4. **No hacer `git push`.** Mostrar en local para revisión.

---

## PASO 1 — FOTOS DE PERFIL

Hoy el selector de niño en el nav muestra solo el nombre ("María") porque no
hay ninguna columna de imagen en la base.

1. **Migración nueva**: agregar `photo_path` (string, nullable) a `children` y
   a `users`.
2. **Filament**: campo `FileUpload` de imagen en `ChildResource` y `UserResource`,
   con recorte a cuadrado, límite de tamaño y disco público.
3. **Portal de familias**: mostrar el avatar del niño en el nav junto al nombre
   y en el home. Si no hay foto, mostrar un avatar con las iniciales sobre
   fondo de color (no un ícono genérico roto).
4. Registrar el enlace simbólico de storage si hace falta: `php artisan storage:link`.

---

## PASO 2 — TABLAS DE DATOS

### Lado familias (Livewire)

En `Historial` (`/mi-escuelita/mis-experiencias`), reemplazar el listado actual
por una tabla con:

- Columnas: experiencia, área, fecha de envío, estado, respuesta de la guía
- Búsqueda por título
- Filtro por área y por estado
- Orden por fecha
- Paginación
- Estado vacío cuidado (ya existe uno bueno en `ExperienciaLista`, seguir ese tono)

Usar Livewire con `WithPagination`. **No** meter una librería JS de DataTables:
el proyecto ya tiene Livewire y Tailwind, sumar otra dependencia es ruido.

### Lado staff (Filament)

Los 6 resources ya tienen CRUD completo. Pulir:

- `ContentResource`: filtros por ambiente, área, tipo y estado. Acción rápida
  de publicar/despublicar desde la fila. Columna con conteo de evidencias recibidas.
- `ChildResource`: filtro por ambiente y por estado. Avatar en la tabla.
- Nuevo `EvidenceResource` (o RelationManager sobre Content): que la guía vea
  las evidencias que subieron las familias, pueda abrirlas y responder.
  **Sin calificaciones** — solo respuesta cualitativa.
- Verificar que cada acción respete las policies existentes en `app/Policies/`.

---

## PASO 3 — ESTRUCTURA REAL DE AULAS

| Aula | Edades | Materias | Contenido | Calificaciones |
|---|---|---|---|---|
| **Mentes Absorbentes** | 3 a 6 años | Obligatorias: Lectura, Matemática, Inglés | Carga de contenido | **No hay** |
| **Mentes Razonadoras** | 6 a 9 años | Obligatorias las mismas 3 + materias abiertas | Solo lectura y ejercicios | **No hay** |

15 alumnos por aula, 30 en total. **No existen calificaciones en ningún lado.**

1. Migración nueva: columna booleana `is_required` en `areas`, default `false`.
2. Reescribir `database/seeders/DatabaseSeeder.php`:
   - 2 environments con `age_range` "3 a 6 años" y "6 a 9 años"
     (la columna `age_range` **ya existe**, no hace falta migración)
   - Areas obligatorias: Lectura, Matemática, Inglés (`is_required = true`)
   - Areas abiertas de ejemplo (`is_required = false`)
   - 15 children por environment, cada uno con su Family y su foto de ejemplo
   - Contenido publicado en ambas aulas, tipos `reading` y `task`
3. En `ContentResource`, si el ambiente es Mentes Razonadoras, el campo `type`
   ofrece solo `reading` y `task`. Reactivo en el form **y validado en servidor**.

`app/Models/Content.php` ya define `TYPE_READING` y `TYPE_TASK` (más EXPERIENCE,
VIDEO, DOCUMENT, GALLERY, ANNOUNCEMENT) y los estados DRAFT / SCHEDULED /
PUBLISHED / ARCHIVED. **Reusarlos, no inventar tipos nuevos.**

---

## PASO 4 — PANEL EN ESPAÑOL

Son **dos causas independientes**; arreglar una sola deja el trabajo a medias.

**Causa 1 — locale.** `config('app.locale')` es `en`. Filament ya trae el
español instalado en `vendor/filament/*/resources/lang/es/`, solo hay que
activarlo: `APP_LOCALE=es` en `.env` y `.env.example`, y cambiar el default en
`config/app.php`. Dejar `APP_FALLBACK_LOCALE=es`.

**Causa 2 — labels.** Ningún resource los define, así que Filament los deriva
del nombre de la clase (`ChildResource` → "children"). Agregar a cada uno
`$modelLabel`, `$pluralModelLabel` y `$navigationLabel`:

| Resource | Singular | Plural |
|---|---|---|
| ChildResource | Niño | Niños |
| FamilyResource | Familia | Familias |
| UserResource | Usuario | Usuarios |
| EnvironmentResource | Ambiente | Ambientes |
| AreaResource | Área | Áreas |
| ContentResource | Contenido | Contenidos |

Respetar tildes. **No tocar `$navigationGroup`**, ya está en español.

---

## VERIFICACIÓN (obligatoria, con evidencia real)

1. `php artisan optimize:clear && php artisan migrate:fresh --seed`
2. `php artisan test` → 65 en verde (ajustar los que dependan del seeder viejo)
3. Golpear endpoints: `/` · `/login` · `/admin/login` · `/privacidad` ·
   `/mi-escuelita` · `/admin`
4. **Abrir la consola del navegador y confirmar CERO errores de JavaScript.**
   Los dos bugs del día 2 solo se veían ahí, no en los tests.
5. Como `familia@pestalozzi.test`:
   - Home muestra el avatar del niño
   - Listar y filtrar experiencias
   - Abrir una experiencia y **subir una evidencia real** (foto + comentario)
   - Ver esa evidencia en "Mis experiencias", en la tabla nueva
   - Probar el menú hamburguesa en ancho mobile (375px)
6. Como `guia@pestalozzi.test` en `/admin` (**ventana privada**: el caché de
   Firefox muestra renders muertos y hace parecer que el panel está roto):
   - Menú en español: Niños · Ambientes · Contenidos
   - Los 2 ambientes con su rango de edad y 30 niños repartidos 15 y 15
   - Crear contenido tipo lectura en Mentes Razonadoras, publicarlo,
     editarlo y eliminarlo
   - Ver la evidencia que subió la familia en el paso 5 y responderla

---

## RESTRICCIONES

- Identificadores, clases y tablas en inglés. Copy de UI y comentarios en
  español rioplatense (es el tono que ya usa el portal: "Contanos", "Subí",
  "Compartí").
- **No sumar librerías JS de tablas.** Livewire + Tailwind alcanzan.
- **No hacer `git push`.** Mostrar en local primero.
- Si aparece la necesidad de tocar algo fuera de este alcance, **preguntar antes**.
