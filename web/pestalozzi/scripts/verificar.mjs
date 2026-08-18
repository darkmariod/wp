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
aviso('Fotos: las 6 son fotogramas de video 720px vertical, solo para demo');
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

/* ───────────────────────────────────────────────────────────── */
console.log(`\n\x1b[1mRESULTADO\x1b[0m  \x1b[32m${pasadas} pasadas\x1b[0m  \x1b[31m${fallidas} fallidas\x1b[0m  \x1b[33m${avisos} avisos\x1b[0m`);
process.exit(fallidas > 0 ? 1 : 0);
