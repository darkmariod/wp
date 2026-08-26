// Bot de WhatsApp — Pestalozzi Ambato
//
// Recibe cada mensaje entrante desde Evolution API (webhook), busca una
// coincidencia en menu.js y responde por el mismo camino. Sin IA, sin base
// de datos: la "memoria" de cada conversación vive en RAM y se pierde si el
// contenedor se reinicia — para un menú de preguntas frecuentes no hace
// falta más.

const express = require('express');
const menu = require('./menu');

const app = express();
app.use(express.json());

const EVOLUTION_URL = process.env.EVOLUTION_URL; // ej: http://evolution:8080
const EVOLUTION_API_KEY = process.env.EVOLUTION_API_KEY;
const INSTANCIA = process.env.EVOLUTION_INSTANCE || 'pestalozzi';

// numero -> { visto: bool, pausadoHasta: timestamp }
const estados = new Map();
const PAUSA_MS = 30 * 60 * 1000; // 30 min tras pedir hablar con una persona

function normalizar(texto) {
  return (texto || '').trim().toLowerCase();
}

function buscarOpcion(texto) {
  const t = normalizar(texto);
  return menu.opciones.find((o) => o.disparadores.includes(t));
}

async function evolution(ruta, body) {
  const resp = await fetch(`${EVOLUTION_URL}${ruta}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', apikey: EVOLUTION_API_KEY },
    body: JSON.stringify(body),
  });
  if (!resp.ok) {
    console.error(`Evolution respondió ${resp.status} en ${ruta}:`, await resp.text());
  }
}

function enviarTexto(numero, texto) {
  return evolution(`/message/sendText/${INSTANCIA}`, { number: numero, text: texto });
}

function enviarMedia(numero, opcion) {
  return evolution(`/message/sendMedia/${INSTANCIA}`, {
    number: numero,
    mediatype: opcion.tipo, // 'imagen' -> Evolution espera 'image'/'video'
    media: opcion.contenido,
    caption: opcion.caption || '',
  });
}

async function responder(numero, opcion) {
  if (opcion.tipo === 'texto') return enviarTexto(numero, opcion.contenido);
  return enviarMedia(numero, { ...opcion, tipo: opcion.tipo === 'imagen' ? 'image' : 'video' });
}

app.get('/', (_req, res) => res.json({ estado: 'ok', conversacionesActivas: estados.size }));

app.post('/webhook/whatsapp', async (req, res) => {
  // Se responde rápido y se procesa después: Evolution no debe esperar.
  res.sendStatus(200);

  try {
    const data = req.body?.data;
    if (!data || data.key?.fromMe) return; // ignora los mensajes que manda el propio número

    const numero = data.key?.remoteJid;
    if (!numero) return;

    const texto =
      data.message?.conversation || data.message?.extendedTextMessage?.text || '';

    const estado = estados.get(numero) || { visto: false, pausadoHasta: 0 };

    // Alguien de secretaría ya está atendiendo esta conversación.
    if (estado.pausadoHasta > Date.now()) return;

    const opcion = buscarOpcion(texto);

    if (opcion) {
      await responder(numero, opcion);
      if (opcion.pausaBot) estado.pausadoHasta = Date.now() + PAUSA_MS;
      estado.visto = true;
    } else if (!estado.visto || ['menu', 'menú'].includes(normalizar(texto))) {
      await enviarTexto(numero, menu.bienvenida);
      estado.visto = true;
    } else {
      await enviarTexto(numero, menu.noEntendido);
    }

    estados.set(numero, estado);
  } catch (err) {
    console.error('Error procesando mensaje:', err);
  }
});

const PUERTO = process.env.PORT || 3000;
app.listen(PUERTO, () => console.log(`Bot escuchando en el puerto ${PUERTO}`));
