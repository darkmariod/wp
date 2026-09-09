# PROMPT — Identidad visual del portal Mi Escuelita

## CONTEXTO

Proyecto: `/Users/mariopazmino/Desktop/wp/aula` — corre en el puerto **8001**
Stack: Laravel 13.30 · Filament 5.8.1 · Livewire 4 · Tailwind v4 · MySQL

Usuarios de prueba (contraseña `password`):
`admin@pestalozzi.test` · `guia@pestalozzi.test` (guía) · `familia@pestalozzi.test`

El sistema **funciona**: panel Filament en español, 2 aulas con 30 niños, CRUD
completo, portal de familias en Livewire, subida de evidencias, 69 tests verdes.
Esta tarea **no toca funcionalidad**. Es solo identidad visual.

---

## EL PROBLEMA

El portal se ve correcto pero **genérico**: parece un dashboard SaaS, no el
espacio de un colegio Montessori. Es el dialecto por defecto de Tailwind.

No es falta de diseño. Es falta de **carácter**.

---

## DIAGNÓSTICO CONCRETO — qué delata el "hecho por IA"

### 1. Emojis como iconografía (el tell más fuerte)

Las áreas usan 📖 ➗ 🇬🇧 🧺 🎨 🌱. Los emojis se ven distinto en cada sistema
operativo, no se pueden colorear con la marca, y gritan "plantilla rápida".

El proyecto **ya tiene `blade-ui-kit/blade-heroicons` instalado**. Usarlo, o
mejor: dibujar un set propio de 6 íconos de línea, uno por área, con el mismo
grosor de trazo y la misma caja. Un set coherente es la diferencia más grande
entre "plantilla" y "producto".

### 2. El acento ámbar está definido y sin usar

`resources/css/app.css` ya declara:

```css
--color-accent-600: #D4922F;
--color-accent-500: #E8A33D;
--color-accent-100: #FCF0DA;
```

Y no aparece prácticamente en ninguna pantalla. Todo es verde sobre blanco.
**Ahí está la calidez que le falta al portal.** Ponerlo a trabajar: estados
destacados, la evidencia pendiente de respuesta, el área activa, acentos de
sección. No como color decorativo: con un significado asignado y consistente.

### 3. Todas las superficies son la misma tarjeta

Blanco, esquinas redondeadas, sombra suave, idéntico peso visual. Cuando todo
está elevado, nada destaca. Hay que crear jerarquía real:

- Lo primario puede tener sombra.
- Lo secundario, solo borde.
- Lo terciario, ni borde ni sombra: apoyado directo sobre el fondo `#FAFAF7`.
- Alguna sección puede invertirse (fondo verde profundo, texto claro) para
  cortar el ritmo.

### 4. Cero fotografía

Es un producto sobre chicos y no hay una sola imagen de chicos. Las tarjetas de
experiencia muestran un placeholder de destellos sobre verde pálido.

`Content` ya tiene `cover_image` y `Area` tiene `image`. Están sin explotar.
La foto debe ser protagonista, no decoración: portada grande en la tarjeta de
experiencia, con la tipografía encima o debajo, no un cuadrito al costado.

### 5. Tipografía sin contraste

DM Sans está bien elegida, pero se usa casi con el mismo peso y tamaño en todos
lados. Falta una escala editorial: títulos grandes y con carácter, cuerpo
tranquilo, metadatos chicos y apagados. El salto entre niveles tiene que ser
evidente, no sutil.

### 6. Layouts simétricos y seguros

Todo centrado, grillas parejas, márgenes iguales. Un poco de asimetría
deliberada —una columna más ancha, un título que se sale del contenedor, una
imagen que sangra al borde— hace que se sienta compuesto por una persona.

---

## DIRECCIÓN — a qué debería parecerse

Montessori es materiales naturales, madera, luz, calma, trabajo hecho a mano.
El portal debería sentirse **cálido y artesanal**, no clínico.

Anclas de referencia (no copiar, calibrar):
- El sitio público del colegio, si existe, manda: el aula debe sentirse parte
  de la misma familia visual, no un producto aparte.
- Estética editorial más que estética dashboard: pensar en una revista escolar
  bien hecha, no en un panel de métricas.

**Importante:** no se trata de agregar más adornos. Se trata de que las
decisiones se noten intencionales.

