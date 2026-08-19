import { defineConfig } from 'astro/config';

// CSS embebido en cada página: es la razón por la que el sitio no
// tenía el destello de "HTML sin estilos" al navegar (antes lo hacía
// a mano scripts/build.mjs). Astro lo hace nativo con esta opción.
export default defineConfig({
  site: 'https://pestalozzi-opal.vercel.app',
  trailingSlash: 'never',
  build: {
    inlineStylesheets: 'always',
  },
});
