# Plan de desarrollo — Sitio Unidad Educativa Pestalozzi Ambato

**Versión:** 1.0
**Fecha:** 2026-08-13
**Referente técnico:** https://portfolio-matias-iglesias.vercel.app/
**Insumos:** `design-plan.md` · `mockups/estructura-web/` · `assets-video/`

---

## 1. Qué encontré en el referente

Lo inspeccioné antes de planificar. Esto es lo que realmente usa, no lo que aparenta:

| Aspecto | Hallazgo |
|---|---|
| Framework | **Ninguno.** Sin React, sin Next, sin build step. |
| Archivos | `index.html` + `css/styles.css` (28 KB) + `js/main.js` (50 KB) |
| Librerías de animación | **Cero.** Sin GSAP, ScrollTrigger, AOS, Lenis ni Locomotive. |
| Tipografías | Google Fonts: Space Grotesk, Inter, JetBrains Mono |
| Tokens | 152 custom properties en `:root` |
| Motor de animación | `IntersectionObserver` agrega una clase; **el CSS hace el movimiento** |
| `@keyframes` | Solo 4, y todos son detalles: indicador de scroll, punto pulsante, borde pulsante, cursor parpadeante |
| Accesibilidad | Bloque `prefers-reduced-motion` que apaga todo |

**La lección clave:** las animaciones no vienen de una librería. Son `transition` y `transform`
en CSS, disparadas por una clase que el JS agrega cuando el elemento entra en pantalla.
25 `transform:` y 23 `transition:` en toda la hoja. Eso es todo.

### Lo que NO vamos a copiar

El referente tiene un **canvas con una nave, control por mouse y `AudioContext`**. Es un
easter egg de portafolio: sirve para que un reclutador se acuerde de él.

En un sitio de colegio eso es un pasivo. La audiencia son representantes en Ambato, muchos
entrando desde un Android de gama media con datos móviles. Un canvas animado con audio les
quema batería, les gasta megas y no matricula a nadie. **Se descarta.**

También descartamos el tema oscuro del referente: nuestra marca es verde sobre blanco cálido.
Copiamos la **técnica**, no la piel.

---

## 2. Stack

```
HTML5 semántico  ·  CSS3 con custom properties  ·  JavaScript ES6+ sin dependencias
```

Sin build step, sin `node_modules`, sin framework. Se sube por FTP y funciona.

**Por qué esto y no Next.js o Astro:** son 4 páginas informativas que cambian dos veces
al año. Un framework agrega complejidad de despliegue y una dependencia que hay que
actualizar, a cambio de nada que este proyecto necesite. Si más adelante piden blog o
área de padres, se migra — pero no se paga esa complejidad hoy.

**Dependencias externas:**
- Google Fonts (Poppins + Inter), con `preconnect` y `display=swap`
- **GSAP 3.15.0 + ScrollTrigger**, para parallax y animaciones ligadas al scroll

### Sobre GSAP — pesos reales medidos

| Archivo | Crudo | Gzip |
|---|---|---|
| `gsap.min.js` | 72,9 KB | **28,4 KB** |
| `ScrollTrigger.min.js` | 44,6 KB | **18,0 KB** |
| `ScrollSmoother.min.js` | 13,4 KB | 5,5 KB |

Núcleo + ScrollTrigger = **46 KB comprimidos**. Licencia: "Standard no-charge license"
(GSAP 3.15.0 en npm). Conviene leer los términos antes de facturar el proyecto:
https://gsap.com/standard-license

**ScrollSmoother queda fuera.** El scroll suavizado secuestra el scroll nativo del
navegador; en Android de gama media se siente pegajoso y rompe el gesto que el usuario
conoce. El parallax no lo necesita.

---

## 3. Estructura de archivos

