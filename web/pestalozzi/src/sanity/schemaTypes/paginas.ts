import { defineField, defineType } from 'sanity';

// Una página = un documento único. El colegio no crea ni borra páginas,
// solo edita el contenido de las que existen — por eso son singletons y
// no una colección.

export const paginaInicio = defineType({
  name: 'paginaInicio',
  title: 'Página: Inicio',
  type: 'document',
  groups: [
    { name: 'portada', title: 'Portada', default: true },
    { name: 'propuesta', title: 'Propuesta' },
    { name: 'niveles', title: 'Niveles' },
    { name: 'destacados', title: 'Lo que nos distingue' },
    { name: 'programas', title: 'Qué ofrece cada nivel' },
    { name: 'practico', title: 'Datos prácticos' },
    { name: 'voluntariado', title: 'Voluntariado' },
    { name: 'marquesina', title: 'Frase deslizante' },
    { name: 'porQue', title: 'Por qué Pestalozzi' },
    { name: 'galeria', title: 'Vida escolar' },
    { name: 'franja', title: 'Franja final' },
  ],
  fields: [
    defineField({
      name: 'tituloLinea1',
      title: 'Título — primera línea',
      type: 'string',
      group: 'portada',
      description: 'Ej: Mi Escuelita',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'tituloLinea2',
      title: 'Título — segunda línea',
      type: 'string',
      group: 'portada',
      description: 'Se muestra en color ámbar, destacada. Ej: Pestalozzi',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'bajada',
      title: 'Texto debajo del título',
      type: 'text',
      rows: 2,
      group: 'portada',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'fotoPortada',
      title: 'Foto de portada',
      type: 'imagenConAlt',
      group: 'portada',
      description:
        'Se ve mientras carga el video de fondo, y es la que queda en celulares con datos limitados.',
      validation: (r) => r.required(),
    }),

    defineField({
      name: 'videoEscritorio',
      title: 'Video de portada — computadora',
      type: 'videoFondo',
      group: 'portada',
      description:
        'Opcional. Se reproduce detrás del título en pantallas grandes. Si se deja vacío queda la foto de portada, que también funciona bien. Máximo 8 MB.',
    }),
    defineField({
      name: 'videoMovil',
      title: 'Video de portada — celular',
      type: 'videoFondo',
      group: 'portada',
      description:
        'Opcional. Conviene uno vertical y más liviano que el de computadora. Si se deja vacío se usa el de computadora.',
    }),

    defineField({
      name: 'propuestaTitulo',
      title: 'Título',
      type: 'string',
      group: 'propuesta',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'propuestaTexto',
      title: 'Texto',
      type: 'text',
      rows: 3,
      group: 'propuesta',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'propuestaPilares',
      title: 'Pilares',
      type: 'array',
      of: [{ type: 'pilar' }],
      group: 'propuesta',
      validation: (r) => r.required().length(3).error('Deben ser exactamente 3 pilares.'),
    }),

    defineField({
      name: 'nivelesEyebrow',
      title: 'Etiqueta pequeña',
      type: 'string',
      group: 'niveles',
      initialValue: 'Oferta educativa',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesTitulo',
      title: 'Título',
      type: 'string',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesTexto',
      title: 'Texto',
      type: 'text',
      rows: 2,
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesChips',
      title: 'Niveles que ofrece el colegio',
      type: 'array',
      of: [{ type: 'string' }],
      group: 'niveles',
      description: 'Cada uno se muestra como una etiqueta redondeada. Ej: Inicial 1',
      validation: (r) => r.required().min(1),
    }),
    defineField({
      name: 'nivelesFotoIzquierda',
      title: 'Foto de la izquierda',
      type: 'imagenConAlt',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesBloqueVerde',
      title: 'Bloque verde',
      type: 'pilar',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesBloqueAmbar',
      title: 'Bloque ámbar',
      type: 'pilar',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesFotoDerecha',
      title: 'Foto de la derecha',
      type: 'imagenConAlt',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesCarrusel',
      title: 'Fotos del carrusel',
      type: 'array',
      of: [{ type: 'imagenConAlt' }],
      group: 'niveles',
      description:
        'Las fotos que giran en el carrusel de esta sección. Conviene de 4 en adelante: con menos no se nota la profundidad. Si se deja vacío, usa las de la galería.',
    }),

    // --- Lo que nos distingue ---
    // Va apenas debajo de la portada: lo primero que ve un padre después
    // del título. Cuatro razones cortas, no párrafos.
    defineField({
      name: 'destacados',
      title: 'Razones',
      type: 'array',
      of: [{ type: 'pilar' }],
      group: 'destacados',
      description:
        'Cuatro como máximo, y bien cortas: un título de dos o tres palabras y una línea de explicación. Si se deja vacío, la sección no aparece.',
      validation: (r) => r.max(4),
    }),

    // --- Qué ofrece cada nivel ---
    // Sin required: si el colegio todavía no cargó nada, la sección
    // entera no se muestra en el sitio. Mejor que aparezca vacía o con
    // datos de relleno inventados.
    defineField({
      name: 'programasTitulo',
      title: 'Título de la sección',
      type: 'string',
      group: 'programas',
      initialValue: 'Qué ofrece cada nivel',
    }),
    defineField({
      name: 'programasTexto',
      title: 'Texto de la sección',
      type: 'text',
      rows: 2,
      group: 'programas',
    }),
    defineField({
      name: 'programas',
      title: 'Niveles',
      type: 'array',
      of: [{ type: 'programa' }],
      group: 'programas',
      description:
        'Un bloque por nivel, con la edad y qué incluye. Si se deja vacío, la sección no aparece en el sitio.',
    }),

    // --- Datos prácticos ---
    defineField({
      name: 'practicoTitulo',
      title: 'Título de la sección',
      type: 'string',
      group: 'practico',
      initialValue: 'Horarios y admisiones',
    }),
    defineField({
      name: 'practicoDatos',
      title: 'Datos',
      type: 'array',
      of: [{ type: 'datoPractico' }],
      group: 'practico',
      description:
        'Horario de atención, cupos por aula, requisitos de matrícula… lo que un padre pregunta antes de llamar. Si se deja vacío, la sección no aparece.',
    }),

    // --- Voluntariado ---
    // Sale de la reunión: el colegio busca voluntarios angloparlantes
    // que conozcan Montessori. Si el título queda vacío, la sección no
    // aparece — así el colegio puede apagarla cuando no busque gente,
    // sin que haya que tocar el código.
    defineField({
      name: 'voluntariadoTitulo',
      title: 'Título',
      type: 'string',
      group: 'voluntariado',
      description:
        'Si se deja vacío, la sección no se muestra en el sitio. Sirve para apagarla cuando no estén buscando voluntarios.',
    }),
    defineField({
      name: 'voluntariadoTexto',
      title: 'Texto',
      type: 'text',
      rows: 3,
      group: 'voluntariado',
    }),
    defineField({
      name: 'voluntariadoRequisitos',
      title: 'A quién buscan',
      type: 'array',
      of: [{ type: 'string' }],
      group: 'voluntariado',
      description:
        'Uno por línea, corto. Ej: "Hablante nativo de inglés", "Conoce la metodología Montessori".',
    }),
    defineField({
      name: 'voluntariadoTextoBoton',
      title: 'Texto del botón',
      type: 'string',
      group: 'voluntariado',
      initialValue: 'Quiero ser voluntario',
    }),
    defineField({
      name: 'voluntariadoFoto',
      title: 'Foto',
      type: 'imagenConAlt',
      group: 'voluntariado',
      description: 'Opcional. Si no se carga, la sección va sin foto.',
    }),

    defineField({
      name: 'marquesinaFrases',
      title: 'Frases',
      type: 'array',
      of: [{ type: 'string' }],
      group: 'marquesina',
      description:
        'Se muestran una tras otra, desplazándose sin parar. Ej: Cada día crezco · con alegría · con amor',
      validation: (r) => r.required().min(2),
    }),

    defineField({
      name: 'porQueEyebrow',
      title: 'Etiqueta pequeña',
      type: 'string',
      group: 'porQue',
      initialValue: 'Por qué Pestalozzi',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'porQueTitulo',
      title: 'Título',
      type: 'string',
      group: 'porQue',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'porQueParrafos',
      title: 'Párrafos',
      type: 'array',
      of: [{ type: 'text', rows: 3 }],
      group: 'porQue',
      validation: (r) => r.required().min(1),
    }),
    defineField({
      name: 'porQueFoto',
      title: 'Foto',
      type: 'imagenConAlt',
      group: 'porQue',
      validation: (r) => r.required(),
    }),

    defineField({
      name: 'galeriaEyebrow',
      title: 'Etiqueta pequeña',
      type: 'string',
      group: 'galeria',
      initialValue: 'Vida escolar',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'galeriaTitulo',
      title: 'Título',
      type: 'string',
      group: 'galeria',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'galeriaTexto',
      title: 'Texto',
      type: 'text',
      rows: 2,
      group: 'galeria',
      validation: (r) => r.required(),
    }),

    defineField({
      name: 'franja',
      title: 'Franja final',
      type: 'franja',
      group: 'franja',
      validation: (r) => r.required(),
    }),
  ],
  preview: { prepare: () => ({ title: 'Página: Inicio' }) },
});

