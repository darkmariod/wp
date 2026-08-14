// Genera dist/ con solo lo público (sin build real: el sitio es vanilla).
// Uso: node scripts/build.mjs  (o `npm run build`)
import { cp, mkdir, readdir, rm } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const dist = resolve(raiz, 'dist');

// Nunca van a producción:
const excluidos = new Set([
  'dist',            // la salida misma
  '_vanilla-respaldo', // versión anterior (solo referencia)
  '_media-originales', // videos sin comprimir: 4.9 MB que no deben salir a producción
  'scripts',         // herramientas de desarrollo
  'PLAN-DESARROLLO.md',
  'package.json',    // si va en dist/, Vercel intenta ejecutar "build" y falla
  '.gitignore',
  '.DS_Store',
  '.vercelignore',
  'node_modules',
  '.git',
]);

await rm(dist, { recursive: true, force: true });
await mkdir(dist, { recursive: true });

const entradas = await readdir(raiz, { withFileTypes: true });
for (const entrada of entradas) {
  if (excluidos.has(entrada.name)) continue;
  await cp(join(raiz, entrada.name), join(dist, entrada.name), { recursive: true });
}

console.log('dist/ generado en', dist);
