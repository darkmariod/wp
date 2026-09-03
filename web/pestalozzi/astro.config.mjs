import { defineConfig } from 'astro/config';
// React queda SOLO por el panel del CMS en /admin: Sanity Studio es
// una aplicación React y no arranca sin esto. Las páginas públicas no
// llevan un solo componente React — sus animaciones son GSAP directo
// (ver public/js/componentes.js).
import react from '@astrojs/react';
import sanity from '@sanity/astro';

// CSS embebido en cada página: es la razón por la que el sitio no
// tenía el destello de "HTML sin estilos" al navegar (antes lo hacía
// a mano scripts/build.mjs). Astro lo hace nativo con esta opción.
export default defineConfig({
  site: 'https://pestalozzi-opal.vercel.app',
  trailingSlash: 'never',
  build: {
    inlineStylesheets: 'always',
  },
  // Español sin prefijo (pestalozziambato.com/nosotros), inglés bajo
  // /en (pestalozziambato.com/en/nosotros). prefixDefaultLocale:false
  // es lo que deja al español así, sin /es/ delante — es el idioma
  // que ya estaba indexado en Google.
  i18n: {
    defaultLocale: 'es',
    locales: ['es', 'en'],
    routing: { prefixDefaultLocale: false },
  },
  integrations: [
    react(),
    sanity({
      projectId: '513m7736',
      dataset: 'production',
      // false a propósito. El contenido se lee UNA vez por build, no en
      // cada visita, así que la CDN no ahorra nada — y sí rompe: cachea
      // unos segundos, y como el webhook dispara el build apenas se
      // publica, el build alcanzaba a leer la versión anterior. El
      // cambio quedaba congelado hasta la siguiente publicación.
      useCdn: false,
      // Studio embebido en /admin — es donde entra el colegio a editar.
      studioBasePath: '/admin',
    }),
  ],
});
