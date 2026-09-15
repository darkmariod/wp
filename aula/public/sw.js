// Service worker mínimo: solo lo necesario para que el navegador
// considere instalable la PWA. Sin caché agresivo — el aula depende de
// datos frescos (asistencia, evidencias), cachear páginas dinámicas a
// medias generaría más confusión que beneficio.
const CACHE = 'mi-escuelita-shell-v1';
const SHELL = ['/manifest.webmanifest', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

// Network-first: si no hay conexión, se avisa en vez de mostrar una
// página vieja con datos que ya no son ciertos.
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