export const paginaNosotros = defineType({
  name: 'paginaNosotros',
  title: 'Página: Nosotros',
  type: 'document',
  groups: [
    { name: 'portada', title: 'Portada', default: true },
    { name: 'quienes', title: 'Quiénes somos' },
    { name: 'filosofia', title: 'El método' },
    { name: 'franja', title: 'Franja final' },
  ],
  fields: [
    defineField({
      name: 'portada',
      title: 'Portada',
      type: 'portada',
      group: 'portada',
      validation: (r) => r.required(),
    }),

    defineField({
      name: 'quienesEyebrow',
      title: 'Etiqueta pequeña',
      type: 'string',
      group: 'quienes',
      initialValue: 'Quiénes somos',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'quienesTitulo',
      title: 'Título',
      type: 'string',
      group: 'quienes',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'quienesParrafos',
      title: 'Párrafos',
      type: 'array',
      of: [{ type: 'text', rows: 3 }],
      group: 'quienes',
      validation: (r) => r.required().min(1),
    }),
    defineField({
      name: 'quienesFoto',
      title: 'Foto',
      type: 'imagenConAlt',
      group: 'quienes',
      validation: (r) => r.required(),
    }),

    defineField({
      name: 'filosofiaEyebrow',
      title: 'Etiqueta pequeña',
      type: 'string',
      group: 'filosofia',
      initialValue: 'El método',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'filosofiaTitulo',
      title: 'Título',
      type: 'string',
      group: 'filosofia',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'filosofiaTexto',
      title: 'Texto',
      type: 'text',
      rows: 3,
      group: 'filosofia',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'filosofiaPilares',
      title: 'Pilares',
      type: 'array',
      of: [{ type: 'pilar' }],
      group: 'filosofia',
      validation: (r) => r.required().length(3).error('Deben ser exactamente 3 pilares.'),
    }),

    defineField({
      name: 'franja',
      title: 'Franja final',
      type: 'franja',
      group: 'franja',
      validation: (r) => r.required(),
    }),
  ],
  preview: { prepare: () => ({ title: 'Página: Nosotros' }) },
});

