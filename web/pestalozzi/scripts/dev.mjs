// Servidor de desarrollo que imita a Vercel con cleanUrls activado.
// python -m http.server NO resuelve /nosotros -> nosotros.html, así que en
// local salían 404 en enlaces que en producción funcionan bien.
// Uso: node scripts/dev.mjs   (o `npm run dev`)
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { dirname, extname, join, normalize, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const puerto = Number(process.env.PORT) || 4173;

const TIPOS = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.webp': 'image/webp',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.mp4': 'video/mp4',
  '.ico': 'image/x-icon',
};

async function existeArchivo(ruta) {
  try {
    const s = await stat(ruta);
    return s.isFile() ? ruta : null;
  } catch {
    return null;
  }
}

// Misma resolución que Vercel con cleanUrls: /nosotros -> nosotros.html
async function resolverRuta(urlPath) {
  const limpio = normalize(decodeURIComponent(urlPath.split('?')[0])).replace(/^(\.\.[/\\])+/, '');
  const base = join(raiz, limpio);

  if (limpio === '/' || limpio === '\\') return existeArchivo(join(raiz, 'index.html'));
  if (extname(base)) return existeArchivo(base);

  return (await existeArchivo(base + '.html'))
      || (await existeArchivo(join(base, 'index.html')));
}

createServer(async (req, res) => {
  const archivo = await resolverRuta(req.url || '/');

  if (!archivo) {
    res.writeHead(404, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end('<h1>404</h1><p>No existe: ' + (req.url || '') + '</p>');
    return;
  }

  const cuerpo = await readFile(archivo);
  res.writeHead(200, {
    'Content-Type': TIPOS[extname(archivo)] || 'application/octet-stream',
    'Cache-Control': 'no-store',   // en desarrollo nunca cachear
  });
  res.end(cuerpo);
}).listen(puerto, '127.0.0.1', () => {
  console.log(`Pestalozzi en http://127.0.0.1:${puerto}  (cleanUrls como en Vercel)`);
});
