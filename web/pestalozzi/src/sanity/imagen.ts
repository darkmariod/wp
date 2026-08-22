import { createImageUrlBuilder } from '@sanity/image-url';
import type { Image } from '@sanity/types';
import { sanityClient } from 'sanity:client';

const constructor = createImageUrlBuilder(sanityClient);

export type ImagenSanity = Image & { alt?: string };

// Sanity sirve las imágenes redimensionadas desde su CDN: se sube una vez
// en tamaño original y cada lugar del sitio pide la medida que necesita,
// sin que el colegio tenga que recortar nada a mano.
export function urlImagen(fuente: ImagenSanity, ancho: number, alto?: number) {
  let url = constructor.image(fuente).width(ancho).auto('format').quality(78);
  if (alto) url = url.height(alto).fit('crop');
  return url.url();
}

// Alto proporcional para un ancho dado, respetando la relación de aspecto
// real del archivo subido. Sirve para poner width/height en el <img> y
// que la página no salte mientras carga (CLS).
export function dimensiones(fuente: ImagenSanity, ancho: number) {
  const ref = fuente?.asset?._ref ?? '';
  const medidas = ref.match(/-(\d+)x(\d+)-/);
  if (!medidas) return { width: ancho, height: undefined };
  const [, w, h] = medidas;
  const alto = Math.round((Number(h) / Number(w)) * ancho);
  return { width: ancho, height: alto };
}