export const paginaGaleria = defineType({
  name: 'paginaGaleria',
  title: 'Página: Galería',
  type: 'document',
  groups: [
    { name: 'portada', title: 'Portada', default: true },
    { name: 'descripciones', title: 'Textos de cada filtro' },
    { name: 'franja', title: 'Franja final' },
  ],
  fields: [
    defineField({
      name: 'portada',
      title: 'Portada',
      type: 'portada',
      group: 'portada',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descTodas',
      title: 'Texto del filtro "Todas"',
      type: 'text',
      rows: 2,
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descInstalaciones',
      title: 'Texto del filtro "Instalaciones"',
      type: 'text',
      rows: 2,
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descActividades',
      title: 'Texto del filtro "Actividades"',
      type: 'text',
      rows: 2,
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descDeportes',
      title: 'Texto del filtro "Deportes"',
      type: 'text',
      rows: 2,
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descEventos',
      title: 'Texto del filtro "Eventos"',
      type: 'text',
      rows: 2,
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'franja',
      title: 'Franja final',
      type: 'franja',
      group: 'franja',
      validation: (r) => r.required(),
    }),
  ],
  preview: { prepare: () => ({ title: 'Página: Galería' }) },
});

export const paginaContacto = defineType({
  name: 'paginaContacto',
  title: 'Página: Contacto',
  type: 'document',
  fields: [
    defineField({
      name: 'portada',
      title: 'Portada',
      type: 'portada',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'datosTitulo',
      title: 'Título de la sección de datos',
      type: 'string',
      initialValue: 'Dónde estamos',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'formularioTitulo',
      title: 'Título del formulario',
      type: 'string',
      initialValue: 'Envíanos un mensaje',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesFormulario',
      title: 'Opciones del campo "Nivel de interés"',
      type: 'array',
      of: [{ type: 'string' }],
      description: 'Las opciones que el visitante puede elegir en el formulario.',
      validation: (r) => r.required().min(1),
    }),
  ],
  preview: { prepare: () => ({ title: 'Página: Contacto' }) },
});
