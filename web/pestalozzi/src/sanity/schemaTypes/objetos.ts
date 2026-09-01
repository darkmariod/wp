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

// Lo que un padre pregunta antes de decidir: qué edad, qué incluye. El
// bloque de niveles antes solo tenía el nombre ("Inicial 1") — esto le
// agrega la información concreta que hace falta para elegir.
export const programa = defineType({
  name: 'programa',
  title: 'Nivel educativo',
  type: 'object',
  fields: [
    defineField({
      name: 'nombre',
      title: 'Nombre del nivel',
      type: 'string',
      description: 'Ej: Inicial 1',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'edades',
      title: 'Edades',
      type: 'string',
      description: 'Ej: 3 a 4 años',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descripcion',
      title: 'Descripción',
      type: 'text',
      rows: 3,
      description: 'Una o dos frases sobre qué se trabaja en este nivel.',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'incluye',
      title: 'Qué incluye',
      type: 'array',
      of: [{ type: 'string' }],
      description:
        'Puntos concretos, no frases largas. Ej: "Grupos de hasta 12 niños", "Informe diario a la familia".',
      validation: (r) => r.required().min(1),
    }),
  ],
  preview: {
    select: { title: 'nombre', subtitle: 'edades' },
  },
});

// Un dato suelto y verificable (horario, años de experiencia, cupos).
export const datoPractico = defineType({
  name: 'datoPractico',
  title: 'Dato práctico',
  type: 'object',
  fields: [
    defineField({
      name: 'etiqueta',
      title: 'Qué es',
      type: 'string',
      description: 'Ej: Horario de atención',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'valor',
      title: 'El dato',
      type: 'string',
      description: 'Ej: Lunes a viernes, 07:30 a 13:00',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'detalle',
      title: 'Aclaración (opcional)',
      type: 'string',
      description: 'Una línea corta debajo, si hace falta.',
    }),
  ],
  preview: {
    select: { title: 'etiqueta', subtitle: 'valor' },
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
