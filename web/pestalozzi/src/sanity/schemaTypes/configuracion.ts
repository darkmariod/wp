import { defineField, defineType } from 'sanity';

// Documento único. Estos datos salen en el pie de página, en la página de
// Contacto, en el botón flotante y en los datos que lee Google — por eso
// viven en un solo lugar: se cambian una vez y se actualizan en todos lados.
export const configuracion = defineType({
  name: 'configuracion',
  title: 'Datos del colegio',
  type: 'document',
  groups: [
    { name: 'contacto', title: 'Contacto', default: true },
    { name: 'redes', title: 'Redes sociales' },
    { name: 'matriculas', title: 'Matrículas' },
    { name: 'aulaVirtual', title: 'Aula virtual' },
  ],
  fields: [
    defineField({
      name: 'telefono',
      title: 'Teléfono',
      type: 'string',
      group: 'contacto',
      description: 'Como se muestra en la página. Ej: 099 824 6396',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'telefonoInternacional',
      title: 'Teléfono en formato internacional',
      type: 'string',
      group: 'contacto',
      description:
        'Solo números, con el código de país y sin espacios. Es el que usan los botones de llamada y WhatsApp. Ej: 593998246396',
      validation: (r) =>
        r
          .required()
          .regex(/^\d{9,15}$/, { name: 'solo números, entre 9 y 15 dígitos' }),
    }),
    defineField({
      name: 'correo',
      title: 'Correo electrónico',
      type: 'string',
      group: 'contacto',
      validation: (r) => r.required().email(),
    }),
    defineField({
      name: 'direccionCalle',
      title: 'Dirección — calle',
      type: 'string',
      group: 'contacto',
      description: 'Ej: Tiwinza #95 y Etza',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'direccionSector',
      title: 'Dirección — sector',
      type: 'string',
      group: 'contacto',
      description: 'Ej: Parroquia Atahualpa',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'ciudad',
      title: 'Ciudad',
      type: 'string',
      group: 'contacto',
      initialValue: 'Ambato',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'facebook',
      title: 'Facebook',
      type: 'url',
      group: 'redes',
    }),
    defineField({
      name: 'tiktok',
      title: 'TikTok',
      type: 'url',
      group: 'redes',
    }),
    defineField({
      name: 'avisoMatriculasTitulo',
      title: 'Aviso de matrículas — título',
      type: 'string',
      group: 'matriculas',
      initialValue: 'Matrículas abiertas',
    }),
    defineField({
      name: 'avisoMatriculasTexto',
      title: 'Aviso de matrículas — texto',
      type: 'string',
      group: 'matriculas',
      description: 'Ej: Ciclo lectivo 2026 – 2027',
    }),
    defineField({
      name: 'textoBotonAdmisiones',
      title: 'Texto del botón de admisiones (arriba a la derecha)',
      type: 'string',
      group: 'matriculas',
      initialValue: 'Admisiones 2026–2027',
      validation: (r) => r.required(),
    }),
    // Si este campo queda vacío, el enlace no aparece en ningún lado del
    // sitio. Así el colegio puede publicar la página antes de tener el
    // Moodle listo, sin que quede un botón roto a la vista.
    defineField({
      name: 'moodleUrl',
      title: 'Dirección del aula virtual (Moodle)',
      type: 'url',
      group: 'aulaVirtual',
      description:
        'Si se deja vacío, el enlace no se muestra en el sitio. Ej: https://aula.pestalozzi.edu.ec',
    }),
    defineField({
      name: 'moodleTexto',
      title: 'Texto del enlace',
      type: 'string',
      group: 'aulaVirtual',
      initialValue: 'Aula virtual',
      description: 'Cómo se llama el enlace en el menú y en el pie de página.',
    }),
  ],
  preview: {
    prepare: () => ({ title: 'Datos del colegio' }),
  },
});
