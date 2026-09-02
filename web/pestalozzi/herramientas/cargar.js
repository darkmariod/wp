// Lee contenido.txt y lo publica en el sitio (Sanity).
// Lo llama cargar.sh — no se corre directo.
//
// Lo que se deja vacío NO se toca: no pisa lo que ya esté cargado.

const fs = require('fs');

const TOKEN = process.env.SANITY_TOKEN;
const PROYECTO = '513m7736';
const DATASET = 'production';

const texto = fs.readFileSync(process.argv[2], 'utf8');
const lineas = texto.split('\n');

const niveles = [];
const datos = [];
const sueltos = {};

for (const cruda of lineas) {
  const linea = cruda.trim();
  if (!linea || linea.startsWith('#')) continue;

  if (linea.startsWith('NIVEL:')) {
    const p = linea.slice(6).split('|').map(s => s.trim());
    // Sin nombre o sin edades no sirve: se salta en vez de publicar a medias.
    if (!p[0] || !p[1]) continue;
    const puntos = (p[3] || '').split(';').map(s => s.trim()).filter(Boolean);
    niveles.push({
      _key: 'n' + (niveles.length + 1),
      _type: 'programa',
      nombre: p[0],
      edades: p[1],
      descripcion: p[2] || p[0],
      incluye: puntos.length ? puntos : [p[1]],
    });
  } else if (linea.startsWith('DATO:')) {
    const p = linea.slice(5).split('|').map(s => s.trim());
    if (!p[0] || !p[1]) continue;   // sin valor real, no se publica
    const d = { _key: 'd' + (datos.length + 1), _type: 'datoPractico', etiqueta: p[0], valor: p[1] };
    if (p[2]) d.detalle = p[2];
    datos.push(d);
  } else if (linea.includes(':')) {
    const i = linea.indexOf(':');
    const clave = linea.slice(0, i).trim();
    const valor = linea.slice(i + 1).trim();
    if (valor) sueltos[clave] = valor;
  }
}

const set = {};
if (niveles.length) set.programas = niveles;
if (datos.length) set.practicoDatos = datos;
if (sueltos.TITULO_NIVELES) set.programasTitulo = sueltos.TITULO_NIVELES;
if (sueltos.TEXTO_NIVELES) set.programasTexto = sueltos.TEXTO_NIVELES;
if (sueltos.TITULO_DATOS) set.practicoTitulo = sueltos.TITULO_DATOS;

if (!Object.keys(set).length) {
  console.log('No hay nada que cargar: el archivo está vacío.');
  console.log('Completá al menos un NIVEL o un DATO y volvé a intentar.');
  process.exit(1);
}

console.log('  Niveles a publicar: ' + niveles.length);
niveles.forEach(n => console.log('    · ' + n.nombre + ' (' + n.edades + ')'));
console.log('  Datos prácticos: ' + datos.length);
datos.forEach(d => console.log('    · ' + d.etiqueta + ': ' + d.valor));
console.log('');

fetch(`https://${PROYECTO}.api.sanity.io/v2024-01-01/data/mutate/${DATASET}`, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${TOKEN}`, 'Content-Type': 'application/json' },
  body: JSON.stringify({ mutations: [{ patch: { id: 'paginaInicio', set } }] }),
})
  .then(r => r.json())
  .then(r => {
    if (r.error) { console.error('ERROR de Sanity: ' + (r.error.description || r.error)); process.exit(1); }
    console.log('PUBLICADO. El sitio se reconstruye solo en 1-2 minutos.');
  })
  .catch(e => { console.error('ERROR: ' + e.message); process.exit(1); });
