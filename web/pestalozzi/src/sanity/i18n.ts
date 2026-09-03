// Todo lo necesario para tener el sitio en dos idiomas: leer un campo
// del CMS en el idioma que toca, y el texto de interfaz que NUNCA pasa
// por el panel (nav, botones genéricos, mensajes del formulario).
//
// El contenido editorial (descripciones, filosofía, textos de cada
// nivel) vive en Sanity con las dos casillas — ver textoLocalizado y
// parrafoLocalizado en schemaTypes/objetos.ts. El texto de acá abajo es
// justamente lo otro: microcopy mecánico (botones, validaciones) que
// no necesita la voz del colegio para traducirse bien.

export type Locale = 'es' | 'en';

type CampoLocalizado = { es?: string; en?: string } | null | undefined;

// Si el inglés todavía no está cargado, muestra el español en su
// lugar — así ninguna sección queda con un hueco en blanco mientras
// el colegio va traduciendo.
export function t(campo: CampoLocalizado, locale: Locale): string {
  if (!campo) return '';
  if (locale === 'en' && campo.en) return campo.en;
  return campo.es ?? '';
}

export function tArray(campos: CampoLocalizado[] | null | undefined, locale: Locale): string[] {
  if (!campos) return [];
  return campos.map((c) => t(c, locale));
}

// Arma la dirección de una página en el idioma pedido. El español no
// lleva prefijo (es el que ya estaba indexado); el inglés va bajo /en.
export function urlIdioma(ruta: string, locale: Locale): string {
  if (locale === 'es') return ruta;
  return ruta === '/' ? '/en' : `/en${ruta}`;
}

