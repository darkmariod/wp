# Design Plan — Unidad Educativa Pestalozzi Ambato

**Versión:** 1.0
**Fecha:** 2026-08-07
**Fuente de marca:** https://www.facebook.com/pestalozziambato
**Alcance:** sitio informativo de 4 secciones (Inicio, Nosotros, Galería, Contacto)

---

## 1. Datos institucionales verificados

Extraídos directamente de la página oficial de Facebook (sección *Intro*):

| Campo | Valor |
|---|---|
| Nombre | Unidad Educativa Pestalozzi Ambato |
| Categoría | Elementary School / Unidad Educativa |
| Descripción | "Unidad Educativa en la ciudad de Ambato." |
| Dirección | Tiwinza #1995 y Etza, Ambato, Ecuador |
| Teléfono | 099 824 6396 |
| Email | 18h00063@gmail.com |
| Facebook | facebook.com/pestalozziambato |
| Seguidores | ~1.7K |
| Ciclo en campaña | 2026–2027 |

**Posicionamiento declarado por la institución** (post del 28 de julio):
> "Pestalozzi no es solo el nombre de nuestra institución, es el reflejo de una filosofía que pone al niño en el centro de su aprendizaje."

Este es el eje narrativo del sitio. Todo el copy debe girar alrededor de *el niño en el centro*.

> ⚠️ **Pendiente de confirmar con el cliente:** niveles educativos que ofertan (Inicial / EGB / Bachillerato), horario de atención, misión y visión formales, y si `18h00063@gmail.com` es el correo público definitivo (parece ser el código AMIE usado como usuario de correo — conviene migrar a un dominio propio, ej. `info@pestalozziambato.edu.ec`).

---

## 2. Color

### 2.1 Color primario — extraído del logotipo

El logotipo usa **un solo verde institucional**, medido pixel a pixel sobre el PNG oficial de perfil:

| Rol | Hex | Uso |
|---|---|---|
| **Verde Pestalozzi** (primario) | `#126333` | Logo, headers, botones primarios, títulos |
| Verde profundo | `#0B4A26` | Hover de botones, footer, overlays sobre foto |
| Verde oscuro (sombra del logo) | `#005622` | Bordes, estados activos |

`#126333` es el color exacto del isotipo y del wordmark. **No se debe alterar.**

### 2.2 Escala derivada

Construida a partir del primario para tener tintes usables en fondos y estados:

```css
--green-950: #05341A;
--green-900: #0B4A26;
--green-800: #126333;  /* PRIMARIO — color de marca */
--green-700: #197A40;
--green-600: #239450;
--green-500: #35AD65;
--green-300: #86D4A6;
--green-100: #D7F0E2;
--green-50:  #EFF9F3;
```

### 2.3 Neutros y acento

El verde intenso necesita respiro. Base de neutros cálidos (no gris azulado) para que el sitio se sienta humano y no corporativo:

```css
--ink-900:   #14261C;  /* texto principal (verde-negro, no negro puro) */
--ink-600:   #4A5A51;  /* texto secundario */
--ink-400:   #68776F;  /* captions y texto terciario — 4.51:1, mínimo AA */
--ink-300:   #8A968F;  /* SOLO decorativo (iconos, divisores). Nunca texto: 2.94:1 */
--surface:   #FFFFFF;  /* fondo de tarjetas */
--bg:        #FAFAF7;  /* fondo de página, blanco cálido */
--border:    #E4E7E3;

--accent-500: #E8A33D;  /* ámbar — CTA secundario, badges, "Admisiones abiertas" */
--accent-100: #FCF0DA;
```

> El ámbar `#E8A33D` **no está en la marca actual**: es una propuesta. Complementa el verde, aporta calidez infantil y da un CTA que destaca sin competir con el primario. Si el cliente lo rechaza, el fallback es usar `--green-500` para los CTA secundarios.

### 2.4 Colores funcionales

```css
--success: #239450;   /* reutiliza la familia de marca */
--warning: #E8A33D;
--error:   #C0402F;
--info:    #2F6FB0;
```

### 2.5 Contraste — obligatorio

Ratios calculados con la fórmula de luminancia relativa de WCAG 2.1:

| Combinación | Ratio | Estado |
|---|---|---|
| `#126333` sobre `#FFFFFF` | 7.35:1 | ✅ AAA |
| `#FFFFFF` sobre `#126333` | 7.35:1 | ✅ AAA |
| `#FFFFFF` sobre `#0B4A26` | 10.39:1 | ✅ AAA |
| `#14261C` sobre `#FAFAF7` | 15.17:1 | ✅ AAA |
| `#4A5A51` sobre `#FAFAF7` | 6.99:1 | ✅ AA |
| `#68776F` sobre `#FAFAF7` | 4.51:1 | ✅ AA (mínimo) |
| `#14261C` sobre `#E8A33D` | 7.36:1 | ✅ AAA |
| `#FFFFFF` sobre `#E8A33D` | 2.16:1 | ❌ **Prohibido** |
| `#8A968F` sobre `#FAFAF7` | 2.94:1 | ❌ **No usar como texto** |

