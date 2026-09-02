# Resumen técnico — Proyecto Pestalozzi Ambato


---

## 1. Arquitectura general

```
Sitio web (Astro, estático) ──► Vercel (hosting, gratis)
        ▲
        │ lee contenido en cada build
        │
   Sanity CMS ──► Sanity Cloud (gratis, plan actual)
        ▲
        │ el colegio edita desde acá
        │
  pestalozzi.sanity.studio  /  pestalozzi-opal.vercel.app/admin

Bot de WhatsApp (Node.js + Baileys) ──► pendiente de desplegar en VPS
        │
        └─ responde con datos del propio sitio (fotos, video, textos)

Moodle ──► NO alojado por este proyecto, solo un campo de enlace en el CMS
```

---

## 2. Sitio web

- **Framework**: Astro (sitio estático, se reconstruye en cada cambio del CMS)
- **Hosting**: Vercel — `https://pestalozzi-opal.vercel.app`
- **Repo**: `github.com/darkmariod/wp`, carpeta `web/pestalozzi/`
- **Dominio propio**: todavía no comprado por el colegio (sitio corre en el subdominio de Vercel)
- **Pruebas automatizadas**: `scripts/verificar.mjs`, 94 pruebas, se corren con `npm run build && node scripts/verificar.mjs`

### Páginas
Inicio, Nosotros, Galería, Contacto — las 4 leen su contenido desde Sanity en cada build.

---

## 3. CMS (Sanity)

- **Project ID**: `513m7736`
- **Dataset**: `production`
- **Panel embebido**: `pestalozzi-opal.vercel.app/admin`
- **Panel independiente**: `pestalozzi.sanity.studio`
- **Documentos editables**: Datos del colegio, Página Inicio, Página Nosotros, Página Galería, Página Contacto, Fotos de la galería (6)
- **Webhook → Vercel**: cada `Publish` dispara un rebuild automático (Deploy Hook configurado en Vercel + webhook en Sanity)

### Campo de Moodle
`configuracion.moodleUrl` — si está vacío, el enlace "Aula virtual" no aparece en el sitio. Hoy tiene puesto el demo público de Moodle (`school.moodledemo.net`) como placeholder — **reemplazar por la URL real antes de entregar**.

### Pendiente de higiene
- [ ] Borrar el token de API `migracion` (quedó expuesto durante la migración de contenido)
- [ ] Invitar al colegio como **Editor** (no Administrator) en Sanity
- [ ] Confirmar que el plan Growth Trial de Sanity no cobre automáticamente al vencer

---

## 4. Bot de WhatsApp

- **Stack**: Node.js + `@whiskeysockets/baileys` (conexión directa, sin Evolution API, sin Docker, sin n8n)
- **Estado actual**: código probado y funcional, corriendo localmente para pruebas — **no desplegado en servidor todavía**
- **Archivo de contenido**: `menu.js` — un único archivo con las opciones del menú, textos y URLs de fotos/video. Es lo único que se edita para cambiar una respuesta.
- **Menú actual** (con datos de ejemplo, pendiente de confirmar con el colegio):
  1. Ubicación
  2. Horarios de atención
  3. Niveles educativos
  4. Requisitos de matrícula
  5. Fotos del plantel
  6. Video del colegio
  7. Hablar con una persona (pausa el bot 30 min en esa conversación)

### Pendiente antes de entregar
- [ ] Confirmar contenido real de cada opción con el colegio
- [ ] Confirmar el número de WhatsApp a vincular — **recomendado: uno secundario**, no el principal (riesgo de bloqueo, cláusula 14 del contrato)
- [ ] Desplegar en el VPS propio  como proceso simple con `pm2`, sin Docker
- [ ] Vincular el número real (escaneo de QR único, ya con todo probado de antemano)

### Riesgo conocido
No usa la API oficial de Meta — es una conexión tipo "dispositivo vinculado", igual que WhatsApp Web. Meta puede bloquear el número sin aviso. Está declarado en la cláusula 14 del contrato.

---

## 5. Moodle

**Fuera de alcance de este contrato.** El colegio no tiene Moodle propio hoy. Lo único construido es un campo en el CMS para pegar la URL de un Moodle que el colegio contrate por su cuenta.

Si en el futuro se decide alojar Moodle:
- Es un **servicio nuevo**, con contrato/anexo y precio aparte
- Necesita su **propio VPS**, separado del que usa el bot (Moodle es pesado: PHP + base de datos + cron + disco creciente)
- No hay nada que migrar (confirmado con el colegio: no tienen Moodle en ningún lado todavía) — sería instalación desde cero
- Requiere que el colegio defina antes cómo organiza los cursos (por grado, por materia, quién administra)

---

## 6. Contrato — resumen de lo relevante a lo técnico

| Concepto | Valor |
|---|---|
| Setup (pago único) | USD 280 — incluye sitio, CMS, bot de WhatsApp |
| Anual (recurrente) | USD 120/año — dominio, hosting, SSL, servidor del bot, mantenimiento |
| Moodle | Fuera de este contrato, se cotiza aparte si se pide |

Documento completo: `Pestalozzi-Clausulas-Corregidas.md`.

---

## 7. Accesos y credenciales (dónde están, no los valores)

| Servicio | Dónde vive el acceso |
|---|---|
| GitHub | `darkmariod/wp` |
| Vercel | cuenta `darkmariod` |
| Sanity | proyecto `513m7736`, org `otzs4sr9z` |
| VPS (bot) | pendiente de definir — hoy solo probado en local |

---

## 8. Lo que falta para entregar del todo

1. Contenido real del bot (horarios, requisitos, número de WhatsApp)
2. Desplegar el bot en un VPS propio
3. URL real de Moodle (si el colegio ya tiene uno) o dejar el campo vacío
4. Dominio propio del colegio (hoy en subdominio de Vercel)
5. Limpieza de accesos en Sanity (token, rol del colegio)
