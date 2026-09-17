import { defineField, defineType } from 'sanity';

export const AMBIENTES_GALERIA = [
  { title: 'Mentes Absorbentes', value: 'mentes-absorbentes' },
  { title: 'Mentes Razonadoras', value: 'mentes-razonadoras' },
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
      type: 'reference',
      to: [{ type: 'categoriaGaleria' }],
      description:
        'En qué filtro de la galería aparece esta foto. Si la categoría que buscás no existe todavía, creala primero desde "Categoría de la galería".',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'ambiente',
      title: 'Ambiente',
      type: 'string',
      description:
        'Opcional: asocia la foto a un ambiente específico. Si se deja vacío, la foto aparece solo cuando el filtro de ambiente está en "Todas".',
      options: {
        list: AMBIENTES_GALERIA,
        layout: 'radio',
      },
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
    select: {
      media: 'imagen',
      alt: 'imagen.alt',
      nombreCategoria: 'categoria.nombre.es',
      ambiente: 'ambiente',
      orden: 'orden',
    },
    prepare: ({ media, alt, nombreCategoria, ambiente, orden }) => {
      const nombreCat = nombreCategoria ?? 'Sin categoría';
      const nombreAmb = ambiente
        ? AMBIENTES_GALERIA.find((a) => a.value === ambiente)?.title
        : null;
      const partes = nombreAmb ? [nombreCat, nombreAmb] : [nombreCat];
      return {
        title: alt || 'Foto sin descripción',
        subtitle: `${partes.join(' · ')} · orden ${orden ?? '—'}`,
        media,
      };
    },
  },
});
