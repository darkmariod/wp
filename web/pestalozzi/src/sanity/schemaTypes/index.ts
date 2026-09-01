import type { SchemaTypeDefinition } from 'sanity';

import { datoPractico, franja, imagenConAlt, pilar, portada, programa } from './objetos';
import { configuracion } from './configuracion';
import { foto } from './foto';
import { paginaContacto, paginaGaleria, paginaInicio, paginaNosotros } from './paginas';

export const schemaTypes: SchemaTypeDefinition[] = [
  // Bloques reutilizables
  pilar,
  programa,
  datoPractico,
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
