import { defineField, defineType } from 'sanity';

// Bloques reutilizables. Se repiten en varias páginas, así que viven una
// sola vez acá — igual que los componentes .astro.

export const pilar = defineType({
  name: 'pilar',
  title: 'Pilar',
  type: 'object',
  fields: [
    defineField({
      name: 'titulo',
      title: 'Título',
      type: 'string',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'texto',
      title: 'Texto',
      type: 'text',
      rows: 3,
      validation: (r) => r.required(),
    }),
  ],
  preview: {
    select: { title: 'titulo', subtitle: 'texto' },
  },
});

export const imagenConAlt = defineType({
  name: 'imagenConAlt',
  title: 'Imagen',
  type: 'image',
  options: { hotspot: true },
  fields: [
    defineField({
      name: 'alt',
      title: 'Descripción de la imagen',
      type: 'string',
      description:
        'Describe brevemente qué se ve en la foto. Lo leen los buscadores y las personas con discapacidad visual. Ej: "Niños corriendo en el patio de césped".',
      validation: (r) => r.required(),
    }),
  ],
});

export const franja = defineType({
  name: 'franja',
  title: 'Franja de llamado a la acción',
  type: 'object',
  description: 'La banda con foto de fondo y botón que aparece al final de la página.',
  fields: [
    defineField({
      name: 'titulo',
      title: 'Título',
      type: 'string',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'texto',
      title: 'Texto',
      type: 'text',
      rows: 2,
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'textoBoton',
      title: 'Texto del botón',
      type: 'string',
      initialValue: 'Escríbenos por WhatsApp',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'foto',
      title: 'Foto de fondo',
      type: 'imagenConAlt',
      validation: (r) => r.required(),
    }),
  ],
  preview: {
    select: { title: 'titulo', media: 'foto' },
  },
});

export const portada = defineType({
  name: 'portada',
  title: 'Portada de la página',
  type: 'object',
  description: 'La parte de arriba, con la foto grande y el título principal.',
  fields: [
    defineField({
      name: 'titulo',
      title: 'Título',
      type: 'string',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'bajada',
      title: 'Texto debajo del título',
      type: 'text',
      rows: 2,
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'foto',
      title: 'Foto de fondo',
      type: 'imagenConAlt',
      validation: (r) => r.required(),
    }),
  ],
  preview: {
    select: { title: 'titulo', media: 'foto' },
  },
});
