// Verificación del sitio construido.
// Uso:  npm run build && node scripts/verificar.mjs
// Sale con código 1 si alguna prueba crítica falla (sirve para CI).
import { readFile, readdir, stat } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { gzipSync } from 'node:zlib';

const dist = resolve(dirname(fileURLToPath(import.meta.url)), '..', 'dist');
const PAGINAS = ['index.html', 'nosotros.html', 'galeria.html', 'contacto.html'];

let pasadas = 0, fallidas = 0, avisos = 0;
const ok    = (m) => { pasadas++; console.log(`  \x1b[32m✓\x1b[0m ${m}`); };
const fallo = (m) => { fallidas++; console.log(`  \x1b[31m✗ ${m}\x1b[0m`); };
const aviso = (m) => { avisos++;  console.log(`  \x1b[33m!\x1b[0m ${m}`); };
const titulo = (t) => console.log(`\n\x1b[1m${t}\x1b[0m`);

const html = {};
for (const p of PAGINAS) html[p] = await readFile(join(dist, p), 'utf8');

/* ─────────────────────────────────────────────────────────────
   1. EL BUG DEL HTML CRUDO — cero hojas que bloqueen el render
   ───────────────────────────────────────────────────────────── */
titulo('1. Destello de HTML sin estilos');
for (const p of PAGINAS) {
  const enlaces = html[p].match(/<link[^>]*rel="stylesheet"[^>]*>/g) || [];
  const bloquean = enlaces.filter((l) => !l.includes('media="print"'));
  // el de <noscript> no cuenta: solo aplica sin JS
  const sinNoscript = bloquean.filter((l) => {
    const i = html[p].indexOf(l);
    const antes = html[p].slice(Math.max(0, i - 120), i);
    return !antes.includes('<noscript>');
  });
  sinNoscript.length === 0
    ? ok(`${p}: 0 hojas bloqueantes`)
    : fallo(`${p}: ${sinNoscript.length} hoja(s) bloquean el render`);

  /<style>[\s\S]{5000,}<\/style>/.test(html[p])
    ? ok(`${p}: CSS embebido en el documento`)
    : fallo(`${p}: no tiene el CSS embebido`);
}

/* ─────────────────────────────────────────────────────────────
   2. NAVEGACIÓN — sin enlaces que disparen redirect
   ───────────────────────────────────────────────────────────── */
