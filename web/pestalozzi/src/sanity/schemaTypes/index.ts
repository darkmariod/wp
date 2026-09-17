import type { SchemaTypeDefinition } from 'sanity';

import { datoPractico, franja, imagenConAlt, pilar, portada, programa, textoLocalizado, parrafoLocalizado, videoFondo } from './objetos';
import { configuracion } from './configuracion';
import { categoriaGaleria } from './categoriaGaleria';
import { foto } from './foto';
import { paginaContacto, paginaGaleria, paginaInicio, paginaNosotros, paginaVoluntariado } from './paginas';

export const schemaTypes: SchemaTypeDefinition[] = [
  // Bloques reutilizables
  textoLocalizado,
  parrafoLocalizado,
  pilar,
  programa,
  datoPractico,
  videoFondo,
  imagenConAlt,
  franja,
  portada,
  // Documentos
  configuracion,
  categoriaGaleria,
  foto,
  paginaInicio,
  paginaNosotros,
  paginaGaleria,
  paginaContacto,
  paginaVoluntariado,
];
