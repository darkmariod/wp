import { defineField, defineType } from 'sanity';

// Bloques reutilizables. Se repiten en varias páginas, así que viven una
// sola vez acá — igual que los componentes .astro.

// --- Texto en dos idiomas ---
// Cada campo de contenido pasa a tener una casilla en español y otra en
// inglés, lado a lado en la misma pantalla de edición — no dos páginas
// separadas. Así la estructura (cuántas fotos, cuántos párrafos) es una
// sola, y no hay forma de que las dos versiones se desincronicen.
//
// El inglés no es obligatorio: mientras el colegio no lo cargue, el
// sitio muestra el español ahí (ver el helper de idioma en las páginas).
// Así nunca queda un hueco en blanco por un campo sin traducir todavía.
export const textoLocalizado = defineType({
  name: 'textoLocalizado',
  title: 'Texto',
  type: 'object',
  fieldsets: [{ name: 'idiomas', title: 'Idiomas', options: { columns: 2 } }],
  fields: [
    defineField({ name: 'es', title: '🇪🇨 Español', type: 'string', fieldset: 'idiomas', validation: (r) => r.required() }),
    defineField({ name: 'en', title: '🇬🇧 English', type: 'string', fieldset: 'idiomas' }),
  ],
  preview: { select: { title: 'es', subtitle: 'en' } },
});

// Misma idea que textoLocalizado, para párrafos más largos.
export const parrafoLocalizado = defineType({
  name: 'parrafoLocalizado',
  title: 'Párrafo',
  type: 'object',
  fieldsets: [{ name: 'idiomas', title: 'Idiomas', options: { columns: 2 } }],
  fields: [
    defineField({ name: 'es', title: '🇪🇨 Español', type: 'text', rows: 3, fieldset: 'idiomas', validation: (r) => r.required() }),
    defineField({ name: 'en', title: '🇬🇧 English', type: 'text', rows: 3, fieldset: 'idiomas' }),
  ],
  preview: { select: { title: 'es', subtitle: 'en' } },
});

export const pilar = defineType({
  name: 'pilar',
  title: 'Pilar',
  type: 'object',
  fields: [
    defineField({
      name: 'titulo',
      title: 'Título',
      type: 'textoLocalizado',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'texto',
      title: 'Texto',
      type: 'parrafoLocalizado',
      validation: (r) => r.required(),
    }),
  ],
  preview: {
    select: { title: 'titulo.es', subtitle: 'texto.es' },
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
      type: 'textoLocalizado',
      description: 'Ej: Inicial 1',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'edades',
      title: 'Edades',
      type: 'textoLocalizado',
      description: 'Ej: 3 a 4 años',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descripcion',
      title: 'Descripción',
      type: 'parrafoLocalizado',
      description: 'Una o dos frases sobre qué se trabaja en este nivel.',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'incluye',
      title: 'Qué incluye',
      type: 'array',
      of: [{ type: 'textoLocalizado' }],
      description:
        'Puntos concretos, no frases largas. Ej: "Grupos de hasta 12 niños", "Informe diario a la familia".',
      validation: (r) => r.required().min(1),
    }),
  ],
  preview: {
    select: { title: 'nombre.es', subtitle: 'edades.es' },
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
      type: 'textoLocalizado',
      description: 'Ej: Horario de atención',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'valor',
      title: 'El dato',
      type: 'textoLocalizado',
      description: 'Ej: Lunes a viernes, 07:30 a 13:00',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'detalle',
      title: 'Aclaración (opcional)',
      type: 'textoLocalizado',
      description: 'Una línea corta debajo, si hace falta.',
    }),
  ],
  preview: {
    select: { title: 'etiqueta.es', subtitle: 'valor.es' },
  },
});

// Video de fondo. El límite de peso NO es un capricho: el video de
// portada se descarga entero antes de reproducirse, y muchas familias
// entran con datos móviles. Los videos actuales pesan 1,2 MB; de 8 MB
// para arriba la portada tarda tanto que se ve la foto fija todo el rato.
export const videoFondo = defineType({
  name: 'videoFondo',
  title: 'Video de fondo',
  type: 'file',
  options: { accept: 'video/mp4,video/webm' },
  validation: (r) =>
    r.custom((valor: any) => {
      const tam = valor?.asset?.size;
      if (!tam) return true;              // todavía no subió nada
      const mb = tam / (1024 * 1024);
      if (mb > 8) {
        return `El video pesa ${mb.toFixed(1)} MB. El máximo recomendado es 8 MB: más que eso y la portada tarda demasiado en celulares con datos.`;
      }
      return true;
    }),
});

// El texto alternativo se deja COMPARTIDO entre los dos idiomas, a
// propósito: hay más de 20 imágenes en el sitio (portadas, franjas,
// carrusel, galería), y duplicar "niños jugando en el patio" /
// "children playing in the yard" en cada una es mucho trabajo para el
// colegio a cambio de poco: el lector de pantalla igual describe la
// escena, en el idioma que sea. Si en el futuro hace falta afinarlo
// por idioma, se puede volver a separar sin perder lo ya cargado.
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
      type: 'textoLocalizado',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'texto',
      title: 'Texto',
      type: 'parrafoLocalizado',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'textoBoton',
      title: 'Texto del botón',
      type: 'textoLocalizado',
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
    select: { title: 'titulo.es', media: 'foto' },
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
      type: 'textoLocalizado',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'bajada',
      title: 'Texto debajo del título',
      type: 'parrafoLocalizado',
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
    select: { title: 'titulo.es', media: 'foto' },
  },
});