titulo('2. Navegación sin redirects');
for (const p of PAGINAS) {
  const conHtml = (html[p].match(/href="[^"]*\.html"/g) || []);
  conHtml.length === 0
    ? ok(`${p}: ningún enlace interno con .html`)
    : fallo(`${p}: ${conHtml.length} enlace(s) .html -> provocan 308: ${conHtml.slice(0,3)}`);
}

/* ─────────────────────────────────────────────────────────────
   3. TELÉFONO — número oficial publicado en todas partes
   ───────────────────────────────────────────────────────────── */
titulo('3. Teléfono oficial');
// El colegio confirmó el número el 2026-08-14 (099 824 6396 / 593998246396).
// Ya NO es un placeholder: ahora se verifica que esté publicado en todas
// partes y que no quede ningún rastro del antiguo ficticio (099 000 0000).
const OFICIAL = '593998246396';
const PLACEHOLDER_VIEJO = ['593990000000', '099 000 0000'];
const archivos = [];
const recorrer = async (d) => {
  for (const e of await readdir(d, { withFileTypes: true })) {
    const r = join(d, e.name);
    if (e.isDirectory()) await recorrer(r);
    else if (/\.(html|js|json|css)$/.test(e.name)) archivos.push(r);
  }
};
await recorrer(dist);
let quedaPlaceholder = false;
for (const f of archivos) {
  const c = await readFile(f, 'utf8');
  for (const n of PLACEHOLDER_VIEJO) if (c.includes(n)) { fallo(`${f.replace(dist,'dist')}: aún tiene el placeholder viejo "${n}"`); quedaPlaceholder = true; }
}
if (!quedaPlaceholder) ok('ningún archivo conserva el placeholder ficticio anterior');
html['index.html'].includes(OFICIAL) ? ok('número oficial presente en la portada')
                                     : fallo('el número oficial no aparece en la portada');
html['index.html'].includes(`"telephone": "+${OFICIAL}"`) ? ok('número oficial en el JSON-LD (lo que lee Google)')
                                                          : fallo('el JSON-LD no tiene el número oficial');
for (const p of PAGINAS) {
  html[p].includes(OFICIAL) ? ok(`${p}: número oficial presente`) : fallo(`${p}: falta el número oficial`);
}

/* ─────────────────────────────────────────────────────────────
   4. GSAP — no debe cargarse en celular
   ───────────────────────────────────────────────────────────── */
titulo('4. GSAP solo en escritorio');
const js = await readFile(join(dist, 'js', 'main.js'), 'utf8');
js.includes('punteroFino && pantallaAncha')
  ? ok('la carga está condicionada a puntero fino + ancho >= 1024')
  : fallo('GSAP se cargaría en cualquier dispositivo');
/pointer: fine/.test(js) && /min-width: 1024px/.test(js)
  ? ok('media queries correctas')
  : fallo('faltan las media queries de la condición');

/* ─────────────────────────────────────────────────────────────
   5. ACCESIBILIDAD
   ───────────────────────────────────────────────────────────── */
titulo('5. Accesibilidad');
for (const p of PAGINAS) {
  const imgs = html[p].match(/<img[^>]*>/g) || [];
  const sinAlt = imgs.filter((i) => !/\salt=/.test(i));
  sinAlt.length === 0 ? ok(`${p}: todas las imágenes con alt`)
                      : fallo(`${p}: ${sinAlt.length} imagen(es) sin alt`);
  html[p].includes('lang="es-EC"') ? ok(`${p}: idioma declarado`) : fallo(`${p}: falta lang`);
  html[p].includes('class="saltar"') ? ok(`${p}: enlace de salto`) : aviso(`${p}: sin enlace de salto`);
}
const contacto = html['contacto.html'];
const labels = (contacto.match(/<label[^>]*for="/g) || []).length;
labels >= 5 ? ok(`formulario: ${labels} labels reales`) : fallo(`formulario: solo ${labels} labels`);
contacto.includes('aria-live') ? ok('formulario: avisos con aria-live') : fallo('formulario: sin aria-live');
html['index.html'].includes('prefers-reduced-motion') || (await readFile(join(dist,'js','main.js'),'utf8')).includes('prefers-reduced-motion')
  ? ok('respeta prefers-reduced-motion') : fallo('no respeta prefers-reduced-motion');

/* ─────────────────────────────────────────────────────────────
   6. SEO
   ───────────────────────────────────────────────────────────── */
titulo('6. SEO');
const titulos = new Set(), descripciones = new Set();
for (const p of PAGINAS) {
  const t = html[p].match(/<title>(.*?)<\/title>/)?.[1];
  const d = html[p].match(/name="description" content="(.*?)"/)?.[1];
  const c = html[p].match(/rel="canonical" href="(.*?)"/)?.[1];
  t ? titulos.add(t) : fallo(`${p}: sin <title>`);
  d ? descripciones.add(d) : fallo(`${p}: sin description`);
  c ? ok(`${p}: canonical -> ${c.replace('https://pestalozzi-opal.vercel.app','')}`)
    : fallo(`${p}: sin canonical`);
}
titulos.size === PAGINAS.length ? ok('4 títulos únicos') : fallo(`solo ${titulos.size} títulos únicos`);
descripciones.size === PAGINAS.length ? ok('4 descripciones únicas') : fallo(`solo ${descripciones.size} únicas`);

for (const p of PAGINAS) {
  const img = html[p].match(/property="og:image" content="(.*?)"/)?.[1];
  // Relativa: los scrapers de Facebook/WhatsApp/X no siempre la resuelven
  // contra la URL de la página y la vista previa sale sin imagen.
  img?.startsWith('https://') ? ok(`${p}: og:image absoluta`) : fallo(`${p}: og:image relativa (${img})`);
  html[p].includes('og:description') ? ok(`${p}: og:description presente`) : fallo(`${p}: falta og:description`);
  html[p].includes('name="twitter:card"') ? ok(`${p}: twitter:card presente`) : fallo(`${p}: falta twitter:card`);
}

try {
  const robots = await readFile(join(dist, 'robots.txt'), 'utf8');
  robots.includes('Sitemap:') ? ok('robots.txt: declara sitemap') : fallo('robots.txt: no declara sitemap');
} catch { fallo('robots.txt: no existe en dist/'); }

try {
  const sitemap = await readFile(join(dist, 'sitemap.xml'), 'utf8');
  const urls = (sitemap.match(/<loc>/g) || []).length;
  urls === PAGINAS.length ? ok(`sitemap.xml: ${urls} URLs`) : fallo(`sitemap.xml: ${urls} URLs, esperaba ${PAGINAS.length}`);
} catch { fallo('sitemap.xml: no existe en dist/'); }

/* ─────────────────────────────────────────────────────────────
   7. PRESUPUESTO DE RENDIMIENTO
   ───────────────────────────────────────────────────────────── */
titulo('7. Presupuesto de rendimiento');
const TOPES = { portadaGzip: 60, jsPropio: 30, videoTotal: 2600, imagenHero: 200 };

const idx = await readFile(join(dist, 'index.html'));
const idxGz = gzipSync(idx).length / 1024;
idxGz < TOPES.portadaGzip ? ok(`portada (HTML+CSS) ${idxGz.toFixed(1)} KB gzip < ${TOPES.portadaGzip}`)
                          : fallo(`portada ${idxGz.toFixed(1)} KB gzip supera ${TOPES.portadaGzip}`);

const jsKb = (await stat(join(dist, 'js', 'main.js'))).size / 1024;
jsKb < TOPES.jsPropio ? ok(`js propio ${jsKb.toFixed(1)} KB < ${TOPES.jsPropio}`)
                      : fallo(`js propio ${jsKb.toFixed(1)} KB supera ${TOPES.jsPropio}`);

let video = 0;
for (const v of ['hero-escritorio.mp4', 'hero-movil.mp4']) {
  try { video += (await stat(join(dist, 'img', 'hero', v))).size / 1024; } catch {}
}
video < TOPES.videoTotal ? ok(`videos ${(video/1024).toFixed(2)} MB < ${(TOPES.videoTotal/1024).toFixed(1)} MB`)
                         : fallo(`videos ${(video/1024).toFixed(2)} MB se pasan`);

const hero = (await stat(join(dist, 'img', 'hero', 'portada.webp'))).size / 1024;
hero < TOPES.imagenHero ? ok(`imagen de portada ${hero.toFixed(0)} KB < ${TOPES.imagenHero}`)
                        : fallo(`imagen de portada ${hero.toFixed(0)} KB se pasa`);

/* ─────────────────────────────────────────────────────────────
   8. CLS — toda imagen con dimensiones
   ───────────────────────────────────────────────────────────── */
titulo('8. Estabilidad visual (CLS)');
for (const p of PAGINAS) {
  const imgs = (html[p].match(/<img[^>]*>/g) || [])
    .filter((i) => !i.includes('hero__foto') && !i.includes('franja__foto') && !i.includes('src=""'));
  const sinDim = imgs.filter((i) => !/width=/.test(i) || !/height=/.test(i));
  sinDim.length === 0 ? ok(`${p}: todas las imágenes con width/height`)
                      : fallo(`${p}: ${sinDim.length} sin dimensiones`);
}

/* ─────────────────────────────────────────────────────────────
   9. CONFIGURACIÓN DE DESPLIEGUE
   ───────────────────────────────────────────────────────────── */
titulo('9. Configuración de Vercel');
const vercel = JSON.parse(await readFile(resolve(dist, '..', 'vercel.json'), 'utf8'));
const PERMITIDAS = new Set(['framework','buildCommand','outputDirectory','trailingSlash','cleanUrls',
  'rewrites','redirects','headers','routes','installCommand','devCommand','regions','public',
  'github','functions','images','crons','ignoreCommand']);
const malas = Object.keys(vercel).filter((k) => !PERMITIDAS.has(k));
malas.length === 0 ? ok('vercel.json: todas las claves son válidas')
                   : fallo(`vercel.json: claves inválidas -> ${malas} (el deploy fallará)`);
vercel.outputDirectory === 'dist' ? ok('outputDirectory = dist') : fallo(`outputDirectory = ${vercel.outputDirectory}`);
(vercel.rewrites || []).length === 3 ? ok('3 rewrites de rutas limpias') : fallo('faltan rewrites');
!('css' in (await readdir(dist))) ? ok('carpeta css/ eliminada del build') : aviso('css/ sigue en dist');


/* ─────────────────────────────────────────────────────────────
   10. EFECTOS
   ───────────────────────────────────────────────────────────── */
titulo('10. Efectos');
const jsFinal = await readFile(join(dist, 'js', 'main.js'), 'utf8');
const cssInline = (html['index.html'].match(/<style>([\s\S]*?)<\/style>/g) || []).join('');

const efectos = [
  ['cortina direccional',            cssInline.includes('data-cortina'),      html['index.html'].includes('data-cortina')],
  ['revelado al scroll',             cssInline.includes('data-revelar'),      jsFinal.includes('data-revelar')],
  ['marquesina',                     cssInline.includes('marquesina__pista'), html['index.html'].includes('marquesina')],
  ['boton de contacto flotante',     cssInline.includes('fab__opcion'),       html['index.html'].includes('class="fab"')],
  ['parallax de galeria',            true,                                    jsFinal.includes('DESPLAZAMIENTO')],
];
for (const [nombre, enCss, enHtml] of efectos) {
  (enCss && enHtml) ? ok(`${nombre}: CSS y marcado presentes`)
                    : fallo(`${nombre}: falta ${!enCss ? 'CSS' : 'marcado'}`);
}
jsFinal.includes('prefers-reduced-motion')
  ? ok('los efectos respetan reduced-motion') : fallo('efectos sin guarda de reduced-motion');
jsFinal.includes('punteroFino && pantallaAncha')
  ? ok('los efectos pesados no llegan al celular') : fallo('efectos pesados sin guarda de escritorio');

/* ─────────────────────────────────────────────────────────────
   11. PENDIENTES DEL CLIENTE
   ───────────────────────────────────────────────────────────── */
titulo('11. Pendientes antes de cobrar y entregar');
const idxHtml = html['index.html'], nosHtml = html['nosotros.html'];
idxHtml.includes('593998246396') ? ok('Teléfono: oficial confirmado y publicado (099 824 6396)')
                                  : aviso('Teléfono: falta el número oficial');
// OJO: buscar la palabra "pendiente" daba falso verde cuando se quitaba el
// marcador pero el dato seguia sin confirmar. Ahora se verifica el HECHO.
idxHtml.includes('Educación General Básica')
  ? aviso('Niveles: la página AFIRMA Inicial y EGB, y el colegio nunca lo confirmó por escrito')
  : ok('Niveles confirmados por la institución');
/<h2[^>]*>\s*Misión|Misión y visión/.test(nosHtml)
  ? aviso('Misión y visión: revisar que el texto sea el oficial')
  : aviso('Misión y visión: la sección NO existe en Nosotros');
// Alto real vía `magick identify`: un parseo manual del binario WebP
// (leer bytes 26-27) daba 640 para TODAS las fotos — el campo equivocado,
// falso positivo silencioso. Mejor delegar a una herramienta que ya sabe
// leer el formato en vez de reinventarlo mal.
const { execFileSync } = await import('node:child_process');
const galFotos = (await readdir(join(dist, 'img', 'galeria'))).filter((n) => n.endsWith('.webp'));
let videoFrames = 0, realesGaleria = 0;
for (const f of galFotos) {
  const ruta = join(dist, 'img', 'galeria', f);
  const h = Number(execFileSync('magick', ['identify', '-format', '%h', ruta], { encoding: 'utf8' }).trim());
  (h > 1000 ? videoFrames++ : realesGaleria++);
}
if (videoFrames > 0) aviso(`Fotos: ${videoFrames} de ${galFotos.length} siguen siendo fotogramas de video (720px vertical), solo para demo`);
if (realesGaleria > 0) ok(`Fotos: ${realesGaleria} foto(s) real(es) ya entregada(s) por el colegio`);
aviso('Logo: PNG recortado del video; el verde definitivo depende del vectorial');

/* ─────────────────────────────────────────────────────────────
   12. REEMPLAZO DE CONTENIDO SIN TOCAR CÓDIGO
   ───────────────────────────────────────────────────────────── */
titulo('12. Facilidad de reemplazo');
const galeriaImgs = (await readdir(join(dist, 'img', 'galeria'))).filter((f) => f.endsWith('.webp')).sort();
/^g\d{2}\.webp$/.test(galeriaImgs[0] || '')
  ? ok(`galería correlativa: ${galeriaImgs.length} fotos (${galeriaImgs[0]} … ${galeriaImgs[galeriaImgs.length-1]})`)
  : aviso('los nombres de galería no siguen patrón correlativo');
const heroImgs = (await readdir(join(dist, 'img', 'hero'))).filter((f) => f.endsWith('.webp'));
heroImgs.length >= 4 ? ok(`una portada por página (${heroImgs.length} imágenes)`)
                     : aviso(`solo ${heroImgs.length} portadas`);
((cssInline.match(/--font-\w+:/g) || []).length >= 2)
  ? ok('tipografía en variables: se cambia en un solo lugar')
  : fallo('tipografía no centralizada');
((cssInline.match(/--green-\d+:/g) || []).length >= 6)
  ? ok('paleta en variables: se cambia en un solo lugar')
  : fallo('colores no centralizados');

/* ─────────────────────────────────────────────────────────────
   13. CONTRASTE (WCAG AA) — texto sobre velo de foto/video
   El video del hero y la foto de la franja son fotogramas que
   cambian; si algún fotograma es claro (cielo, pared blanca) el
   velo tiene que seguir garantizando AA. Se recalcula sobre el
   peor caso real: el mismo tinte, mezclado contra blanco puro.
   ───────────────────────────────────────────────────────────── */
titulo('13. Contraste (WCAG AA) sobre fondos de foto');
function lin(c) { c /= 255; return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4; }
function luminancia([r, g, b]) { return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b); }
function contraste(a, b) {
  const [l1, l2] = [luminancia(a), luminancia(b)].sort((x, y) => y - x);
  return (l1 + 0.05) / (l2 + 0.05);
}
function mezclar(fg, alpha, bg) { return fg.map((c, i) => Math.round(c * alpha + bg[i] * (1 - alpha))); }
function hexA(h) { h = h.replace('#', ''); return [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16)); }

const BLANCO = [255, 255, 255];

function peorAlphaDe(gradienteCss) {
  // Toma el alpha MÁS BAJO entre los stops rgba(...) del gradiente:
  // ese es el punto donde menos tapa la foto de fondo.
  const alphas = [...gradienteCss.matchAll(/rgba\([^)]+,\s*([\d.]+)\)/g)].map((m) => Number(m[1]));
  return Math.min(...alphas);
}

