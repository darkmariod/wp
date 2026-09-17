import { defineField, defineType } from 'sanity';

// Las docentes crean sus propias categorías acá: el filtro de la
// galería y las fotos que hay debajo de cada botón se arman solos a
// partir de lo que exista en este listado, sin tocar código.
export const categoriaGaleria = defineType({
  name: 'categoriaGaleria',
  title: 'Categoría de la galería',
  type: 'document',
  fields: [
    defineField({
      name: 'nombre',
      title: 'Nombre',
      type: 'textoLocalizado',
      description: 'Cómo se ve en el botón del filtro de la galería (ej: "Excursiones").',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descripcion',
      title: 'Descripción',
      type: 'parrafoLocalizado',
      description: 'El texto que aparece arriba de las fotos cuando alguien elige este filtro.',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'orden',
      title: 'Orden',
      type: 'number',
      description:
        'En qué posición aparece este filtro (de menor a mayor). Deja espacio entre números (10, 20, 30…) para poder intercalar después sin renumerar todo.',
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
    select: { title: 'nombre.es', orden: 'orden' },
    prepare: ({ title, orden }) => ({
      title: title || 'Categoría sin nombre',
      subtitle: `Orden ${orden ?? '—'}`,
    }),
  },
});
