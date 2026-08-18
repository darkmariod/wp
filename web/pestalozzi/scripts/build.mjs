// Genera dist/ listo para producción.
//
// El sitio es vanilla, pero el build hace UNA cosa que importa: mete todo el
// CSS dentro del HTML.
//
// Por qué: con hojas de estilo enlazadas, el navegador no puede pintar hasta
// que llegan todas. En esa ventana Firefox alcanza a mostrar el HTML sin
// estilos — el "html crudo" que se veía al cambiar de página. Con el CSS
// dentro del documento no existe esa ventana: es imposible por construcción.
//
// El código fuente conserva los 4 CSS separados para poder editarlos.
//
// Uso: node scripts/build.mjs   (o `npm run build`)
import { cp, mkdir, readdir, readFile, rm, writeFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const dist = resolve(raiz, 'dist');

// Nunca van a producción:
const excluidos = new Set([
  'dist',
  '_vanilla-respaldo',
  '_media-originales',
  'scripts',
  'PLAN-DESARROLLO.md',
  'PLAN-EFECTOS.md',
  'PLAN-CHATBOT.md',
  'package.json',
  '.gitignore',
  '.DS_Store',
  '.vercelignore',
  'node_modules',
  '.git',
]);

// Orden de cascada: tokens define variables que el resto usa.
const ORDEN_CSS = ['tokens.css', 'base.css', 'componentes.css', 'animaciones.css'];

await rm(dist, { recursive: true, force: true });
await mkdir(dist, { recursive: true });

for (const entrada of await readdir(raiz, { withFileTypes: true })) {
  if (excluidos.has(entrada.name)) continue;
  await cp(join(raiz, entrada.name), join(dist, entrada.name), { recursive: true });
}

// --- Une el CSS en un solo bloque ---
let css = '';
for (const archivo of ORDEN_CSS) {
  css += `\n/* ${archivo} */\n` + await readFile(join(raiz, 'css', archivo), 'utf8');
}
// Compresión conservadora: comentarios y espacio sobrante. No toca selectores
// ni valores, así que no puede romper una regla.
const cssMin = css
  .replace(/\/\*[\s\S]*?\*\//g, '')
  .replace(/\n{2,}/g, '\n')
  .replace(/[ \t]{2,}/g, ' ')
  .trim();

// --- Reemplaza los <link> por el <style> en cada página ---
const paginas = (await readdir(dist)).filter((f) => f.endsWith('.html'));
for (const pagina of paginas) {
  const ruta = join(dist, pagina);
  let html = await readFile(ruta, 'utf8');

  const enlaces = html.match(/[ \t]*<link rel="stylesheet" href="css\/[^"]+">\n?/g) || [];
  if (enlaces.length === 0) {
    console.warn(`  aviso: ${pagina} no tenia <link> de CSS local`);
    continue;
  }

  // El primero se convierte en el bloque completo; los demás desaparecen.
  html = html.replace(enlaces[0], `<style>${cssMin}</style>\n`);
  for (const sobrante of enlaces.slice(1)) html = html.replace(sobrante, '');

  await writeFile(ruta, html);
  console.log(`  ${pagina}: ${enlaces.length} <link> -> 1 <style> (${(cssMin.length / 1024).toFixed(1)} KB)`);
}

// El CSS suelto ya no hace falta en producción.
await rm(join(dist, 'css'), { recursive: true, force: true });

console.log('dist/ generado en', dist);
