# Cómo armar el archivo de Figma

## Antes de empezar

Necesitas iniciar sesión en Figma. Ese paso lo haces tú: pulsa **"Log in with browser"**
en la app de escritorio. Sin sesión, Figma no deja crear archivos.

## Qué hay en esta carpeta

| Carpeta | Para qué sirve |
|---|---|
| `pantallas/` | **Las pantallas limpias.** Sin números ni explicaciones. Son las que editas y presentas como demo. |
| Esta carpeta (`1-inicio.svg` … `6-precio.svg`) | **Las láminas explicadas.** Con números y leyenda. Sirven para sustentar el diseño en la reunión. |
| `../*.png` | Las mismas láminas en imagen, para WhatsApp o proyectar. |

## Armado del archivo (10 minutos)

1. Crea un archivo nuevo: `Pestalozzi Ambato — Demo`.
2. Crea dos páginas en el panel izquierdo:
   - `Demo` → aquí van los 8 archivos de `pantallas/`
   - `Sustento` → aquí van las 6 láminas explicadas
3. Arrastra los `.svg` desde el Finder al lienzo de cada página.
4. Selecciona cada uno y pulsa `Ctrl/Cmd + Alt + G` para convertirlo en **Frame**.
   Así se comporta como pantalla y puedes prototipar encima.

## Tamaños que vas a ver

| Archivo | Medida |
|---|---|
| `01-inicio-desktop` | 1080 × 1210 |
| `02-nosotros-desktop` | 1080 × 1208 |
| `03-galeria-desktop` | 1080 × 954 |
| `03b-foto-ampliada` | 1080 × 300 (el estado al tocar una foto) |
| `04-contacto-desktop` | 1080 × 1014 |
| `05` / `06` / `07` móvil | 300 × 620 cada uno |

Si quieres el estándar de 1440 de ancho, selecciona el frame de escritorio y cambia
el ancho en el panel derecho con el candado de proporción activado.

## Cómo prototipar la demo

En la pestaña **Prototype**, arrastra una conexión desde cada enlace del menú hacia
su pantalla. Con eso el cliente navega el sitio en `Presentar` como si ya existiera.
Sugerido: menú → 4 pantallas, foto de galería → `03b-foto-ampliada`, menú móvil → `07`.

## Detalles

- La fuente declarada es **Inter**, que Figma ya trae. Si pide sustituir, cambia a Inter
  (o a Poppins en los títulos, según `design-plan.md`).
- Los grupos vienen nombrados y en orden: `01 Barra de navegacion`, `02 Portada`, etc.
- Los bloques grises con equis son **marcadores de foto**: se reemplazan con
  `Fill > Image` sobre el rectángulo.
- Son wireframes a propósito: grises, sin foto real ni tipografía final. El color de
  marca (`#126333`) y la tipografía están definidos en `design-plan.md`.
