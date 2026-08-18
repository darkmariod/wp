# Plan — Menú de contacto asistido (chatbot ligero)

**Versión:** 1.0
**Fecha:** 2026-08-18
**Decisiones ya tomadas con el cliente:**
- Respuestas fijas y predefinidas. Sin backend, sin IA, sin costo mensual.
- "Agendar cita" no reserva un cupo real: abre el menú con un mensaje
  predefinido que el usuario envía por WhatsApp para coordinar con la
  secretaria.

---

## 1. Qué es realmente esto

No es un chatbot en el sentido de "conversación libre". Es un **menú de
opciones con respuesta instantánea**, con la forma visual de un chat.
La diferencia importa: no hay caja de texto libre, no hay interpretación
de lenguaje, no hay nada que pueda responder mal. El usuario **toca**,
no escribe.

Esto es intencional y es la decisión correcta para este proyecto: un
chatbot que interpreta texto libre sin IA real detrás inevitablemente
da respuestas vacías o absurdas ante la primera pregunta rara, y eso
daña más la confianza que no tener el bot.

## 2. Relación con lo que ya existe

Ya tienes un botón de contacto flotante (`.fab`) con 3 opciones en arco:
WhatsApp, llamar, correo. Hay dos caminos:

| Opción | Qué implica |
|---|---|
| **A. El FAB se convierte en el disparador del menú** | Al tocarlo, en vez de abrir el arco de 3 iconos, abre el panel de chat. Un solo punto de contacto en el sitio, más simple para el visitante. |
| **B. Se agrega un segundo botón flotante** | El FAB sigue igual, y aparece otro botón (ej. burbuja de chat) que abre el menú. Dos botones flotantes en la esquina compiten por atención — ya lo señalé como error en el plan de efectos anterior. |

**Recomiendo A.** Absorbe el FAB: el panel de chat, en su primera pantalla,
ya ofrece "Escribir por WhatsApp ahora" como una opción más del menú.
No se pierde nada de lo que el FAB hacía, se ordena mejor.

## 3. Estructura del menú

```
┌─────────────────────────────────┐
│  Pestalozzi Ambato          [X] │
│  ¿En qué te ayudamos?           │
├─────────────────────────────────┤
│  📅  Agendar una visita          │
│  ❓  Preguntas frecuentes         │
│  📞  Hablar con secretaría        │
└─────────────────────────────────┘
```

Cada opción lleva a una segunda pantalla dentro del mismo panel (no a
otra página), con un botón "‹ Volver" para regresar al menú.

### 3.1 · Agendar una visita

```
┌─────────────────────────────────┐
│  ‹ Volver                        │
│                                  │
│  Para agendar tu visita,        │
│  escríbenos por WhatsApp y      │
│  coordinamos el mejor horario.  │
│                                  │
│  [ Escribir por WhatsApp ]      │
└─────────────────────────────────┘
```

El botón abre `wa.me/593998246396` con texto precargado:
`"Hola, quisiera agendar una visita al colegio"`.

### 3.2 · Preguntas frecuentes

Lista de 6-8 preguntas. Al tocar una, se expande la respuesta ahí mismo
(acordeón), sin salir del panel:

| Pregunta | Fuente de la respuesta |
|---|---|
| ¿Qué niveles educativos ofrecen? | **Bloqueada** — depende del pendiente #1 de la entrega |
| ¿Cuál es el horario de atención? | **Bloqueada** — pendiente #2 |
| ¿Dónde están ubicados? | Ya la tenemos: Tiwinza #95 y Etza, Ambato |
| ¿Cómo es el proceso de matrícula? | Falta que el colegio la redacte |
| ¿Tienen transporte escolar? | Falta confirmar con el colegio |
| ¿Cuál es el costo de la pensión? | Falta confirmar — colegios suelen preferir dar esto solo por contacto directo, no publicado |

**Esto es lo que de verdad bloquea empezar a construir.** El menú en sí
se arma en un día; lo que tarda es que el colegio escriba las respuestas.
Si quieres, empezamos igual con las 2-3 preguntas que ya podemos
responder hoy (ubicación, cómo contactar) y el resto se agrega después
sin tocar el resto del código — están pensadas como una lista de datos,
no como texto quemado en el HTML.

### 3.3 · Hablar con secretaría

```
┌─────────────────────────────────┐
│  ‹ Volver                        │
│                                  │
│  📞  099 824 6396                │
│  ✉️  18h00063@gmail.com          │
│                                  │
│  [ Escribir por WhatsApp ]      │
└─────────────────────────────────┘
```