**Reglas:** sobre ámbar siempre texto `--ink-900`, nunca blanco. `--ink-300` es decorativo, jamás texto.

---

## 3. Tipografía

### 3.1 Análisis del logotipo

El wordmark "Pestalozzi" es una **sans geométrica pesada con terminaciones redondeadas**: `a` de un solo piso, `e` con barra horizontal, `t` con cola curva, punto de la `i` circular, `z` con cortes diagonales limpios. "Ambato" es la misma familia en peso regular.

> **Nota de honestidad:** no tengo el archivo de fuente original, así que no puedo confirmar la tipografía exacta del logo. Lo que sigue son las coincidencias más cercanas disponibles en Google Fonts, elegidas por estructura geométrica, altura de x y terminaciones.

### 3.2 Selección

| Rol | Fuente | Pesos | Por qué |
|---|---|---|---|
| **Títulos** | `Poppins` | 600, 700 | Geométrica, `a` de un solo piso, la más cercana al wordmark. Google Fonts, gratuita, soporte completo de español. |
| **Cuerpo** | `Inter` | 400, 500, 600 | Poppins es mala para lectura larga (interletraje amplio, ascendentes cortas). Inter tiene altura de x alta y es óptima en pantalla. |
| Alternativa a Poppins | `Nunito` | 700, 800 | Si el cliente quiere un tono **más redondeado e infantil**, Nunito se acerca más a las terminaciones suaves del logo. |

**Recomendación:** Poppins + Inter. Es la combinación que mantiene el sitio coherente con el logo sin sacrificar legibilidad en párrafos.

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
```

```css
--font-display: 'Poppins', system-ui, -apple-system, sans-serif;
--font-body: 'Inter', system-ui, -apple-system, sans-serif;
```

### 3.3 Escala tipográfica

Escala modular 1.25 (mayor tercera), con fluidez por `clamp()`:

| Token | Tamaño | Line-height | Peso | Fuente | Uso |
|---|---|---|---|---|---|
| `display` | `clamp(2.5rem, 5vw, 4rem)` | 1.05 | 700 | Poppins | H1 del hero |
| `h1` | `clamp(2rem, 4vw, 3rem)` | 1.15 | 700 | Poppins | Título de sección |
| `h2` | `clamp(1.5rem, 3vw, 2rem)` | 1.25 | 600 | Poppins | Subsección |
| `h3` | `1.25rem` | 1.35 | 600 | Poppins | Título de tarjeta |
| `body-lg` | `1.125rem` | 1.7 | 400 | Inter | Intro de sección |
| `body` | `1rem` | 1.65 | 400 | Inter | Párrafo base |
| `small` | `0.875rem` | 1.5 | 400 | Inter | Captions, pie de foto |
| `label` | `0.8125rem` | 1.4 | 600 | Inter | Etiquetas, eyebrow (`letter-spacing: 0.08em`, mayúsculas) |

**Reglas duras:**
- Ancho máximo de línea en párrafos: `65ch`.
- Nunca menos de `16px` en cuerpo (móvil incluido).
- Títulos en Poppins con `letter-spacing: -0.02em` a partir de `h2`.

---

## 4. Sistema base

### 4.1 Espaciado (base 4px)

```css
--space-1: 0.25rem;  --space-2: 0.5rem;   --space-3: 0.75rem;
--space-4: 1rem;     --space-6: 1.5rem;   --space-8: 2rem;
--space-12: 3rem;    --space-16: 4rem;    --space-24: 6rem;
```

Padding vertical de sección: `--space-16` en móvil, `--space-24` en desktop.

### 4.2 Radios y sombras

```css
--radius-sm: 8px;    /* inputs, badges */
--radius-md: 16px;   /* tarjetas, imágenes */
--radius-lg: 24px;   /* bloques del hero */
--radius-full: 999px;/* botones y píldoras */