```
pestalozzi/
├── index.html
├── nosotros.html
├── galeria.html
├── contacto.html
├── css/
│   ├── tokens.css          ← variables de design-plan.md
│   ├── base.css            ← reset, tipografía, utilidades
│   ├── componentes.css     ← botones, tarjetas, header, footer, formulario
│   ├── animaciones.css     ← el sistema de revelado
│   └── styles.css          ← @import de los anteriores
├── js/
│   └── main.js             ← un solo archivo, en módulos internos
├── img/
│   ├── hero/               ← .webp + .jpg de respaldo
│   ├── galeria/
│   └── logo.svg
└── favicon/
```

Cuatro HTML separados, no una SPA. Cada sección es una URL real que Google indexa por
separado — es justo lo que se cobró como "SEO configurado sección por sección".

---

## 4. El sistema de animación

### 4.1 El motor (unas 20 líneas de JS)

```js
const observador = new IntersectionObserver((entradas) => {
  entradas.forEach((entrada) => {
    if (!entrada.isIntersecting) return;
    const retraso = entrada.target.dataset.retraso || 0;
    setTimeout(() => entrada.target.classList.add('visible'), retraso);
    observador.unobserve(entrada.target);   // se anima una sola vez
  });
}, { threshold: 0.15, rootMargin: '0px 0px -80px 0px' });

document.querySelectorAll('[data-revelar]').forEach((el) => observador.observe(el));
```

En el HTML basta con marcar y escalonar:

```html
<article data-revelar>…</article>
<article data-revelar data-retraso="100">…</article>
<article data-revelar data-retraso="200">…</article>
```

### 4.2 El movimiento (CSS)

```css
[data-revelar] {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity .6s var(--ease-out), transform .6s var(--ease-out);
}
[data-revelar].visible { opacity: 1; transform: none; }
```

**Solo animamos `opacity` y `transform`.** Son las dos únicas propiedades que el navegador
resuelve en la GPU sin recalcular el layout. Animar `height`, `top` o `margin` provoca
reflow y se siente entrecortado en gama media — que es justo el equipo de nuestra audiencia.

### 4.3 Capa GSAP — parallax y scroll ligado

GSAP **no reemplaza** el sistema anterior: lo complementa. Dos capas con roles distintos.

| Capa | Motor | Alcance | Se carga |
|---|---|---|---|
| **Base** | IntersectionObserver + CSS | Revelados, escalonados, hover, menú | Siempre, en todo equipo |
| **Enriquecida** | GSAP + ScrollTrigger | Parallax y efectos ligados al scroll | Solo escritorio, sin `reduced-motion` |

**Por qué en dos capas:** si GSAP falla, tarda o se bloquea, el sitio sigue animado y
completamente usable. El parallax es un lujo; el revelado es la experiencia base.

#### Carga condicional

```js
const fino    = window.matchMedia('(pointer: fine)').matches;
const ancho   = window.matchMedia('(min-width: 1024px)').matches;
const calmado = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (fino && ancho && !calmado) {
  await cargarScript('…/gsap.min.js');
  await cargarScript('…/ScrollTrigger.min.js');
  gsap.registerPlugin(ScrollTrigger);
  iniciarParallax();
}
```

Los 46 KB **no se descargan en celular**. Ahí vive el 70 % de la audiencia y es donde el
parallax se siente peor: el scroll táctil es inercial y el efecto tiembla.

#### Los efectos

```js
// Portada: el fondo se mueve más lento que el contenido
gsap.to('.portada__foto', {
  yPercent: 18, ease: 'none',
  scrollTrigger: { trigger: '.portada', start: 'top top', end: 'bottom top', scrub: true }
});
```

`scrub: true` ata la animación a la posición del scroll — sin él es un disparo, no parallax.
`ease: 'none'` es obligatorio en scrub: cualquier otra curva se siente elástica y mal.