Reutiliza literalmente los datos que ya están en `tokens.css`/HTML.
Cero información nueva que mantener en dos lugares.

## 4. Cómo se construye (técnico)

**Sin librerías nuevas.** Mismo patrón que todo el sitio: HTML semántico
+ CSS con transiciones + JS vanilla. El "motor" es una máquina de
estados minúscula (qué pantalla del panel está activa) — no necesita
ni GSAP ni nada de la capa de escritorio.

```html
<div class="chat" data-pantalla="menu">
  <div class="chat__pantalla" data-id="menu">…</div>
  <div class="chat__pantalla" data-id="agendar" hidden>…</div>
  <div class="chat__pantalla" data-id="faq" hidden>…</div>
  <div class="chat__pantalla" data-id="secretaria" hidden>…</div>
</div>
```

Las preguntas frecuentes viven como datos, no como HTML repetido:

```js
const FAQ = [
  { p: '¿Dónde están ubicados?', r: 'Tiwinza #95 y Etza, Ambato, Ecuador.' },
  // agregar una pregunta = agregar una línea aquí, sin tocar CSS ni HTML
];
```

Esto es importante para ti: **cuando el colegio te mande las respuestas
que faltan, no vas a tocar el diseño ni el maquetado.** Agregas líneas
a esa lista y ya.

### Accesibilidad — no negociable, mismo estándar que el resto del sitio

- El panel es un diálogo: `role="dialog"`, foco atrapado mientras está
  abierto, `Escape` cierra, cierra al tocar fuera.
- Cada pantalla anuncia su cambio a lectores de pantalla (`aria-live`).
- El acordeón de preguntas usa `<button aria-expanded>`, no un `<div>`
  con `onclick` — igual que ya se hizo en el formulario de contacto.
- Objetivos táctiles de 44px mínimo, igual que el FAB actual.

### Peso — el presupuesto real, no una promesa

```
JS propio hoy:        25.8 KB de 30 permitidos  →  quedan ~4 KB
```

**El chatbot no entra en el margen que queda.** Un menú con 3 pantallas,
acordeón de FAQ y máquina de estados pesa entre 3 y 5 KB minificado —
justo en el límite o pasándolo.

Dos caminos honestos:
1. **Subir el tope de JS propio** de 30 a 35 KB. Sigue siendo minúsculo
   (GSAP solo son 46 KB y ni siquiera llega al celular), y es la opción
   simple.
2. **Recortar algo del JS actual** para hacerle espacio. No hay nada
   obviamente recortable sin perder función — no lo recomiendo solo
   para no tocar un número arbitrario.

Recomiendo (1). Lo actualizo en `scripts/verificar.mjs` cuando
empecemos, para que la suite lo siga midiendo.

## 5. Orden de trabajo

| Fase | Qué incluye | Bloqueado por |
|---|---|---|
| 1 | Estructura del panel + menú principal + máquina de estados | Nada — se puede empezar hoy |
| 2 | Pantalla "Agendar visita" (WhatsApp con mensaje precargado) | Nada |
| 3 | Pantalla "Hablar con secretaría" | Nada |
| 4 | Pantalla "Preguntas frecuentes" con 2-3 respuestas ya conocidas | Nada |
| 5 | Resto de las FAQ (niveles, horario, matrícula, transporte, costo) | **El colegio debe escribirlas** |
| 6 | El FAB absorbe el menú (decisión de diseño A de la sección 2) | Fases 1-4 |

Las fases 1-4 y 6 se pueden hacer completas sin esperar nada del
colegio. La 5 queda con una lista corta y honesta hasta que llegue el
contenido — igual que ya hicimos con los niveles educativos en el resto
del sitio.

## 6. Lo que NO se está construyendo (y por qué)

- **Sin caja de texto libre.** El usuario no escribe preguntas; toca
  opciones. Evita el problema de "el bot no entendió" — la causa más
  común de que un chatbot mal calibrado se sienta peor que no tener nada.
- **Sin calendario de citas real.** Ya definido: WhatsApp coordina la
  fecha, no el sitio.
- **Sin IA ni servidor.** Coherente con "sin backend" que ya se decidió
  para todo el proyecto — el sitio sigue siendo 100% estático, se sigue
  desplegando igual en Vercel sin build de servidor.

---

## Siguiente paso

Con esto aprobado, empiezo por la Fase 1 (estructura + menú principal) y
lo subo a local para que lo pruebes antes de tocar nada de git, como
venimos trabajando. ¿Arranco?
