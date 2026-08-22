import type { SchemaTypeDefinition } from 'sanity';

import { franja, imagenConAlt, pilar, portada } from './objetos';
import { configuracion } from './configuracion';
import { foto } from './foto';
import { paginaContacto, paginaGaleria, paginaInicio, paginaNosotros } from './paginas';

export const schemaTypes: SchemaTypeDefinition[] = [
  // Bloques reutilizables
  pilar,
  imagenConAlt,
  franja,
  portada,
  // Documentos
  configuracion,
  foto,
  paginaInicio,
  paginaNosotros,
  paginaGaleria,
  paginaContacto,
];
