// Corrección puntual de contenido en Sanity — 21/09/2026.
//
// Varios títulos usaban mayúscula de título estilo inglés ("Donde Cada
// Estudiante Recibe...") en vez de la regla del español (solo la
// primera palabra y los nombres propios van con mayúscula). También
// había un typo suelto ("...plantel.a") y la dirección duplicaba
// "Ecuador" en todo el sitio porque el campo `ciudad` ya traía
// ", Ecuador" incluido y las plantillas lo agregan aparte.
//
// Script de un solo uso, idempotente: correrlo de nuevo deja los
// mismos valores correctos. Se conserva en el repo como registro de
// qué se corrigió y por qué, no como parte del build.
//
// Uso: npx sanity exec scripts/2026-09-21-fix-ortografia.mjs --with-user-token
import { getCliClient } from 'sanity/cli';

const client = getCliClient({ apiVersion: '2025-08-15' });

const paginaInicioId = await client.fetch('*[_type=="paginaInicio"][0]._id');
const paginaNosotrosId = await client.fetch('*[_type=="paginaNosotros"][0]._id');
const paginaContactoId = await client.fetch('*[_type=="paginaContacto"][0]._id');
const paginaVoluntariadoId = await client.fetch('*[_type=="paginaVoluntariado"][0]._id');
const configuracionId = await client.fetch('*[_type=="configuracion"][0]._id');

await client
  .patch(paginaInicioId)
  .set({
    'porQueEyebrow.es': '¿Por qué Pestalozzi?',
    'porQueEyebrow.en': 'Why Pestalozzi?',
    'porQueTitulo.es': 'Donde cada estudiante recibe una formación personalizada',
    'nivelesEyebrow.es': 'Nuestra oferta académica',
    'nivelesTitulo.es': 'Un sendero de aprendizaje integral',
    'galeriaTitulo.es': 'El día a día en Pestalozzi',
  })
  .commit();
console.log('paginaInicio corregida');

await client
  .patch(paginaNosotrosId)
  .set({
    'filosofiaEyebrow.es': 'Modelo pedagógico',
    'filosofiaTitulo.es': 'La filosofía Pestalozzi en el aula',
  })
  .commit();
console.log('paginaNosotros corregida');

await client
  .patch(paginaContactoId)
  .set({
    'formularioTitulo.es': 'Déjenos un mensaje',
    'portada.bajada.es':
      'Respondemos sus mensajes con mucho gusto el mismo día. Si desea visitarnos en persona, le recibiremos con los brazos abiertos para guiarle por el plantel.',
  })
  .commit();
console.log('paginaContacto corregida');

await client
  .patch(paginaVoluntariadoId)
  .set({ 'introTitulo.es': 'Súmate a nuestra comunidad' })
  .commit();
console.log('paginaVoluntariado corregida');

await client.patch(configuracionId).set({ ciudad: 'Ambato' }).commit();
console.log('configuracion.ciudad corregida (era "Ambato, Ecuador", duplicaba el país en todo el sitio)');
