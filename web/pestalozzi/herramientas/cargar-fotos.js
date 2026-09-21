// Sube en bloque una carpeta de fotos a la galería (Sanity).
// Lo llama cargar-fotos.sh — no se corre directo.
//
// El nombre de la carpeta es el nombre de la categoría: si ya existe
// (sin importar mayúsculas ni tildes) se usa esa; si no, se crea sola.
// Cada imagen se sube tal cual está, en el orden alfabético del
// nombre de archivo.

const fs = require('fs');
const path = require('path');

const TOKEN = process.env.SANITY_TOKEN;
const PROYECTO = '513m7736';
const DATASET = 'production';
const EXTENSIONES = { '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.png': 'image/png', '.webp': 'image/webp' };

const carpeta = process.argv[2];
if (!carpeta || !fs.existsSync(carpeta)) {
  console.error('Uso: cargar-fotos.js <carpeta-de-fotos>');
  process.exit(1);
}

const nombreCategoria = path.basename(path.resolve(carpeta));
const archivos = fs.readdirSync(carpeta)
  .filter((f) => EXTENSIONES[path.extname(f).toLowerCase()])
  .sort();

if (!archivos.length) {
  console.log(`No hay fotos (.jpg, .jpeg, .png, .webp) en "${carpeta}".`);
  process.exit(1);
}

function normalizar(s) {
  return s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
}

function altDesdeArchivo(nombre) {
  const sinExtension = nombre.replace(/\.[^.]+$/, '');
  const limpio = sinExtension.replace(/[-_]+/g, ' ').replace(/\b\d+\b/g, '').replace(/\s+/g, ' ').trim();
  if (limpio.length < 4) return `Foto de ${nombreCategoria}`;
  return limpio.charAt(0).toUpperCase() + limpio.slice(1);
}

async function api(ruta, opciones = {}) {
  const r = await fetch(`https://${PROYECTO}.api.sanity.io${ruta}`, {
    ...opciones,
    headers: { Authorization: `Bearer ${TOKEN}`, ...(opciones.headers || {}) },
  });
  const contentType = r.headers.get('content-type') || '';
  const cuerpo = contentType.includes('application/json') ? await r.json() : await r.text();
  if (!r.ok) {
    const detalle = typeof cuerpo === 'string' ? cuerpo : (cuerpo.error?.description || cuerpo.message || JSON.stringify(cuerpo));
    throw new Error(detalle);
  }
  return cuerpo;
}

async function principal() {
  console.log(`Categoría: "${nombreCategoria}" · ${archivos.length} foto(s)`);
  archivos.forEach((f) => console.log('  · ' + f));
  console.log('');

  // 1) Categoría: reusar si ya existe, crearla si no.
  const query = encodeURIComponent('*[_type=="categoriaGaleria"]{_id, "nombre": nombre.es, orden}');
  const { result: categorias } = await api(`/v2024-01-01/data/query/${DATASET}?query=${query}`);
  let categoria = categorias.find((c) => normalizar(c.nombre) === normalizar(nombreCategoria));

  if (categoria) {
    console.log(`Categoría existente: ${categoria.nombre}`);
  } else {
    const ordenMax = categorias.reduce((m, c) => Math.max(m, c.orden || 0), 0);
    const idCategoria = 'categoria-' + normalizar(nombreCategoria).replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const doc = {
      _id: idCategoria,
      _type: 'categoriaGaleria',
      orden: ordenMax + 10,
      nombre: { _type: 'textoLocalizado', es: nombreCategoria, en: nombreCategoria },
      descripcion: {
        _type: 'parrafoLocalizado',
        es: `Fotos de ${nombreCategoria.toLowerCase()}.`,
        en: `Photos of ${nombreCategoria.toLowerCase()}.`,
      },
    };
    await api(`/v2024-01-01/data/mutate/${DATASET}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mutations: [{ createIfNotExists: doc }] }),
    });
    categoria = { _id: idCategoria, nombre: nombreCategoria };
    console.log(`Categoría nueva creada: "${nombreCategoria}" — revisá su descripción en el Studio, quedó genérica.`);
  }

  // 2) Próximo orden disponible entre las fotos ya cargadas.
  const { result: fotos } = await api(`/v2024-01-01/data/query/${DATASET}?query=${encodeURIComponent('*[_type=="foto"]{orden}')}`);
  let orden = fotos.reduce((m, f) => Math.max(m, f.orden || 0), 0);

  // 3) Subir cada imagen y crear su documento "foto".
  for (const archivo of archivos) {
    const rutaCompleta = path.join(carpeta, archivo);
    const buffer = fs.readFileSync(rutaCompleta);
    const mime = EXTENSIONES[path.extname(archivo).toLowerCase()];

    const asset = await api(`/v2024-01-01/assets/images/${DATASET}`, {
      method: 'POST',
      headers: { 'Content-Type': mime },
      body: buffer,
    });

    orden += 10;
    const alt = altDesdeArchivo(archivo);
    const docFoto = {
      _type: 'foto',
      imagen: { _type: 'imagenConAlt', alt, asset: { _type: 'reference', _ref: asset.document._id } },
      categoria: { _type: 'reference', _ref: categoria._id },
      orden,
    };
    await api(`/v2024-01-01/data/mutate/${DATASET}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mutations: [{ create: docFoto }] }),
    });
    console.log(`  ✓ ${archivo} → "${alt}" (orden ${orden})`);
  }

  console.log('');
  console.log(`Listo. ${archivos.length} foto(s) publicadas en "${categoria.nombre || nombreCategoria}". El sitio se reconstruye solo en 1-2 minutos.`);
}

principal().catch((e) => { console.error('ERROR: ' + e.message); process.exit(1); });
