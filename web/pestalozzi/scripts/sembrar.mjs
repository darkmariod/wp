// Carga dentro de Sanity el contenido que hoy está escrito a mano en las
// páginas .astro. Se corre UNA vez, al migrar.
//
// Uso:  SANITY_TOKEN=xxx node scripts/sembrar.mjs
//
// El token se saca de sanity.io/manage → proyecto → API → Tokens,
// con permiso de Editor. No se guarda en el repo.
import { createClient } from '@sanity/client';
import { readFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const token = process.env.SANITY_TOKEN;

if (!token) {
  console.error('Falta SANITY_TOKEN. Uso: SANITY_TOKEN=xxx node scripts/sembrar.mjs');
  process.exit(1);
}

const cliente = createClient({
  projectId: '513m7736',
  dataset: 'production',
  apiVersion: '2024-01-01',
  token,
  useCdn: false,
});

// Sube una imagen de public/ y devuelve la referencia lista para un campo
// de tipo imagenConAlt.
const subidas = new Map();
async function subir(rutaRelativa, alt) {
  if (!subidas.has(rutaRelativa)) {
    const archivo = await readFile(join(raiz, 'public', rutaRelativa));
    const nombre = rutaRelativa.split('/').pop();
    const asset = await cliente.assets.upload('image', archivo, { filename: nombre });
    subidas.set(rutaRelativa, asset._id);
    console.log(`  subida ${rutaRelativa} → ${asset._id}`);
  }
  return {
    _type: 'imagenConAlt',
    asset: { _type: 'reference', _ref: subidas.get(rutaRelativa) },
    alt,
  };
}

const pilar = (titulo, texto) => ({ _type: 'pilar', _key: titulo.toLowerCase().replace(/\W/g, ''), titulo, texto });

console.log('Subiendo imágenes...');

const fotoPortada = await subir('img/hero/portada.webp', 'Estudiantes de Pestalozzi jugando en el patio de la institución');
const fotoNosotros = await subir('img/hero/nosotros.webp', 'Fachada de la Unidad Educativa Pestalozzi con la bandera del Ecuador');
const fotoGaleria = await subir('img/hero/galeria.webp', 'Estudiantes en el aula de la institución');
const fotoContacto = await subir('img/hero/contacto.webp', 'Estudiante sonriendo en la casita del patio');

const g01 = await subir('img/galeria/g01.webp', 'Niños corriendo en el patio de césped de la institución');
const g02 = await subir('img/galeria/g02.webp', 'Niño asomado en la casita de madera del patio');
const g03 = await subir('img/galeria/g03.webp', 'Estudiante escribiendo números en la pizarra digital del aula');
const g04 = await subir('img/galeria/g04.webp', 'Estudiante armando una estructura con piezas de construcción');
const g05 = await subir('img/galeria/g05.webp', 'Fachada del plantel con la bandera del Ecuador');
const g06 = await subir('img/galeria/g06.webp', 'Estudiante jugando en la resbaladera del patio');

console.log('Creando documentos...');

const documentos = [
  {
    _id: 'configuracion',
    _type: 'configuracion',
    telefono: '099 824 6396',
    telefonoInternacional: '593998246396',
    correo: '18h00063@gmail.com',
    direccionCalle: 'Tiwinza #95 y Etza',
    direccionSector: 'Parroquia Atahualpa',
    ciudad: 'Ambato',
    facebook: 'https://facebook.com/pestalozziambato',
    tiktok: 'https://www.tiktok.com/@pestalozziambato',
    avisoMatriculasTitulo: 'Matrículas abiertas',
    avisoMatriculasTexto: 'Ciclo lectivo 2026 – 2027',
    textoBotonAdmisiones: 'Admisiones 2026–2027',
  },
  {
    _id: 'paginaInicio',
    _type: 'paginaInicio',
    tituloLinea1: 'Mi Escuelita',
    tituloLinea2: 'Pestalozzi',
    bajada: 'Aquí crezco creando y aprendiendo a hacer, con confianza y autonomía.',
    fotoPortada,
    propuestaTitulo: 'Educar la cabeza, el corazón y las manos',
    propuestaTexto:
      'Pestalozzi no es solo el nombre de la institución: es una forma en la que el niño es dueño de su propio aprendizaje.',
    propuestaPilares: [
      pilar('Cabeza', 'Observación, razonamiento y lenguaje propio. El estudiante entiende antes de memorizar, y pregunta sin miedo a equivocarse.'),
      pilar('Corazón', 'Cada día crecemos con alegría, con amor y con valor.'),
      pilar('Manos', 'Se aprende haciendo: dibujar, construir, experimentar y jugar. El conocimiento se fija cuando pasa por las manos.'),
    ],
    nivelesEyebrow: 'Oferta educativa',
    nivelesTitulo: 'Un camino para cada etapa',
    nivelesTexto: 'Metodología Montessori, grupos reducidos y acompañamiento cercano en cada nivel:',
    nivelesChips: ['Inicial 1', 'Inicial 2', 'Primero de Básica', 'Segundo de Básica'],
    nivelesFotoIzquierda: g02,
    nivelesBloqueVerde: pilar('Educación Inicial', 'Inicial 1 e Inicial 2. Los primeros años son los que más pesan: espacios seguros y lúdicos para explorar el mundo con confianza.'),
    nivelesBloqueAmbar: pilar('Primero y Segundo de Básica', 'Formación con metodología Montessori, en grupos reducidos y con acompañamiento cercano en cada etapa.'),
    nivelesFotoDerecha: g03,
    marquesinaFrases: [
      'Cada día crezco', 'con alegría', 'con amor', 'y con valor',
      'creando', 'aprendiendo a hacer', 'con confianza', 'y autonomía',
    ],
    porQueEyebrow: 'Por qué Pestalozzi',
    porQueTitulo: 'Una escuelita donde a cada niño se lo conoce por su nombre',
    porQueParrafos: [
      'No somos el nombre de un pedagogo colgado en la fachada. Aplicamos su método: el niño observa, comprende y hace.',
      'Estamos en una ladera de Ambato, con amplios espacios verdes, huerto y vista a la ciudad. Aquí los niños salen a correr todos los días.',
    ],
    porQueFoto: g04,
    galeriaEyebrow: 'Vida escolar',
    galeriaTitulo: 'La vida en Pestalozzi',
    galeriaTexto: 'Instalaciones, actividades, deportes y eventos del año lectivo.',
    franja: {
      _type: 'franja',
      titulo: 'Matrículas abiertas · Ciclo 2026 – 2027',
      texto: 'Conoce la institución antes de decidir. Agenda una visita y recorre las aulas con nosotros.',
      textoBoton: 'Escríbenos por WhatsApp',
      foto: g01,
    },
  },
  {
    _id: 'paginaNosotros',
    _type: 'paginaNosotros',
    portada: {
      _type: 'portada',
      titulo: 'Conócenos',
      bajada: 'Una escuelita de barrio en Ambato, con la filosofía que puso al niño primero.',
      foto: fotoNosotros,
    },
    quienesEyebrow: 'Quiénes somos',
    quienesTitulo: 'Mi escuelita Pestalozzi',
    quienesParrafos: [
      'Somos una unidad educativa de la ciudad de Ambato dedicada a la formación integral de niñas y niños. Trabajamos con grupos reducidos, seguimiento individual y una relación cercana con cada familia.',
      'Nuestro plantel está en una ladera con patio de césped, huerto, juegos y vista a la ciudad. Los niños salen a correr todos los días: aquí el movimiento es parte del aprendizaje, no un premio.',
    ],
    quienesFoto: g05,
    filosofiaEyebrow: 'El método',
    filosofiaTitulo: 'La filosofía Pestalozzi en el aula',
    filosofiaTexto:
      'Johann Heinrich Pestalozzi propuso que el niño debe ser el centro de su propio aprendizaje, formándose en tres dimensiones a la vez.',
    filosofiaPilares: [
      pilar('Cabeza', 'El pensamiento se construye desde la observación directa. Primero el niño mira y describe con sus palabras; recién después llega el concepto.'),
      pilar('Corazón', 'La formación moral y afectiva no es una materia aparte. Se aprende en el trato diario, en cómo se resuelve un conflicto y en cómo se celebra un logro.'),
      pilar('Manos', 'La habilidad práctica cierra el círculo. Dibujar, construir, sembrar y experimentar fijan lo aprendido mejor que cualquier repetición.'),
    ],
    franja: {
      _type: 'franja',
      titulo: '¿Te gustaría conocernos en persona?',
      texto: 'Agenda una visita guiada y recorre las aulas y el patio con nosotros.',
      textoBoton: 'Escríbenos por WhatsApp',
      foto: g06,
    },
  },
  {
    _id: 'paginaGaleria',
    _type: 'paginaGaleria',
    portada: {
      _type: 'portada',
      titulo: 'Galería',
      bajada: 'Instalaciones, actividades, deportes y eventos del año lectivo.',
      foto: fotoGaleria,
    },
    descTodas: 'Un vistazo a la vida diaria del plantel: instalaciones, actividades, deportes y eventos del año lectivo.',
    descInstalaciones: 'El espacio donde los niños pasan el día: patio, aulas y áreas comunes del plantel.',
    descActividades: 'Momentos de aprendizaje dentro y fuera del aula, con la metodología Montessori.',
    descDeportes: 'Movimiento y juego al aire libre — parte del aprendizaje, no un premio aparte.',
    descEventos: 'Celebraciones y actividades especiales del año lectivo. Se suman fotos aquí en cuanto el colegio las entregue.',
    franja: {
      _type: 'franja',
      titulo: '¿Quieres conocer el plantel en persona?',
      texto: 'Las fotos ayudan, pero recorrer el patio y las aulas convence más.',
      textoBoton: 'Agenda tu visita',
      foto: g02,
    },
  },
  {
    _id: 'paginaContacto',
    _type: 'paginaContacto',
    portada: {
      _type: 'portada',
      titulo: 'Hablemos',
      bajada: 'Respondemos en el día. También puedes visitarnos sin cita previa.',
      foto: fotoContacto,
    },
    datosTitulo: 'Dónde estamos',
    formularioTitulo: 'Envíanos un mensaje',
    nivelesFormulario: ['Inicial 1', 'Inicial 2', 'Primero de Básica', 'Segundo de Básica', 'Aún no lo sé'],
  },
];

// Las 6 fotos de la galería, con la categoría que ya tenían en galeria.astro.
const fotos = [
  { imagen: g01, categoria: 'deportes', orden: 10 },
  { imagen: g02, categoria: 'instalaciones', orden: 20 },
  { imagen: g03, categoria: 'actividades', orden: 30 },
  { imagen: g04, categoria: 'actividades', orden: 40 },
  { imagen: g05, categoria: 'instalaciones', orden: 50 },
  { imagen: g06, categoria: 'deportes', orden: 60 },
];

fotos.forEach((f, i) => {
  documentos.push({ _id: `foto-${String(i + 1).padStart(2, '0')}`, _type: 'foto', ...f });
});

// createOrReplace: el script se puede volver a correr sin duplicar nada.
const tx = documentos.reduce((t, doc) => t.createOrReplace(doc), cliente.transaction());
await tx.commit();

console.log(`\nListo: ${documentos.length} documentos creados.`);
console.log('Revisa el resultado en /admin');
