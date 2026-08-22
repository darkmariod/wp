import { defineField, defineType } from 'sanity';

export const CATEGORIAS_GALERIA = [
  { title: 'Instalaciones', value: 'instalaciones' },
  { title: 'Actividades', value: 'actividades' },
  { title: 'Deportes', value: 'deportes' },
  { title: 'Eventos', value: 'eventos' },
];

// Cada foto de la galería es un documento suelto: así el colegio agrega
// una nueva sin tocar nada más, y el filtro de la galería se arma solo a
// partir de la categoría que elijan acá.
export const foto = defineType({
  name: 'foto',
  title: 'Foto de la galería',
  type: 'document',
  fields: [
    defineField({
      name: 'imagen',
      title: 'Imagen',
      type: 'imagenConAlt',
      description:
        'Arrastra la foto aquí o haz clic para subirla. Mientras más grande y nítida, mejor.',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'categoria',
      title: 'Categoría',
      type: 'string',
      description: 'Define en qué filtro de la galería aparece esta foto.',
      options: {
        list: CATEGORIAS_GALERIA,
        layout: 'radio',
      },
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'orden',
      title: 'Orden',
      type: 'number',
      description:
        'Las fotos se muestran de menor a mayor. Deja espacio entre números (10, 20, 30…) para poder intercalar después sin renumerar todo.',
      validation: (r) => r.required().integer().positive(),
    }),
  ],
  orderings: [
    {
      title: 'Orden de aparición',
      name: 'ordenAsc',
      by: [{ field: 'orden', direction: 'asc' }],
    },
  ],
  preview: {
    select: { media: 'imagen', alt: 'imagen.alt', categoria: 'categoria', orden: 'orden' },
    prepare: ({ media, alt, categoria, orden }) => {
      const nombre = CATEGORIAS_GALERIA.find((c) => c.value === categoria)?.title ?? 'Sin categoría';
      return {
        title: alt || 'Foto sin descripción',
        subtitle: `${nombre} · orden ${orden ?? '—'}`,
        media,
      };
    },
  },
});
