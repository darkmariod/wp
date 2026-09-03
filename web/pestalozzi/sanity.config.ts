import { defineConfig } from 'sanity';
import { structureTool } from 'sanity/structure';
import { visionTool } from '@sanity/vision';

import { schemaTypes } from './src/sanity/schemaTypes';

// Documentos de los que existe UNO solo. Se listan como enlace directo,
// no como carpeta con botón de "crear nuevo": el colegio edita la página
// de Inicio, no crea varias páginas de Inicio.
const UNICOS = [
  { id: 'configuracion', titulo: 'Datos del colegio', icono: '🏫' },
  { id: 'paginaInicio', titulo: 'Página: Inicio', icono: '🏠' },
  { id: 'paginaNosotros', titulo: 'Página: Nosotros', icono: '📖' },
  { id: 'paginaGaleria', titulo: 'Página: Galería', icono: '🖼️' },
  { id: 'paginaContacto', titulo: 'Página: Contacto', icono: '✉️' },
  { id: 'paginaVoluntariado', titulo: 'Página: Voluntariado', icono: '🤝' },
];

export default defineConfig({
  name: 'pestalozzi',
  title: 'Pestalozzi Ambato',
  projectId: '513m7736',
  dataset: 'production',
  schema: { types: schemaTypes },
  tools: (herramientas, { currentUser }) => {
    // Vision es una consola de consultas para desarrollo. El personal del
    // colegio no la necesita y solo agrega ruido: se muestra únicamente a
    // administradores.
    const esAdmin = currentUser?.roles?.some((r) => r.name === 'administrator');
    return esAdmin ? herramientas : herramientas.filter((h) => h.name !== 'vision');
  },
  plugins: [
    structureTool({
      title: 'Contenido',
      structure: (S) =>
        S.list()
          .title('Contenido del sitio')
          .items([
            ...UNICOS.map(({ id, titulo, icono }) =>
              S.listItem()
                .title(titulo)
                .id(id)
                .child(S.document().schemaType(id).documentId(id).title(titulo))
            ),
            S.divider(),
            S.documentTypeListItem('foto').title('Fotos de la galería'),
          ]),
    }),
    visionTool(),
  ],
  document: {
    // Sin "duplicar" ni "borrar" en los documentos únicos: si el colegio
    // borra la página de Inicio por accidente, el sitio se queda sin
    // contenido hasta que alguien la recree a mano.
    actions: (acciones, { schemaType }) =>
      UNICOS.some((u) => u.id === schemaType)
        ? acciones.filter(({ action }) => action !== 'duplicate' && action !== 'delete')
        : acciones,
  },
});