| # | Efecto | Dónde |
|---|---|---|
| P1 | Foto de portada a velocidad menor | Portada |
| P2 | Titular subiendo más rápido que el fondo | Portada |
| P3 | Fotos de galería con desplazamiento leve | Galería |
| P4 | Franja verde que entra con el scroll | Llamado a la acción |
| P5 | Contadores ligados a scroll | Cifras |
| P6 | Fachada con zoom lento | Nosotros |

**Tope: `yPercent` entre 10 y 20.** Más que eso se nota falso y deja huecos en los bordes.

### 4.4 Inventario base (sin GSAP)

| # | Animación | Dónde | Técnica |
|---|---|---|---|
| 1 | Revelado al entrar en pantalla | Todas las secciones | IntersectionObserver + transition |
| 2 | Escalonado de tarjetas | Pilares, niveles, galería | `data-retraso` de 100 en 100 ms |
| 3 | Header que se compacta al bajar | Global | Clase `.compacto` + `backdrop-filter` |
| 4 | Enlace activo según sección | Global | IntersectionObserver (scrollspy) |
| 5 | Menú móvil | Global | `classList.toggle` + transform |
| 6 | Hover de tarjetas | Tarjetas y fotos | `transform: translateY(-4px)` + sombra |
| 7 | Lightbox de galería | Galería | Clase + transition, con foco atrapado |
| 8 | Filtros de galería | Galería | Filtrado del DOM + re-revelado |
| 9 | Indicador de scroll | Portada | `@keyframes` (el único justificado) |

