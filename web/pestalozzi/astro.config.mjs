import { defineConfig } from 'astro/config';
import sanity from '@sanity/astro';
import react from '@astrojs/react';

// CSS embebido en cada página: es la razón por la que el sitio no
// tenía el destello de "HTML sin estilos" al navegar (antes lo hacía
// a mano scripts/build.mjs). Astro lo hace nativo con esta opción.
export default defineConfig({
  site: 'https://pestalozzi-opal.vercel.app',
  trailingSlash: 'never',
  build: {
    inlineStylesheets: 'always',
  },
  integrations: [
    sanity({
      projectId: '513m7736',
      dataset: 'production',
      // El sitio se construye en Vercel y se sirve como HTML estático:
      // el contenido se lee en el build, no en cada visita. Por eso la
      // CDN de Sanity está bien (más rápida y más barata que la API).
      useCdn: true,
      // Studio embebido en /admin — es donde entra el colegio a editar.
      studioBasePath: '/admin',
    }),
    react(),
  ],
});
