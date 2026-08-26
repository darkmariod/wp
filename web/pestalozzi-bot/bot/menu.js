// El único archivo que el colegio puede pedirte que edites. Cada entrada
// es una opción del menú: qué palabras la disparan, y qué responde.
//
// tipo: 'texto' | 'imagen' | 'video'
// Para imagen/video, "media" es una URL pública (del sitio o de Sanity) —
// nunca subas el archivo a este servidor, se sirve desde ahí.

module.exports = {
  bienvenida: `Hola 👋 Soy el asistente de la Unidad Educativa Pestalozzi.
Respondé con un número:

1  Ubicación y cómo llegar
2  Horarios de atención
3  Niveles educativos
4  Requisitos de matrícula
5  Ver fotos del plantel
6  Ver un video del colegio
7  Hablar con una persona`,

  // Si un mensaje no calza con ninguna opción, se repite esto.
  noEntendido: `No entendí ese mensaje 🙏 Respondé con un número del 1 al 7, o escribí "menu" para ver las opciones otra vez.`,

  opciones: [
    {
      disparadores: ['1', 'ubicacion', 'ubicación', 'direccion', 'dirección', 'como llegar', 'cómo llegar'],
      tipo: 'texto',
      contenido: 'Estamos en Tiwinza #95 y Etza, Ambato. 📍 https://maps.google.com/?q=Tiwinza+95+y+Etza+Ambato',
    },
    {
      disparadores: ['2', 'horario', 'horarios', 'atencion', 'atención'],
      tipo: 'texto',
      contenido: 'Secretaría atiende de lunes a viernes, de 07:30 a 13:00.',
    },
    {
      disparadores: ['3', 'niveles', 'nivel'],
      tipo: 'texto',
      contenido: 'Ofrecemos Educación Inicial, Preparatoria, Básica y Bachillerato. Escribí "matrícula" si querés los requisitos.',
    },
    {
      disparadores: ['4', 'matricula', 'matrícula', 'requisitos', 'inscripcion', 'inscripción'],
      tipo: 'texto',
      contenido: 'Para matricularte necesitás: cédula del estudiante, cédula del representante, certificado de notas del año anterior y una foto tamaño carnet.',
    },
    {
      disparadores: ['5', 'fotos', 'foto', 'imagenes', 'imágenes'],
      tipo: 'imagen',
      contenido: 'https://pestalozzi-opal.vercel.app/img/galeria/g01.webp',
      caption: 'Nuestras instalaciones 🏫',
    },
    {
      disparadores: ['6', 'video', 'vídeo', 'videos', 'vídeos'],
      tipo: 'video',
      contenido: 'https://pestalozzi-opal.vercel.app/img/hero/hero-escritorio.mp4',
      caption: 'Conocé nuestro colegio 🎥',
    },
    {
      disparadores: ['7', 'persona', 'humano', 'hablar', 'secretaria', 'secretaría'],
      tipo: 'texto',
      contenido: 'En un momento te atiende una persona del colegio. Gracias por tu paciencia 🙏',
      pausaBot: true, // deja de responder automático en esta conversación
    },
  ],
};