const veloHeroCss = cssInline.match(/\.hero__velo\s*{[^}]*}/)?.[0] || '';
const veloFranjaCss = [...cssInline.matchAll(/\.franja__velo\s*{[^}]*}/g)].pop()?.[0] || '';

if (veloHeroCss) {
  const peor = mezclar([11, 74, 38], peorAlphaDe(veloHeroCss), BLANCO);
  const rBlanco = contraste(BLANCO, peor);
  rBlanco >= 4.5 ? ok(`velo del hero, peor punto: texto blanco ${rBlanco.toFixed(2)}:1`)
                 : fallo(`velo del hero, peor punto: texto blanco ${rBlanco.toFixed(2)}:1 (min 4.5)`);
} else {
  fallo('no se encontró .hero__velo en el CSS embebido');
}

if (veloFranjaCss) {
  // El 100% del gradiente de franja es intencionalmente claro (sin
  // texto encima, ver comentario en componentes.css) — se evalúa el
  // segundo stop (42%), que es donde vive el párrafo.
  const alphas = [...veloFranjaCss.matchAll(/rgba\([^)]+,\s*([\d.]+)\)/g)].map((m) => Number(m[1]));
  const alphaConTexto = alphas[1];
  const peor = mezclar([11, 74, 38], alphaConTexto, BLANCO);
  const green100 = hexA('D7F0E2');
  const r = contraste(green100, peor);
  r >= 4.5 ? ok(`velo de franja, lado con texto: green-100 ${r.toFixed(2)}:1`)
           : fallo(`velo de franja, lado con texto: green-100 ${r.toFixed(2)}:1 (min 4.5)`);
} else {
  fallo('no se encontró .franja__velo en el CSS embebido');
}