--shadow-sm: 0 1px 2px rgba(20, 38, 28, 0.06);
--shadow-md: 0 4px 16px rgba(20, 38, 28, 0.08);
--shadow-lg: 0 12px 32px rgba(20, 38, 28, 0.12);
```

Botones con `--radius-full`: refuerza el carácter redondeado del logo.

### 4.3 Layout

```css
--container: 1200px;
--container-narrow: 720px;  /* para bloques de texto largo */
--gutter: 1.5rem;
```

Grid de 12 columnas en desktop, 4 en móvil.

### 4.4 Breakpoints (mobile-first)

```
sm: 640px    md: 768px    lg: 1024px    xl: 1280px
```

---

## 5. Estructura de secciones

Navegación: `Inicio · Nosotros · Galería · Contacto` + botón CTA "Admisiones 2026–2027".

### 5.1 Inicio

| Bloque | Contenido | Nota |
|---|---|---|
| Header | Logo + nav + CTA | Sticky, fondo translúcido con blur al hacer scroll |
| Hero | Foto de estudiantes + H1 + subtítulo + 2 CTA | Overlay `--green-900` al 55% para garantizar contraste del texto |
| Propuesta | 3–4 tarjetas con la filosofía Pestalozzi | Íconos en `--green-800` sobre `--green-50` |
| Niveles | Cards por nivel educativo | ⚠️ Requiere confirmación de niveles ofertados |
| Cifras | Años de trayectoria, estudiantes, docentes | Opcional; solo si hay datos reales |
| Galería preview | 6 fotos + link a Galería | |
| CTA final | "Agenda tu visita" → Contacto | Franja `--green-800` a ancho completo |
| Footer | Datos de contacto, mapa, redes | |

**H1 propuesto:** "El niño en el centro de su aprendizaje"
**Subtítulo:** "Unidad Educativa Pestalozzi Ambato — formación integral con la filosofía que puso al estudiante primero."

### 5.2 Nosotros

Ancho `--container-narrow` para el texto, imágenes a sangre completa entre bloques.

1. **Quiénes somos** — historia y presencia en Ambato.
2. **Filosofía Pestalozzi** — el diferenciador real de la marca. Explicar el método (cabeza, corazón y mano) y cómo se aplica en el aula.
3. **Misión y visión** — dos tarjetas lado a lado. ⚠️ Texto pendiente del cliente.
4. **Modelo pedagógico** — cómo se enseña, tamaño de grupo, acompañamiento.
5. **Equipo docente** — grid de retratos con nombre y área. Opcional en fase 1.

### 5.3 Galería

- Grid tipo *masonry*, 3 columnas en desktop / 2 en tablet / 1 en móvil.
- Filtros por categoría: Instalaciones · Actividades · Deportes · Eventos.
- Lightbox accesible: navegable con teclado (`←` `→` `Esc`), `focus-trap`, `alt` obligatorio en cada imagen.
- Imágenes en **WebP** con `<img loading="lazy">` y `width`/`height` explícitos para evitar CLS.
- Fuente inicial: el álbum de fotos del Facebook institucional (requiere autorización y originales en alta resolución).

### 5.4 Contacto

Dos columnas en desktop, apiladas en móvil.

**Izquierda — datos verificados:**
- 📍 Tiwinza #1995 y Etza, Ambato, Ecuador
- 📞 099 824 6396 → `tel:+593998246396`
- ✉️ 18h00063@gmail.com → `mailto:`
- 💬 WhatsApp → `https://wa.me/593998246396` (CTA principal, es el canal real de una institución en Ecuador)
- 🕐 Horario de atención ⚠️ pendiente
- Facebook oficial

**Derecha:**
- Formulario: nombre, email, teléfono, nivel de interés, mensaje. Validación en cliente + honeypot antispam.
- Mapa embebido de Google Maps a ancho completo debajo, `loading="lazy"`.

---

## 6. Componentes a construir

| Componente | Variantes |
|---|---|
| `Button` | primary (verde), secondary (outline verde), accent (ámbar), ghost |
| `Header` | default, scrolled, mobile-drawer |
| `Hero` | con imagen de fondo + overlay |
| `Card` | feature, nivel, docente, testimonio |
| `SectionHeading` | eyebrow + título + descripción |
| `GalleryGrid` + `Lightbox` | |
| `ContactForm` | estados: idle, submitting, success, error |
| `Footer` | |

Organización sugerida (atomic design): `atoms/` · `molecules/` · `organisms/` · `sections/`.

---

## 7. Accesibilidad y rendimiento — no negociable

- Contraste AA mínimo en todo texto (ver 2.5).
- `:focus-visible` con outline de 2px en `--green-800`, offset 2px. Nunca `outline: none` sin reemplazo.
- Navegación completa por teclado, incluido el lightbox.
- Landmarks semánticos: `<header> <nav> <main> <section> <footer>`.
- `alt` descriptivo en todas las fotos de estudiantes.
- Respetar `prefers-reduced-motion`.
- Objetivos táctiles ≥ 44×44 px.
- Lighthouse objetivo: ≥ 90 en las cuatro categorías.
- Imágenes WebP, `srcset` responsive, `loading="lazy"` salvo el hero.

**Privacidad:** son fotos de menores de edad. Verificar autorización de uso de imagen firmada por representantes antes de publicar cualquier foto identificable. Este punto es legal, no estético.

---

## 8. Entregables y siguiente paso

**Fase de diseño**
1. ✅ Este design plan (tokens de color, tipografía, estructura).
2. Wireframes de las 4 secciones en móvil y desktop.
3. Mockup de alta fidelidad de Inicio + un interior.
4. Aprobación del cliente sobre el acento ámbar y los textos pendientes.

**Fase de desarrollo**
5. Tokens en CSS custom properties / `@theme` de Tailwind 4.
6. Librería de componentes base.
7. Maquetación de las 4 secciones.
8. Integración de contenido real (fotos en alta, textos aprobados).
9. QA de accesibilidad y rendimiento.

**Bloqueantes antes de maquetar contenido:**
- Niveles educativos ofertados
- Misión, visión y valores formales
- Horario de atención
- Fotografías en alta resolución con autorización de uso
- Decisión sobre dominio y correo institucional
