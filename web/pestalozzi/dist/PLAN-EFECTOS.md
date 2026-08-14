# Plan de efectos — Sitio Pestalozzi Ambato

**Versión:** 1.0
**Fecha:** 2026-08-14
**Base:** el sitio ya tiene capa GSAP + ScrollTrigger cargando solo en escritorio,
5 ScrollTriggers activos, marquesina funcionando y `prefers-reduced-motion` global.

---

## Regla que gobierna los cinco efectos

Todo lo que sigue vive en la **capa enriquecida**: solo escritorio
(`pointer: fine` + `min-width: 1024px`), nunca con `prefers-reduced-motion`.

En celular el sitio se queda con la capa base — revelado y micro-interacciones.
Eso no es una limitación técnica: es que los efectos de scroll pesado se sienten
mal en scroll táctil inercial y consumen batería. El 70 % de los representantes
entra desde el teléfono.

**El sitio debe seguir funcionando completo con los cinco efectos apagados.**
Si un efecto es la única forma de leer un contenido, está mal construido.

---

## E1 · La sección de abajo sube y tapa a la anterior

### Qué es
Al bajar, la sección siguiente se desliza por encima de la actual en lugar de
empujarla. La de abajo "gana" la pantalla.

### Cómo se hace
ScrollTrigger con `pin` sobre la sección saliente + `y` sobre la entrante.
Las secciones necesitan `position: relative` y `z-index` ascendente.

```js
gsap.utils.toArray('.apila').forEach((seccion, i) => {
  ScrollTrigger.create({
    trigger: seccion,
    start: 'top top',
    pin: true,
    pinSpacing: false,     // clave: sin esto el apilado no ocurre
    scrub: true,
  });
});
```

`pinSpacing: false` es lo que hace que la siguiente suba encima. Con el valor
por defecto (`true`) GSAP reserva el espacio y el efecto no se ve.

### Dónde aplicarlo
**Máximo dos transiciones**, no en toda la página:
- Portada → Propuesta educativa
- Galería → Franja de matrículas

### Riesgo real
Es el efecto más caro de los cinco. `pin` cambia el flujo del documento:
rompe `position: sticky` del header, pelea con el ancla `#propuesta` y puede
descuadrar el cálculo de altura si una imagen carga tarde.

**Mitigación obligatoria:** `ScrollTrigger.refresh()` después de `load`, y
`width`/`height` explícitos en toda imagen dentro de secciones apiladas.

### Costo
Medio-alto. Es el que más pruebas necesita.

---

## E2 · Palabras clave con color y animación

### Qué es
Dentro de un titular o párrafo, ciertas palabras se pintan de color y se animan
cuando entran en pantalla.

### Cómo se hace
Marcado semántico en el HTML, no clases sueltas:

```html
<h2>Aquí crezco <mark class="clave">creando</mark> y
    aprendiendo a <mark class="clave">hacer</mark></h2>
```

El relleno se anima con `background-size`, que corre en GPU:

```css
.clave {
  color: inherit;
  background: linear-gradient(var(--accent-500), var(--accent-500)) no-repeat;
  background-size: 0% 38%;
  background-position: 0 88%;
  transition: background-size .7s var(--ease-out);
}
.clave.pintada { background-size: 100% 38%; }
```

La clase `.pintada` la agrega el mismo IntersectionObserver que ya existe.
**Este efecto NO necesita GSAP** — funciona también en celular.

### Dónde aplicarlo
Las palabras del propio colegio: **creando · hacer · confianza · autonomía ·
alegría · amor · valor**. Máximo dos por bloque de texto.

### Por qué `<mark>` y no `<span>`
`<mark>` significa "texto resaltado por relevancia". Un lector de pantalla lo
anuncia; un `<span>` con color es invisible para quien no ve el color.

### Costo
Bajo. Es el de mejor relación impacto/esfuerzo de los cinco.

---

## E3 · Fondo fijo mientras el contenido pasa por encima

### Qué es
Una fotografía queda quieta y el texto se desliza sobre ella.

### Cómo se hace
**No usar `background-attachment: fixed`.** En iOS no funciona y en Android
provoca repintados que trban el scroll.

La forma correcta es un contenedor con `clip-path` y la imagen en `position: fixed`
dentro, o ScrollTrigger con `pin` sobre la capa de imagen:

```css
.fondo-fijo { position: relative; overflow: clip; }
.fondo-fijo__img {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  object-fit: cover;
  will-change: transform;
}
```

```js
gsap.to('.fondo-fijo__img', {
  yPercent: 12, ease: 'none',
  scrollTrigger: { trigger: '.fondo-fijo', scrub: true },
});
```