const fabWa = cssInline.match(/\.fab__opcion--wa\s*{\s*background:\s*(#[0-9a-fA-F]{6})/)?.[1];
if (fabWa) {
  const r = contraste(BLANCO, hexA(fabWa));
  // Icono, no texto: el umbral que aplica es 3:1 (WCAG 1.4.11).
  r >= 3.0 ? ok(`icono del FAB de WhatsApp: ${r.toFixed(2)}:1`)
           : fallo(`icono del FAB de WhatsApp: ${r.toFixed(2)}:1 (min 3.0)`);
} else {
  fallo('no se encontró el color del FAB de WhatsApp');
}

const eyebrowHero = cssInline.match(/\.hero__contenido \.eyebrow\s*{\s*color:\s*var\(--(\S+?)\)/)?.[1];
eyebrowHero === 'accent-100' || eyebrowHero === 'accent-500' || eyebrowHero === 'green-300'
  ? (eyebrowHero === 'accent-100'
      ? ok(`eyebrow del hero usa --accent-100 (pasa AA sobre el velo)`)
      : fallo(`eyebrow del hero volvió a --${eyebrowHero}, no pasa AA sobre el velo`))
  : aviso('no se pudo verificar el color del eyebrow del hero');

/* ───────────────────────────────────────────────────────────── */
console.log(`\n\x1b[1mRESULTADO\x1b[0m  \x1b[32m${pasadas} pasadas\x1b[0m  \x1b[31m${fallidas} fallidas\x1b[0m  \x1b[33m${avisos} avisos\x1b[0m`);
process.exit(fallidas > 0 ? 1 : 0);
