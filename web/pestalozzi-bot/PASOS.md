<img src="monkey-computer-logo.png" alt="Monkey Computer" width="420">

# Bot de WhatsApp — Pestalozzi Ambato

Responde consultas por WhatsApp con textos, imágenes y videos, sin la API
de Meta y sin n8n. El menú es un archivo de código (`bot/menu.js`) — para
cambiar una respuesta se edita ese archivo y se redespliega.

**Ya probado en local:** conversación completa simulada (bienvenida,
matrícula, foto, "hablar con persona" y su pausa, mensaje sin entender,
mensajes propios ignorados). Los 8 casos dieron el resultado esperado.

**No hace falta VPS nuevo:** se despliega en el servidor que ya tenés
(`108.174.152.179`), desde el panel de Dokploy. Todo por navegador.

---

## Lo que ya quedó hecho

- **Swap de 4 GB activo y persistente** en el servidor, para que un pico del
  bot no le quite memoria a contable ni a garantías.
- **`docker-compose.yml`** con dos servicios (`postgres`, `evolution`) más
  el bot propio (`bot/`), sin publicar puertos — el 8080 ya lo usa
  contable-frontend, y el 80/443 son de Traefik.
- **El bot en sí** (`bot/index.js` y `bot/menu.js`), probado localmente.

---

## 1 · Cambiar la contraseña de root

Si todavía no lo hiciste — quedó expuesta en una conversación:

```bash
ssh -p 22022 root@108.174.152.179
passwd
```

---

## 2 · Subdominios

Dos registros **A** en el DNS de `monkeycomputer.com`, apuntando a
`108.174.152.179`:

| Nombre | Valor |
|---|---|
| `bot` | `108.174.152.179` |

Solo hace falta uno: `bot.monkeycomputer.com`. El bot propio no necesita
subdominio — vive dentro de la red interna, nadie entra a él desde afuera.

---

## 3 · Desplegar en Dokploy

En el panel de Dokploy (`http://108.174.152.179:3000`):

1. **Create Project** → `pestalozzi-bot`
2. **Create Service → Compose** → pegar `docker-compose.yml`
3. Subir también la carpeta `bot/` (Dockerfile, index.js, menu.js,
   package.json) — Dokploy la necesita para construir la imagen del bot
4. Pestaña **Environment**, completar con `openssl rand -hex 24` para cada
   clave vacía:

```
POSTGRES_USER=pestalozzi
POSTGRES_PASSWORD=
EVOLUTION_API_KEY=
DOMINIO_BOT=bot.monkeycomputer.com
```

5. Pestaña **Domains** → agregar `bot.monkeycomputer.com`, servicio
   `evolution`, puerto `8080`
6. **Deploy**

---

## 4 · Vincular el WhatsApp

> **El número secundario del colegio, no el principal.** Cláusula 14 del
> contrato: si Meta bloquea el número, no se corta la comunicación con las
> familias.

Desde tu Mac:

```bash
curl -X POST https://bot.monkeycomputer.com/instance/create \
  -H "apikey: TU_EVOLUTION_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"instanceName":"pestalozzi","qrcode":true,"integration":"WHATSAPP-BAILEYS"}'
```

Devuelve un QR. Escanearlo desde el WhatsApp del colegio:
**Ajustes → Dispositivos vinculados → Vincular dispositivo**.

Comprobar que quedó conectado:

```bash
curl https://bot.monkeycomputer.com/instance/connectionState/pestalozzi \
  -H "apikey: TU_EVOLUTION_API_KEY"
```

Debe decir `"state":"open"`.

---

## 5 · Cargar las respuestas reales

Editar `bot/menu.js` con lo que te pase el colegio: horarios, requisitos de
matrícula, ubicación, fotos reales (las URLs pueden ser del sitio o de
Sanity). Cada opción del menú es un bloque con `disparadores` (qué palabras
la activan) y `contenido` (qué responde). Después de editar, redesplegar
desde Dokploy.

---

## 6 · Antes de entregar

- [ ] Probar las 6 opciones desde otro teléfono real
- [ ] Probar un mensaje que no calce con nada: debe repetir el menú, no quedarse mudo
- [ ] Redesplegar desde Dokploy y confirmar que **no** vuelve a pedir el QR
- [ ] `free -h` en el servidor: confirmar que queda memoria libre
- [ ] Tener por escrito los textos que dio el colegio
- [ ] Confirmar autorización de uso de imagen de menores (cláusula 14.6)

---

## Lo que hay que vigilar

**La sesión se puede caer.** Si alguien cierra los dispositivos vinculados
desde el celular, el bot deja de responder. Revisá `connectionState` cada
tanto.

**Meta puede bloquear el número.** Conexión no oficial, cláusula 14. Por eso
el número secundario.

**El estado de las conversaciones vive en memoria.** Si el contenedor del
bot se reinicia, se pierde quién ya vio el menú y quién estaba pausado
(esperando a una persona) — vuelven a recibir la bienvenida en su próximo
mensaje. No es un problema real para un bot de preguntas frecuentes.

---

## Soporte

| | |
|---|---|
| **Correo electrónico** | monkeycomputerec@gmail.com |
| **Número de contacto** | 0982981564 |
| **Sitio web** | monkeycomputer.com |

<sub>Monkey Computer · Ambato, Ecuador</sub>