### Dónde aplicarlo
Una sola vez: la franja de matrículas. Ya tiene foto y velo, así que es el
lugar natural y no agrega peso nuevo.

### Regla de contraste
El texto encima necesita AA sobre **la zona más clara** de la foto, no sobre
el promedio. Si no da, se sube la opacidad del velo — no se baja el requisito.

### Costo
Bajo si se limita a una sección. Alto si se repite.

---

## E4 · Botón flotante de contacto que se despliega

### Qué es
Lo de la imagen: un botón circular fijo que al tocarlo abre en abanico
WhatsApp, teléfono y correo.

### Cómo se hace
CSS + JS puro. **Sin GSAP** — debe funcionar en celular, que es donde más sirve.

```html
<div class="fab" data-abierto="false">
  <a class="fab__opcion" href="https://wa.me/…" aria-label="WhatsApp">…</a>
  <a class="fab__opcion" href="tel:+…"          aria-label="Llamar">…</a>
  <a class="fab__opcion" href="mailto:…"        aria-label="Correo">…</a>
  <button class="fab__disparador" aria-expanded="false"
          aria-label="Abrir opciones de contacto">…</button>
</div>
```

Cada opción sale con `translateY` escalonado 60 ms. Cerrado quedan en
`scale(0)` con `pointer-events: none`.

### Requisitos que no son opcionales
- `aria-expanded` en el disparador, sincronizado
- Cierra con `Escape` y al tocar fuera
- Foco atrapado mientras está abierto
- Cada opción con `aria-label` propio: un ícono sin nombre no existe para un lector de pantalla
- Objetivos de 48 px mínimo

### Nota
Reemplaza al botón de WhatsApp actual, no se suma. Dos botones flotantes
compitiendo es peor que uno.

### Costo
Bajo. Y es el efecto con **retorno comercial más directo**: un papá que quiere
llamar lo hace en un toque desde cualquier página.

---

## E5 · Logos en movimiento

### Un problema de fondo
Un colegio **no tiene clientes**. Poner una banda de logos ajenos sin relación
real es exactamente el tipo de relleno que hace que un sitio se sienta genérico.

### Qué sí tiene sentido
Solo si existen y se pueden acreditar:
- Ministerio de Educación / código AMIE
- Convenios o alianzas reales
- Certificaciones
- Instituciones donde continúan los egresados

**Si no hay nada de esto, este efecto no va.** Es preferible una franja con una
cifra real ("111 estudiantes", cuando el colegio la confirme) que logos de
relleno.

### Cómo se haría
La marquesina ya existe y funciona: mismo `@keyframes desfile`, cambiando texto
por `<img>` en escala de grises que recuperan color al pasar el mouse.

```css
.logos__item img { filter: grayscale(1); opacity: .65; transition: .3s var(--ease-out); }
.logos__item:hover img { filter: none; opacity: 1; }
```

### Costo
Muy bajo (reutiliza la marquesina). El problema no es técnico, es de contenido.

---

## Orden de trabajo

| # | Efecto | Costo | Impacto | Bloqueado por |
|---|---|---|---|---|
| 1 | E4 · Botón de contacto | Bajo | **Alto** | Teléfono oficial |
| 2 | E2 · Palabras clave | Bajo | Alto | — |
| 3 | E3 · Fondo fijo | Bajo | Medio | — |
| 4 | E1 · Apilado de secciones | **Alto** | Medio | — |
| 5 | E5 · Logos | Bajo | Bajo | **Que existan logos reales** |

Sugerencia: E4 y E2 primero. Se ven en el celular, que es donde mira el cliente,
y no tocan la arquitectura de scroll.

---

## Presupuesto que no se negocia

Los cinco efectos suman JS. El techo sigue siendo el del plan original:

| Métrica | Tope |
|---|---|
| JS propio | **< 30 KB** (hoy 21,6 KB — quedan ~8 KB) |
| JS de terceros en celular | **0 KB** |
| LCP en 4G | < 1,5 s |
| CLS | < 0,05 |
| Lighthouse Mobile | ≥ 90 |

E1 y E3 son los que pueden mover el CLS, porque `pin` altera el layout.
**Se mide con Lighthouse Mobile + 4G simulado después de cada efecto**, no al final.
Si un efecto baja el número, ese efecto se recorta.

---

## Lo que sigue pendiente del cliente

Ninguno de los cinco efectos depende de esto, pero el sitio sí:

- Teléfono oficial (hoy hay placeholder `099 000 0000`) — **bloquea E4**
- Fotos en alta resolución con autorización firmada
- Logo en vector
- Niveles, misión, visión, horario