---

## TAREAS

1. **Set de íconos propio.** Reemplazar los 6 emojis de áreas por íconos de
   línea coherentes (Heroicons ya instalado, o SVG propios). Mismo grosor,
   misma caja, coloreables con los tokens.

2. **Activar el ámbar.** Asignarle un significado y aplicarlo consistente en
   todo el portal y el panel.

3. **Jerarquía de superficies.** Definir 3 niveles (elevado / con borde / plano)
   y aplicarlos según importancia. Que deje de ser todo la misma tarjeta.

4. **Fotografía protagonista.** Rediseñar la tarjeta de experiencia alrededor de
   `cover_image`. Definir un placeholder digno para cuando no haya foto: no un
   ícono genérico, algo que pertenezca a la marca.

5. **Escala tipográfica.** Definir y aplicar niveles con contraste real.

6. **Romper la simetría** en al menos el home y el listado de experiencias.

7. **Detalles de calidez.** Estados vacíos con voz propia (los actuales ya
   tienen buen tono, mantenerlo), microcopy en rioplatense —el portal ya usa
   "Contanos", "Subí", "Compartí"—, y transiciones discretas.

---

## RESTRICCIONES

- **No romper funcionalidad.** Los 69 tests tienen que seguir verdes.
- No sumar librerías de UI ni frameworks de componentes. Tailwind v4 y los
  tokens que ya existen alcanzan.
- Trabajar sobre los tokens de `resources/css/app.css`. Si hace falta sumar
  alguno, que entre al `@theme`, no hardcodeado en las vistas.
- Mantener la accesibilidad ya lograda: tap targets de 44px, `focus-ring`,
  contraste suficiente, `aria-label` en controles sin texto.
- Correr `npm run build` después de tocar clases: Tailwind v4 necesita
  recompilar o las clases nuevas no existen en el CSS.
- Identificadores y clases en inglés; copy de UI en español rioplatense.
- **No hacer `git push`.** Mostrar en local para revisión.

---

## VERIFICACIÓN

1. `npm run build && php artisan test` → 69 tests verdes.
2. Recorrer con `familia@pestalozzi.test`: home, experiencias, detalle,
   mis experiencias, notificaciones, perfil.
3. Recorrer `/admin` con `guia@pestalozzi.test`.
4. **Probar en 375px de ancho** además de escritorio. Confirmar que no aparece
   desbordamiento horizontal (`scrollWidth === clientWidth`).
5. **Abrir la consola del navegador: cero errores de JavaScript.**
6. Comparar capturas antes/después de cada pantalla.

> Nota de método: si verificás con el panel del navegador oculto,
> `document.hidden` queda en `true`, el navegador estrangula
> `requestAnimationFrame` y las transiciones de Alpine nunca se asientan.
> Medir `display` da falsos negativos. Para distinguir bug real de artefacto,
> leer el estado interno con `Alpine.$data(el)`.

---

## ANTES DE LA REVISIÓN — pendiente de seguridad

El archivo `.env` está **trackeado y ya pusheado** al repo público
`darkmariod/wp`, y `.gitignore` no tiene ninguna regla para él.

De todas las claves, la única con valor no vacío es **`APP_KEY`**
(`DB_PASSWORD`, `MAIL_PASSWORD`, `REDIS_PASSWORD` y las `AWS_*` están vacías
porque el entorno es local). El daño real hoy es acotado, pero antes de
cualquier despliegue hay que:

1. Agregar `.env` al `.gitignore`.
2. `git rm --cached aula/.env` y commitear.
3. Rotar `APP_KEY` con `php artisan key:generate`.
4. Decidir si además se purga del historial (reescribe historia pública:
   es decisión del dueño del repo, no automatizable a la ligera).

---

## HUECO DE TESTS DETECTADO

La regla de negocio más distintiva del sistema —que en Mentes Razonadoras solo
se permiten contenidos de tipo lectura y tarea— **no tiene ningún test**.

La validación de servidor existe y está bien cableada
(`ContentResource::esTipoPermitidoEnAmbiente()`, invocada desde
`Pages/CreateContent.php:19` y `Pages/EditContent.php:27`), pero nada la cubre.
Vale agregar un test que intente guardar un contenido tipo `experience` en
Mentes Razonadoras y confirme que se rechaza.
