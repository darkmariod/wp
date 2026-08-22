import { sanityClient } from 'sanity:client';

// Las consultas corren en el build, no en cada visita: el sitio sigue
// siendo HTML estático. Cuando el colegio publica un cambio en el Studio,
// Vercel reconstruye y el cambio aparece.

const CAMPOS_IMAGEN = `{ ..., alt }`;
const CAMPOS_FRANJA = `{ titulo, texto, textoBoton, foto ${CAMPOS_IMAGEN} }`;
const CAMPOS_PORTADA = `{ titulo, bajada, foto ${CAMPOS_IMAGEN} }`;

export async function traerConfiguracion() {
  return sanityClient.fetch(`*[_type == "configuracion"][0]`);
}

export async function traerInicio() {
  return sanityClient.fetch(`*[_type == "paginaInicio"][0]{
    tituloLinea1, tituloLinea2, bajada,
    fotoPortada ${CAMPOS_IMAGEN},
    propuestaTitulo, propuestaTexto, propuestaPilares,
    nivelesEyebrow, nivelesTitulo, nivelesTexto, nivelesChips,
    nivelesFotoIzquierda ${CAMPOS_IMAGEN},
    nivelesBloqueVerde, nivelesBloqueAmbar,
    nivelesFotoDerecha ${CAMPOS_IMAGEN},
    marquesinaFrases,
    porQueEyebrow, porQueTitulo, porQueParrafos,
    porQueFoto ${CAMPOS_IMAGEN},
    galeriaEyebrow, galeriaTitulo, galeriaTexto,
    franja ${CAMPOS_FRANJA}
  }`);
}

export async function traerNosotros() {
  return sanityClient.fetch(`*[_type == "paginaNosotros"][0]{
    portada ${CAMPOS_PORTADA},
    quienesEyebrow, quienesTitulo, quienesParrafos,
    quienesFoto ${CAMPOS_IMAGEN},
    filosofiaEyebrow, filosofiaTitulo, filosofiaTexto, filosofiaPilares,
    franja ${CAMPOS_FRANJA}
  }`);
}

export async function traerGaleria() {
  return sanityClient.fetch(`*[_type == "paginaGaleria"][0]{
    portada ${CAMPOS_PORTADA},
    descTodas, descInstalaciones, descActividades, descDeportes, descEventos,
    franja ${CAMPOS_FRANJA}
  }`);
}

export async function traerContacto() {
  return sanityClient.fetch(`*[_type == "paginaContacto"][0]{
    portada ${CAMPOS_PORTADA},
    datosTitulo, formularioTitulo, nivelesFormulario
  }`);
}

export async function traerFotos() {
  return sanityClient.fetch(
    `*[_type == "foto"] | order(orden asc){ _id, categoria, orden, imagen ${CAMPOS_IMAGEN} }`
  );
}