export const UI = {
  es: {
    lang: 'es-EC',
    ogLocale: 'es_EC',
    saltarContenido: 'Saltar al contenido',
    nav: {
      inicio: 'Inicio',
      nosotros: 'Nosotros',
      galeria: 'Galería',
      voluntariado: 'Voluntariado',
      contacto: 'Contacto',
    },
    breadcrumbInicio: 'Inicio',
    aulaVirtualPorDefecto: 'Aula virtual',
    escribenos: 'Escríbenos',
    abrirMenu: 'Abrir menú',
    cerrarMenu: 'Cerrar menú',
    footer: {
      institucion: 'Institución',
      propuestaEducativa: 'Propuesta educativa',
      niveles: 'Niveles',
      filosofia: 'Filosofía',
      admisiones: 'Admisiones',
      contacto: 'Contacto',
      siguenos: 'Síguenos',
      unidadEducativa: 'Unidad Educativa',
      legal: (anio: string) =>
        `© ${anio} Unidad Educativa Pestalozzi Ambato. Desarrollado: Monkey Computer. Todos los derechos reservados.`,
    },
    fab: {
      whatsapp: 'WhatsApp',
      llamar: 'Llamar',
      correo: 'Correo',
      escribirWhatsapp: 'Escribir por WhatsApp',
      llamarTelefono: 'Llamar por teléfono',
      enviarCorreo: 'Enviar un correo',
      abrirOpciones: 'Abrir opciones de contacto',
      cerrarOpciones: 'Cerrar opciones de contacto',
    },
    desliza: 'Desliza',
    irAlContenido: 'Ir al contenido',
    verTodaLaGaleria: 'Ver toda la galería',
    fotografia: 'fotografía',
    fotografias: 'fotografías',
    tocaFotoAmpliar: 'tocá una foto para ampliarla',
    conocenos: 'Conócenos',
    agendaVisita: 'Agenda tu visita',
    conocePropuesta: 'Conoce la propuesta',
    verDatosContacto: 'Ver datos de contacto',
    contacto: {
      datosDeContacto: 'Datos de contacto',
      direccion: 'Dirección',
      telefono: 'Teléfono',
      llamadasYMensajes: 'Llamadas y mensajes',
      correo: 'Correo',
      secretaria: 'Secretaría',
      escribirWhatsapp: 'Escribir por WhatsApp',
      nombreYApellido: 'Nombre y apellido',
      correoElectronico: 'Correo electrónico',
      opcional: '(opcional)',
      nivelDeInteres: 'Nivel de interés',
      seleccionaUnaOpcion: 'Selecciona una opción',
      mensaje: 'Mensaje',
      errorNombre: 'Escribe tu nombre completo.',
      errorCorreo: 'Revisa el formato del correo.',
      errorMensaje: 'Cuéntanos en qué podemos ayudarte (mínimo 10 caracteres).',
      noLlenar: 'No llenar',
      enviarMensaje: 'Enviar mensaje',
      ubicacionMapa: (ciudad: string) => `Ubicación de la Unidad Educativa Pestalozzi en ${ciudad}`,
      tituloPagina: 'Contacto | Unidad Educativa Pestalozzi Ambato',
      descripcionPagina: (calle: string, ciudad: string, telefono: string) =>
        `${calle}, ${ciudad}. Teléfono ${telefono}. Escríbenos por WhatsApp o agenda una visita a la Unidad Educativa Pestalozzi.`,
    },
    voluntariado: {
      eyebrow: 'Súmate',
      requisitosTituloPorDefecto: 'A quién buscamos',
      botonPorDefecto: 'Quiero ser voluntario',
      mensajeWhatsapp: 'Hola, me interesa el voluntariado en Pestalozzi.',
      tituloPagina: 'Voluntariado | Unidad Educativa Pestalozzi Ambato',
      descripcionPagina:
        'Súmate como voluntario a la Unidad Educativa Pestalozzi de Ambato. Buscamos hablantes nativos de inglés y personas que conozcan la metodología Montessori.',
    },
    galeria: {
      fotografiaAmpliada: 'Fotografía ampliada',
      cerrar: 'Cerrar',
      anterior: 'Anterior',
      siguiente: 'Siguiente',
      tituloPagina: 'Galería | Unidad Educativa Pestalozzi Ambato',
      descripcionPagina: 'Instalaciones, actividades, deportes y eventos de la Unidad Educativa Pestalozzi de Ambato.',
    },
    nosotros: {
      tituloPagina: 'Nosotros | Unidad Educativa Pestalozzi Ambato',
      descripcionPagina: 'Quiénes somos, la filosofía Pestalozzi en el aula, misión y visión de la Unidad Educativa Pestalozzi de Ambato.',
    },
    inicio: {
      tituloPagina: 'Unidad Educativa Pestalozzi Ambato | Matrículas 2026 – 2027',
      descripcionPagina: 'Escuela en Ambato con la filosofía Pestalozzi: el niño en el centro de su aprendizaje. Matrículas abiertas ciclo 2026 – 2027.',
      ogTitulo: 'Unidad Educativa Pestalozzi Ambato',
      fotografiasDelColegio: 'Fotografías del colegio',
      fotoAnterior: 'Foto anterior',
      fotoSiguiente: 'Foto siguiente',
      fotos: 'Fotos',
      irALaFoto: (n: number) => `Ir a la foto ${n}`,
      deTotal: (i: number, total: number) => `${i} de ${total}`,
    },
  },
  en: {
    lang: 'en',
    ogLocale: 'en_US',
    saltarContenido: 'Skip to content',
    nav: {
      inicio: 'Home',
      nosotros: 'About Us',
      galeria: 'Gallery',
      voluntariado: 'Volunteer',
      contacto: 'Contact',
    },
    breadcrumbInicio: 'Home',
    aulaVirtualPorDefecto: 'Virtual Classroom',
    escribenos: 'Message Us',
    abrirMenu: 'Open menu',
    cerrarMenu: 'Close menu',
    footer: {
      institucion: 'Institution',
      propuestaEducativa: 'Educational Approach',
      niveles: 'Grade Levels',
      filosofia: 'Philosophy',
      admisiones: 'Admissions',
      contacto: 'Contact',
      siguenos: 'Follow Us',
      unidadEducativa: 'Educational Unit',
      legal: (anio: string) =>
        `© ${anio} Unidad Educativa Pestalozzi Ambato. Developed by: Monkey Computer. All rights reserved.`,
    },
    fab: {
      whatsapp: 'WhatsApp',
      llamar: 'Call',
      correo: 'Email',
      escribirWhatsapp: 'Message on WhatsApp',
      llamarTelefono: 'Call by phone',
      enviarCorreo: 'Send an email',
      abrirOpciones: 'Open contact options',
      cerrarOpciones: 'Close contact options',
    },
    desliza: 'Scroll',
    irAlContenido: 'Go to content',
    verTodaLaGaleria: 'View full gallery',
    fotografia: 'photo',
    fotografias: 'photos',
    tocaFotoAmpliar: 'tap a photo to enlarge it',
    conocenos: 'Learn more about us',
    agendaVisita: 'Schedule a visit',
    conocePropuesta: 'See our approach',
    verDatosContacto: 'See contact details',
    contacto: {
      datosDeContacto: 'Contact Details',
      direccion: 'Address',
      telefono: 'Phone',
      llamadasYMensajes: 'Calls and messages',
      correo: 'Email',
      secretaria: 'Front office',
      escribirWhatsapp: 'Message on WhatsApp',
      nombreYApellido: 'Full name',
      correoElectronico: 'Email address',
      opcional: '(optional)',
      nivelDeInteres: 'Grade level of interest',
      seleccionaUnaOpcion: 'Select an option',
      mensaje: 'Message',
      errorNombre: 'Please enter your full name.',
      errorCorreo: 'Check the email format.',
      errorMensaje: 'Tell us how we can help (10 characters minimum).',
      noLlenar: 'Leave blank',
      enviarMensaje: 'Send message',
      ubicacionMapa: (ciudad: string) => `Location of Unidad Educativa Pestalozzi in ${ciudad}`,
      tituloPagina: 'Contact | Pestalozzi Ambato Educational Unit',
      descripcionPagina: (calle: string, ciudad: string, telefono: string) =>
        `${calle}, ${ciudad}. Phone ${telefono}. Message us on WhatsApp or schedule a visit to Unidad Educativa Pestalozzi.`,
    },
    voluntariado: {
      eyebrow: 'Join Us',
      requisitosTituloPorDefecto: 'Who We Are Looking For',
      botonPorDefecto: 'I Want to Volunteer',
      mensajeWhatsapp: "Hello, I'm interested in volunteering at Pestalozzi.",
      tituloPagina: 'Volunteer | Pestalozzi Ambato Educational Unit',
      descripcionPagina:
        'Join Unidad Educativa Pestalozzi Ambato as a volunteer. We are looking for native English speakers and people familiar with the Montessori method.',
    },
    galeria: {
      fotografiaAmpliada: 'Enlarged photo',
      cerrar: 'Close',
      anterior: 'Previous',
      siguiente: 'Next',
      tituloPagina: 'Gallery | Unidad Educativa Pestalozzi Ambato',
      descripcionPagina: 'Facilities, activities, sports, and events at Unidad Educativa Pestalozzi Ambato.',
    },
    nosotros: {
      tituloPagina: 'About Us | Unidad Educativa Pestalozzi Ambato',
      descripcionPagina: 'Who we are, the Pestalozzi philosophy in the classroom, mission and vision of Unidad Educativa Pestalozzi Ambato.',
    },
    inicio: {
      tituloPagina: 'Unidad Educativa Pestalozzi Ambato | Enrollment 2026 – 2027',
      descripcionPagina: 'A school in Ambato built on the Pestalozzi philosophy: the child at the center of their own learning. Enrollment open for the 2026 – 2027 school year.',
      ogTitulo: 'Unidad Educativa Pestalozzi Ambato',
      fotografiasDelColegio: 'Photos of the school',
      fotoAnterior: 'Previous photo',
      fotoSiguiente: 'Next photo',
      fotos: 'Photos',
      irALaFoto: (n: number) => `Go to photo ${n}`,
      deTotal: (i: number, total: number) => `${i} of ${total}`,
    },
  },
} as const;