### 4.4 Regla innegociable

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: .01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: .01ms !important;
    scroll-behavior: auto !important;
  }
}
```

Y en JS: si `matchMedia('(prefers-reduced-motion: reduce)').matches`, se aplica `.visible`
de entrada y no se registra ningún observador. Hay personas con trastornos vestibulares a
las que el movimiento les provoca mareo real. No es opcional.

---

## 5. Fases

### Fase 0 — Preparación (medio día)
- Estructura de carpetas
- `tokens.css` con los valores de `design-plan.md`
- Reset, tipografías, contenedor y rejilla
- Optimizar las imágenes de `assets-video/` a `.webp`

### Fase 1 — Componentes (1 día)
Header (con menú móvil) · Footer · Botones · Tarjeta · Encabezado de sección · Campo de
formulario. Se construyen aislados, en una página de prueba, antes de maquetar nada.

### Fase 2 — Inicio (1,5 días)
Portada · Propuesta (Crear/Hacer/Confianza) · Niveles · ¿Por qué Pestalozzi? · Galería ·
Llamado final. Con el copy real del video.

### Fase 3 — Interiores (2 días)
Nosotros · Galería con filtros y lightbox · Contacto con formulario, validación y mapa.

### Fase 4 — Animación e interacción (1,5 días)
Capa base: se conecta el revelado a todas las secciones y se ajustan tiempos.
Capa GSAP: carga condicional, los 6 efectos de parallax y calibración de `yPercent`.
Se prueba con `prefers-reduced-motion` activo y con el parallax desactivado.

### Fase 5 — Rendimiento, accesibilidad y publicación (1 día)
Lighthouse, teclado, lectores de pantalla, metadatos, `sitemap.xml`, despliegue.

**Total: 7,5 días de trabajo efectivo**, dentro de los 12 días cotizados. El margen es para
las 2 rondas de cambios.

---

## 6. Presupuesto de rendimiento

Se cobró "carga en menos de 1 segundo, garantizado". Estos son los topes:

Se cobró "carga en menos de 1 segundo, garantizado". GSAP mueve estos números, así que
el presupuesto se separa por dispositivo:

| Métrica | Celular (sin GSAP) | Escritorio (con GSAP) |
|---|---|---|
| Peso total de la portada | < 450 KB | < 550 KB |
| Imagen de portada | < 120 KB (`.webp`) | < 180 KB (`.webp`, 1920 px) |
| CSS total | < 40 KB | < 40 KB |
| **JS propio** | < 12 KB | < 12 KB |
| **JS de terceros** | **0 KB** | **46 KB** (gsap + ScrollTrigger) |
| LCP | < 1,5 s en 4G | < 1,2 s |
| CLS | < 0,05 | < 0,05 |
| Lighthouse | ≥ 90 | ≥ 90 |

**El compromiso de "menos de 1 segundo" se sostiene en celular**, que es donde se mide
y donde está la audiencia. En escritorio los 46 KB entran después del primer render:
GSAP se carga de forma diferida y no bloquea el LCP.

Reglas que sostienen esos números: `width` y `height` explícitos en toda imagen (evita CLS),
`loading="lazy"` en todo salvo la portada, `preload` de la imagen de portada, fuentes con
`display=swap`, GSAP inyectado dinámicamente después de `load`, y ninguna otra librería.

### Verificación obligatoria antes de entregar

Lighthouse en modo **Mobile con 4G simulado**, no en escritorio con fibra. Si el número
no se cumple ahí, el parallax se recorta — no al revés. La promesa comercial pesa más
que el efecto.

---

## 7. Accesibilidad

Landmarks semánticos · `alt` descriptivo en cada foto · foco visible de 2 px · lightbox
navegable con teclado (`←` `→` `Esc`) y foco atrapado · objetivos táctiles ≥ 44 px ·
contraste AA verificado (tabla en `design-plan.md`) · formulario con `<label>` reales,
no `placeholder` como etiqueta.

---

## 8. Copy real — sale del video institucional

Este texto es del colegio, no inventado. Reemplaza cualquier copy de relleno.

> "Mi escuelita Pestalozzi. En Pestalozzi voy a crecer **creando**, y aprendiendo
> **a hacer**, con **confianza y autonomía**. Cada día crezco **con alegría, con amor,
> y con valor**. Mi escuelita Pestalozzi **es la mejor**."

| Dónde | Texto |
|---|---|
| Titular de portada | **Mi escuelita Pestalozzi** |
| Bajada | Aquí crezco creando y aprendiendo a hacer, con confianza y autonomía. |
| Los tres pilares | **Cabeza** · **Corazón** · **Manos** (crear / hacer / confianza y autonomía) |
| Franja de valores | Cada día crezco con alegría, con amor y con valor |
| Barra de anuncio | Matrículas abiertas · Ciclo 2026 – 2027 |

Dicen **"escuelita"**, no "unidad educativa". Es el término afectivo de la comunidad:
va en el titular, y el nombre formal queda para el pie de página y los datos legales.

---

## 9. Material disponible hoy

En `assets-video/` — extraído del video institucional (720 × 1280, H.264).

| Archivo | Uso |
|---|---|
| `01-patio-ninos-corriendo` | Portada |
| `02-casita-nino` | Nivel Inicial |
| `03-aula-pizarra` | Nivel Básica |
| `04-construccion-mesa` | Pilar "Manos" |
| `06-fachada-bandera` | Nosotros |
| `07-resbaladera` | Galería |
| `05-cierre-marca` | Referencia de logo y color |

**Sirven para la demo, no para producción.** Son fotogramas verticales de video
comprimido; una portada de escritorio necesita 1920 px horizontales.

### Datos verificados (usar estos, no placeholders)

```
Dirección : Tiwinza #1995 y Etza, Ambato, Ecuador
Teléfono  : 099 824 6396
WhatsApp  : wa.me/593998246396
Correo    : 18h00063@gmail.com
Facebook  : facebook.com/pestalozziambato
```

### Cifras institucionales

No hay ninguna verificada. Años de trayectoria, número de estudiantes y de docentes
**no se publican hasta que el colegio los confirme**: son datos factuales de una
institución educativa, no relleno de diseño.

---

## 10. Estado del repositorio

**Versión vanilla funcional (demo) — estructura según este plan:**

```
pestalozzi/
├── index.html
├── nosotros.html
├── galeria.html
├── contacto.html
├── css/                  ← tokens · base · componentes · animaciones
├── js/
│   ├── main.js           ← un solo archivo, en módulos internos
│   └── vendor/           ← gsap.min.js + ScrollTrigger.min.js (3.15.0, locales)
├── img/
│   ├── hero/             ← 4 WebP (portada, nosotros, galeria, contacto)
│   ├── galeria/          ← 6 WebP (g01–g06)
│   └── marca/            ← logo.png
├── _vanilla-respaldo/    ← HTML de referencia (idéntico al de la raíz)
└── PLAN-DESARROLLO.md
```

Se descartó el andamiaje Astro (package.json, node_modules, `src/`, `public/`) del
intento anterior: este sitio es estático puro, se sube por FTP y funciona sin build.

**Nota de licencia GSAP:** se vendorizaron `gsap.min.js` y `ScrollTrigger.min.js`
(3.15.0) localmente — sin CDN, carga diferida solo en escritorio. La "Standard
no-charge license" permite uso sin pago; conviene revisar los términos antes de
facturar al cliente: https://gsap.com/standard-license

**Pendiente para producción (requieren confirmación del colegio):**
- Dominio definitivo → activar `canonical`, `sitemap.xml` y `robots.txt` (hoy NO se
  publican URLs inventadas)
- Logo en vector → fijar el verde de una vez
- Niveles ofertados, misión y visión, horario de atención
- Fotografías en alta resolución con autorización de uso de imagen

**Des-IA (2026-08-13):** rediseño estructural para romper el patrón de landing
generada: tipografía Fraunces+Figtree (antes Poppins+Inter); hero editorial alineado
a la izquierda con palabra destacada en ámbar y CTA único + enlace con flecha;
filosofía como lista numerada editorial (01/02/03) sin iconos Feather ni tarjetas;
partido asimétrico 5fr/7fr; franja CTA con foto de fondo y un solo llamado; sin
WhatsApp flotante; sin badges "Por confirmar"/"Demo"; sin columna "Secciones" del
footer; botones no-pill, tarjetas sin sombra ni lift.

**Des-IA 2 (2026-08-13, tarde):** fuente elegida por el usuario → **Lora +
Work Sans** (primero Newsreader+Figtree, pero seguía sonando a IA; cambiada a Lora
serif humanista + Work Sans, sin numeración); **video institucional en el hero** de portada
(transcodificado con ffmpeg en 2 variantes ligeras: `img/hero/hero-movil.mp4`
540×960 2,8 MB y `img/hero/hero-escritorio.mp4` crop 16:9 720×404 2,1 MB; la foto
`portada.webp` queda como poster/primer paint y fallback, el video aparece con fade
vía `.hero__video--listo` en `canplay`, se elimina con reduced-motion); **view
transitions** cross-document (fade 180 ms, progresivo); **hero split con máscara**
(palabras del titular reveladas con stagger vía GSAP, preservando `<br>` y `<em>`);
hover refinados (subrayado sweep ámbar en `.enlace-flecha`, escala sutil en
`.tarjeta--foto img`). El nav ya tenía underline sweep.

---

## 11. Lo que bloquea

| Bloqueante | Impacto |
|---|---|
| **Logo en vector** | Define el verde. Hay 3 lecturas que no coinciden — ver `assets-video/EXTRAIDO-DEL-VIDEO.md` |
| **Niveles que ofertan** | Sección completa de Inicio |
| **Misión y visión** | Sección de Nosotros |
| **Horario de atención** | Contacto y ficha de Google |
| **Fotos en alta + autorización firmada** | Portada y galería. Son menores identificables: es requisito legal, no estético |

Las fotos de `assets-video/` alcanzan para el demo, pero son 720 px verticales de video
comprimido. Una portada de escritorio necesita 1920 px horizontales.

**Se puede arrancar hoy** con Fases 0 y 1: no dependen de ninguno de estos puntos.
