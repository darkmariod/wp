# PROMPT — Estructura de aulas Pestalozzi + panel Filament en español

## CONTEXTO

Proyecto Laravel en `/Users/mariopazmino/Desktop/moodle-pestalozzi/aula`
Stack: Laravel 13.30 · Filament 5.7.8 · Livewire 4.4.4 · Tailwind v4 · MySQL

- Panel de staff: **Filament en `/admin`** — funciona correctamente.
- Portal de familias: **React/Inertia en `/mi-escuelita`** — funciona, no es el foco.

Hay cambios sin commitear que arreglan dos bugs (namespace de `Section` movido a
`Filament\Schemas\Components\Section`, y `brandLogo` envuelto en `HtmlString`).
**Commitearlos primero** como punto de retorno.

Usuarios de prueba (contraseña `password`):
`admin@pestalozzi.test` · `guia@pestalozzi.test` (Teffy Haro, rol guía) · `familia@pestalozzi.test`

---

## OBJETIVO

1. Modelar la estructura real del colegio: dos aulas, 15 alumnos cada una.
2. Dejar el panel `/admin` con CRUD completo (crear, editar, eliminar) y en español.
3. Que el contenido publicado desde Filament aparezca en la vista de los padres.

---

## MODELO DE NEGOCIO REAL

### Dos aulas, 15 alumnos cada una

| Aula | Edades | Materias | Contenido | Calificaciones |
|---|---|---|---|---|
| **Mentes Absorbentes** | 3 a 6 años | Obligatorias: Lectura, Matemática, Inglés | Carga de contenido | **No hay** |
| **Mentes Razonadoras** | 6 a 9 años | Obligatorias: Lectura, Matemática, Inglés + materias abiertas | Solo lectura y ejercicios | **No hay** |

### Reglas

- **No existen calificaciones en ningún lado.** No crear modelos, campos, columnas
  ni pantallas de notas. Si aparece algo de calificaciones en el código, eliminarlo.
- Las tres materias obligatorias (Lectura, Matemática, Inglés) existen en ambas aulas.
- Mentes Razonadoras además admite **materias abiertas** (configurables por el staff).
- En Mentes Razonadoras solo se sube contenido de tipo **lectura** y **ejercicios**.

---

## LO QUE YA EXISTE (reusar, no reescribir)

### Modelos y tablas

```
Environment (aula)  → name, age_range, teacher_id, description, image, active, order
Area (materia)      → name, icon, image, description, order, active
Content             → title, slug, type, description, teacher_id, environment_id,
                      area_id, cover_image, published_at, requires_evidence, status
Child, Family, User, Evidence, Feedback, Media, Observation
```

`Environment.age_range` ya existe: sirve tal cual para "3 a 6 años" / "6 a 9 años".

### Tipos y estados de Content (ya definidos en `app/Models/Content.php`)

```php
TYPE_EXPERIENCE  TYPE_READING  TYPE_VIDEO  TYPE_TASK
TYPE_DOCUMENT    TYPE_GALLERY  TYPE_ANNOUNCEMENT

STATUS_DRAFT  STATUS_SCHEDULED  STATUS_PUBLISHED  STATUS_ARCHIVED
```

`TYPE_READING` = lectura y `TYPE_TASK` = ejercicios. **Ya existen, usarlos.**

### El circuito hacia los padres YA FUNCIONA

`app/Http/Controllers/MiEscuelita/ExperienceController.php` consulta:

```php
Content::query()->published()
    ->where(/* filtra por el ambiente del niño */)
    ->orderByDesc('published_at')
```

Publicar en Filament con `status = published` hace que el contenido aparezca
automáticamente en la vista de las familias. **No hay que construir ese puente.**

### Otros activos

- 10 policies en `app/Policies/` — escritas y testeadas. Usarlas, no reescribirlas.
- Middlewares `EnsureStaff` (alias `staff`) y `EnsureUserIsFamilia` (alias `familia`).
- Baseline de tests: **65 tests / 177 assertions en verde**.

---

## PARTE A — ESTRUCTURA DE AULAS

1. **Materias obligatorias.** Agregar a la tabla `areas` una columna booleana
   `is_required` (default `false`). Migración nueva, no editar migraciones ya corridas.
   Marcar Lectura, Matemática e Inglés como obligatorias.

