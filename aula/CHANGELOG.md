# Changelog

## Revisión y mejoras — auditoría posterior a Bloque 0
- Bug: el scheduler publicaba con Query Builder y NO disparaba `ContentObserver`
  (las familias nunca recibían la notificación de estrenos publicados). Ahora lo hace
  con loop Eloquent.
- Timezone del app a `America/Guayaquil` (antes UTC, publicaciones programadas
  y fechas desfasadas 5 h).
- Soft deletes (`deleted_at`) en datos de menores: `content`, `children`,
  `evidence`, `observations`, `feedback` — recuperación ante borrados accidentales
  (LOPDP).
- Seguridad: `viewAny` de `ContentPolicy`/`EnvironmentPolicy` restringido a
  personal activo; nuevas `UserPolicy` y `AreaPolicy` (solo staff; `forceDelete`
  solo administrador).
- XSS: el cuerpo HTML de experiencias ahora se sanea con DOMPurify antes de
  renderizarse en el portal.
- Imágenes de portada: `cover_image` pasa al disco público y el portal usa
  `cover_image_url` con fallback visual.
- "Duplicar contenido" ahora copia también los archivos media adjuntos.
- `Evidence` y `Observation` ganan `HasFactory` + factories propias.
- Rate limit específico `throttle:10,1` en subida de evidencias (uploads pesados).
- UI: Login/Registro/Dashboard/Welcome y layouts en español con la paleta de
  Mi Escuelita (antes plantillas grises en inglés de Laravel).
- Fix leak de memoria de `URL.createObjectURL` en previsualización de fotos.
- Filtro de estado en `ChildResource` usa `SelectFilter` sobre `status`
  (antes un nombre confuso con `TernaryFilter`).

## Bloque 0 — Migración a Filament v5, Livewire 4 y Tailwind v4
- Filament `5.7` + Livewire `4.4` (upgrade oficial con `filament-v5`).
- Acciones de tabla migradas al API v5: `recordActions()` / `toolbarActions()`
  y namespace `Filament\Actions\*` (EditAction, DeleteAction, BulkActionGroup).
- Tailwind v4 CSS-first: tokens de diseño movidos a `@theme` en `app.css`
  (paleta green/ink/accent, fuente DM Sans, radios, sombras, transiciones).
- Plugin `@tailwindcss/vite`; eliminados `tailwind.config.js` y `postcss.config.js`.
- Smoke tests nuevos de regresión: `FilamentV5SmokeTest` (panel completo +
  bloqueo de familias) y `PortalV5SmokeTest` (rutas del portal).

## Bloque 9 — Pendiente de validación UX final

## Bloque 8 — Documentación
- `DEPLOY.md`: guía de instalación y puesta en producción.

## Bloque 7 — Producción
- `robots.txt`, `sitemap.xml`, `favicon.svg` y meta tags SEO/OpenGraph en `app.blade.php`.
- Comando `backup:database` (respaldo de SQLite/MySQL) + tarea diaria programada.
- Scheduler público: `content:publish-scheduled` y `backup:database`.

## Bloque 6 — Rendimiento
- Eliminado el N+1 en `ExperienceController::historial` cargando las
  observaciones en una sola query indexada por (child, content).

## Bloque 5 — Tests
- Nuevos archivos: `SecurityTest`, `PrivacyTest`, `MediaSecurityTest`, `ScheduledContentTest`.
- Cobertura de manipulación de IDs, firmas expiradas/ajenas, acceso a
  archivos privados entre familias, programación y privacidad de menores.

## Bloque 4 — Seguridad y privacidad
- Middleware `staff` con rate limiting; `familia` ahora también limita (60/min).
- `FamilyPolicy` y `MediaPolicy` nuevas.
- Página pública de Política de Privacidad alineada a la LOPDP de Ecuador.

## Bloque 3 — Scheduler, duplicar, vista previa y filtros
- Comando `content:publish-scheduled` + registro cada minuto en `routes/console.php`.
- Acción "Duplicar" en `ContentResource` (crea borrador copiándolo).
- Acción "Vista previa": genera enlace firmado de 30 min que abre la vista
  real de la familia en una pestaña nueva (`POST /mi-escuelita/vista-previa/{content}`).
- Filtros avanzados en la tabla de contenido (publicados, programados pendientes, etc.).

## Bloque 2 — Filament CRUD
- 6 recursos CRUD: Familia, Usuario, Niño, Ambiente, Área, Contenido.
- Panel con color Emerald y marca "Mi Escuelita".

## Bloque 1 — Design system, animaciones, responsive y accesibilidad
- Tokens CSS, componentes `Button/Card/Badge/PageHeader/FadeIn`, hook `useInView`.
- Layout del portal familiar con menú animado, aria-labels y targets táctiles.
