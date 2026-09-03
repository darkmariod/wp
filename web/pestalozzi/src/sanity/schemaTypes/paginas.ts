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
    { name: 'marquesina', title: 'Frase deslizante' },
    { name: 'porQue', title: 'Por qué Pestalozzi' },
    { name: 'galeria', title: 'Vida escolar' },
    { name: 'franja', title: 'Franja final' },
  ],
  fields: [
    defineField({
      name: 'tituloLinea1',
      title: 'Título — primera línea',
      type: 'textoLocalizado',
      group: 'portada',
      description: 'Ej: Mi Escuelita',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'tituloLinea2',
      title: 'Título — segunda línea',
      type: 'textoLocalizado',
      group: 'portada',
      description: 'Se muestra en color ámbar, destacada. Ej: Pestalozzi',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'bajada',
      title: 'Texto debajo del título',
      type: 'parrafoLocalizado',
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
      type: 'textoLocalizado',
      group: 'propuesta',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'propuestaTexto',
      title: 'Texto',
      type: 'parrafoLocalizado',
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
      type: 'textoLocalizado',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesTitulo',
      title: 'Título',
      type: 'textoLocalizado',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesTexto',
      title: 'Texto',
      type: 'parrafoLocalizado',
      group: 'niveles',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesChips',
      title: 'Niveles que ofrece el colegio',
      type: 'array',
      of: [{ type: 'textoLocalizado' }],
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
      type: 'textoLocalizado',
      group: 'programas',
    }),
    defineField({
      name: 'programasTexto',
      title: 'Texto de la sección',
      type: 'parrafoLocalizado',
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
      type: 'textoLocalizado',
      group: 'practico',
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

    defineField({
      name: 'marquesinaFrases',
      title: 'Frases',
      type: 'array',
      of: [{ type: 'textoLocalizado' }],
      group: 'marquesina',
      description:
        'Se muestran una tras otra, desplazándose sin parar. Ej: Cada día crezco · con alegría · con amor',
      validation: (r) => r.required().min(2),
    }),

    defineField({
      name: 'porQueEyebrow',
      title: 'Etiqueta pequeña',
      type: 'textoLocalizado',
      group: 'porQue',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'porQueTitulo',
      title: 'Título',
      type: 'textoLocalizado',
      group: 'porQue',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'porQueParrafos',
      title: 'Párrafos',
      type: 'array',
      of: [{ type: 'parrafoLocalizado' }],
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
      type: 'textoLocalizado',
      group: 'galeria',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'galeriaTitulo',
      title: 'Título',
      type: 'textoLocalizado',
      group: 'galeria',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'galeriaTexto',
      title: 'Texto',
      type: 'parrafoLocalizado',
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
      type: 'textoLocalizado',
      group: 'quienes',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'quienesTitulo',
      title: 'Título',
      type: 'textoLocalizado',
      group: 'quienes',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'quienesParrafos',
      title: 'Párrafos',
      type: 'array',
      of: [{ type: 'parrafoLocalizado' }],
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
      type: 'textoLocalizado',
      group: 'filosofia',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'filosofiaTitulo',
      title: 'Título',
      type: 'textoLocalizado',
      group: 'filosofia',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'filosofiaTexto',
      title: 'Texto',
      type: 'parrafoLocalizado',
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
      type: 'parrafoLocalizado',
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descInstalaciones',
      title: 'Texto del filtro "Instalaciones"',
      type: 'parrafoLocalizado',
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descActividades',
      title: 'Texto del filtro "Actividades"',
      type: 'parrafoLocalizado',
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descDeportes',
      title: 'Texto del filtro "Deportes"',
      type: 'parrafoLocalizado',
      group: 'descripciones',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'descEventos',
      title: 'Texto del filtro "Eventos"',
      type: 'parrafoLocalizado',
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
      type: 'textoLocalizado',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'formularioTitulo',
      title: 'Título del formulario',
      type: 'textoLocalizado',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'nivelesFormulario',
      title: 'Opciones del campo "Nivel de interés"',
      type: 'array',
      of: [{ type: 'textoLocalizado' }],
      description: 'Las opciones que el visitante puede elegir en el formulario.',
      validation: (r) => r.required().min(1),
    }),
  ],
  preview: { prepare: () => ({ title: 'Página: Contacto' }) },
});


// Página de voluntariado. Sale de la segunda reunión: el colegio busca
// voluntarios angloparlantes que conozcan Montessori.
export const paginaVoluntariado = defineType({
  name: 'paginaVoluntariado',
  title: 'Página: Voluntariado',
  type: 'document',
  groups: [
    { name: 'portada', title: 'Portada', default: true },
    { name: 'contenido', title: 'Contenido' },
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
      name: 'introTitulo',
      title: 'Título',
      type: 'textoLocalizado',
      group: 'contenido',
      validation: (r) => r.required(),
    }),
    defineField({
      name: 'introParrafos',
      title: 'Párrafos',
      type: 'array',
      of: [{ type: 'parrafoLocalizado' }],
      group: 'contenido',
      validation: (r) => r.required().min(1),
    }),
    defineField({
      name: 'requisitosTitulo',
      title: 'Título de la lista',
      type: 'textoLocalizado',
      group: 'contenido',
    }),
    defineField({
      name: 'requisitos',
      title: 'A quién buscan',
      type: 'array',
      of: [{ type: 'textoLocalizado' }],
      group: 'contenido',
      description: 'Uno por línea, corto. Ej: "Hablante nativo de inglés".',
      validation: (r) => r.required().min(1),
    }),
    defineField({
      name: 'textoBoton',
      title: 'Texto del botón',
      type: 'textoLocalizado',
      group: 'contenido',
    }),
    defineField({
      name: 'foto',
      title: 'Foto',
      type: 'imagenConAlt',
      group: 'contenido',
      description: 'Opcional. Si no se carga, el texto ocupa todo el ancho.',
    }),

    defineField({
      name: 'franja',
      title: 'Franja final',
      type: 'franja',
      group: 'franja',
      validation: (r) => r.required(),
    }),
  ],
  preview: { prepare: () => ({ title: 'Página: Voluntariado' }) },
});
