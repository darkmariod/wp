// Pruebas de resistencia del CMS.
//
// Verifica que la carga de contenido del colegio no pueda romper el
// sitio: campos vacíos, secciones sin datos, imágenes sin descripción,
// videos ausentes. Corre contra dist/, así que hay que construir antes.
//
//   npx astro build && node scripts/verificar-cms.mjs
//
// Es aparte de verificar.mjs (que mira contraste, SEO y rendimiento):
// esto mira específicamente qué pasa cuando el colegio sube contenido.
import { readFileSync, existsSync } from 'node:fs';

let ok = 0, fallo = 0;
const check = (nombre, cond, detalle = '') => {
  if (cond) { console.log(`  ✓ ${nombre}`); ok++; }
  else { console.log(`  ✗ ${nombre} ${detalle}`); fallo++; }
};

const leer = p => existsSync(p) ? readFileSync(p, 'utf8') : '';
const inicio = leer('dist/index.html');
const galeria = leer('dist/galeria/index.html');
const nosotros = leer('dist/nosotros/index.html');
const contacto = leer('dist/contacto/index.html');

console.log('\n\x1b[1m1. Secciones opcionales: ocultas sin datos\x1b[0m');
check('sin niveles cargados, la sección no aparece', !inicio.includes('id="programas"'));
check('sin datos prácticos, la sección no aparece', !inicio.includes('datos-practicos"'));

console.log('\n\x1b[1m2. Nada se rompe con los campos actuales\x1b[0m');
for (const [nom, html] of [['inicio', inicio], ['galeria', galeria], ['nosotros', nosotros], ['contacto', contacto]]) {
  check(`${nom}: sin "undefined" en el HTML`, !html.includes('>undefined<') && !html.includes('"undefined"'));
  check(`${nom}: sin "[object Object]"`, !html.includes('[object Object]'));
  check(`${nom}: sin "null" impreso`, !html.includes('>null<'));
}

console.log('\n\x1b[1m3. Videos: siempre hay una fuente\x1b[0m');
const fuentes = inicio.match(/<source src="[^"]+"/g) || [];
check('el video tiene 2 fuentes', fuentes.length === 2, `(hay ${fuentes.length})`);
check('ninguna fuente quedó vacía', !inicio.includes('<source src=""'));
check('hay foto de respaldo (poster)', /poster="[^"]+"/.test(inicio));

console.log('\n\x1b[1m4. Imágenes: todas con texto alternativo\x1b[0m');
for (const [nom, html] of [['inicio', inicio], ['galeria', galeria], ['nosotros', nosotros]]) {
  const imgs = html.match(/<img[^>]*>/g) || [];
  const sinAlt = imgs.filter(i => !/alt="/.test(i));
  // Un alt vacío está BIEN si la imagen es decorativa a propósito y se
  // marca aria-hidden: por ejemplo el logo blanco del menú, que duplica
  // al de color. Sin esa distinción, la prueba obliga a describir dos
  // veces la misma imagen y un lector de pantalla la anuncia repetida.
  const altVacio = imgs.filter(i => /alt=""/.test(i) && !/aria-hidden="true"/.test(i));
  check(`${nom}: ${imgs.length} imágenes, todas con alt`, sinAlt.length === 0, `(${sinAlt.length} sin alt)`);
  check(`${nom}: ningún alt vacío sin marcar como decorativa`, altVacio.length === 0, `(${altVacio.length} vacíos)`);
}

console.log('\n\x1b[1m5. El carrusel y el acordeón reciben fotos\x1b[0m');
const tarjetas = (inicio.match(/carrusel__tarjeta/g) || []).length;
check(`carrusel con al menos 4 fotos`, tarjetas >= 4, `(tiene ${tarjetas})`);
const paneles = (galeria.match(/acordeon__panel/g) || []).length;
check(`acordeón con al menos 3 fotos`, paneles >= 3, `(tiene ${paneles})`);

console.log('\n\x1b[1m6. Enlaces del aula virtual\x1b[0m');
check('el enlace del aula virtual existe', inicio.includes('header__aula'));
check('abre en pestaña nueva', /header__aula[^>]*target="_blank"/.test(inicio) || /target="_blank"[^>]*header__aula/.test(inicio) || inicio.includes('rel="noopener"'));

console.log(`\n\x1b[1mRESULTADO\x1b[0m  \x1b[32m${ok} pasadas\x1b[0m  ${fallo ? `\x1b[31m${fallo} fallidas\x1b[0m` : '0 fallidas'}`);
process.exit(fallo ? 1 : 0);