2. **Seeder de estructura real.** Reescribir `database/seeders/DatabaseSeeder.php`
   (o crear uno nuevo `PestalozziSeeder`) con:
   - 2 environments:
     - `Mentes Absorbentes` — `age_range` "3 a 6 años"
     - `Mentes Razonadoras` — `age_range` "6 a 9 años"
   - Areas obligatorias: Lectura, Matemática, Inglés (`is_required = true`)
   - Areas abiertas de ejemplo para Razonadoras (`is_required = false`)
   - 15 children por environment (30 en total), cada uno con su Family
   - Contenido de ejemplo publicado en ambas aulas, tipos `reading` y `task`

3. **Restricción de tipos por aula.** En `ContentResource`, cuando el ambiente
   seleccionado sea Mentes Razonadoras, el campo `type` debe ofrecer únicamente
   `reading` y `task`. Implementarlo de forma reactiva en el formulario y
   **validarlo también del lado del servidor** (no confiar solo en la UI).

4. **Eliminar rastros de calificaciones.** Buscar en todo el repo por
   `grade`, `calificacion`, `nota`, `score` y quitar lo que aplique.

---

## PARTE B — CRUD COMPLETO EN FILAMENT

Para los 6 resources en `app/Filament/Resources/` (Child, Family, User,
Environment, Area, Content), verificar y completar:

- Listar con búsqueda, orden, filtros y paginación
- Crear
- Editar
- **Eliminar** (`DeleteAction` en fila + `DeleteBulkAction` en masa)
- Respetar las policies existentes en cada acción

Además:

- Filtro por ambiente y por materia en el listado de Contenidos.
- Filtro por ambiente en el listado de Niños.
- Acción rápida de publicar/despublicar contenido desde el listado.

---

## PARTE C — PANEL EN ESPAÑOL

**Causa 1 — locale.** `config('app.locale')` es `en`. Filament ya trae las
traducciones al español instaladas en `vendor/filament/*/resources/lang/es/`
pero no se activan.

Poner `APP_LOCALE=es` en `.env` y `.env.example`, y cambiar el default en
`config/app.php` de `'en'` a `'es'`. Dejar `APP_FALLBACK_LOCALE=es`.

Esto traduce: Search, Per page, New, Sign out, breadcrumbs (List/Create/Edit),
validaciones y notificaciones.

**Causa 2 — labels de resources.** Ninguno define labels, así que Filament los
deriva del nombre de la clase (`ChildResource` → "children"). Agregar a cada uno:

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

Respetar las tildes. **No tocar `$navigationGroup`** — ya dice
"Gestión de Familias" y "Gestión Académica", están bien.

**Causa 3 — labels sueltos.** Recorrer los 6 resources y traducir cualquier
`->label()` que siga en inglés, más los títulos de `Section::make()` y los
textos de acciones y filtros.

---

## VERIFICACIÓN (obligatoria, con evidencia real)

1. `php artisan optimize:clear`
2. `php artisan migrate:fresh --seed`
3. `php artisan test` → los 65 tests en verde (ajustar los que dependan del
   seeder viejo, sin bajar la cobertura)
4. Abrir `/admin` en **ventana privada** del navegador. El caché viejo de
   Firefox muestra renders muertos y hace parecer que el panel está roto.
   Entrar como `guia@pestalozzi.test` / `password`.
5. Confirmar en pantalla:
   - Menú en español: Niños · Ambientes · Contenidos
   - Los 2 ambientes con sus rangos de edad
   - 30 niños repartidos 15 y 15
   - Crear un contenido tipo lectura en Mentes Razonadoras y publicarlo
   - Editarlo y eliminarlo (probar el CRUD completo)
6. Entrar como `familia@pestalozzi.test` a `/mi-escuelita/experiencias` y
   confirmar que **el contenido publicado en el paso 5 aparece ahí**.

---

## RESTRICCIONES

- Identificadores, clases y nombres de tablas en inglés.
  Copy de UI y comentarios en español (convención ya usada en el repo).
- No hacer `git push`. Mostrar el resultado en local para revisión primero.
- No borrar el portal de familias (`/mi-escuelita`, 8 controllers, 18 páginas
  `.jsx`). Si hiciera falta tocarlo, preguntar antes.
