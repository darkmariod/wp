# Aula Laravel vs. Moodle — qué falta para poder entregar

## Contexto

Desde el principio de este proyecto quedó una decisión explícita: **el aula nueva
convive con el Moodle del VPS, no lo reemplaza hasta que esté lista** (verificado
en memoria, no es un supuesto mío). Este documento compara ambos sistemas en lo
que sí se verificó de cada uno, para decidir qué falta antes de poder apagar
Moodle de verdad.

**No entré al Moodle real para escribir esto.** Es un servidor de producción con
datos reales de alumnos menores de edad, y no tengo tu autorización explícita
para meterme ahí a curiosear. Todo lo que digo de Moodle sale de lo que ya se
verificó en sesiones anteriores (marcado como tal) o es conocimiento general de
cómo funciona Moodle — lo marco por separado para que no se confunda una cosa
con la otra.

---

## 🔴 Bloqueantes — sin esto, no hay entrega real

### 1. Falta migrar la matrícula real

Moodle **ya tiene alumnos reales inscriptos** (verificado: al menos 12-14 cursos
activos con familias reales matriculadas — se probó una entrega real de un
usuario "Familia Prueba A" en sesiones anteriores). El aula Laravel, en cambio,
**salió a propósito con cero niños, cero familias, cero contenido** — así te la
dejé ayer, lista para entregar sin datos de prueba.

Esto significa que hoy, si apagás Moodle, **nadie tiene una cuenta en el aula
nueva**. Falta:
- Sacar la lista real de los ~30 chicos (nombre, fecha de nacimiento, aula,
  familia) desde Moodle o desde donde la tenga el colegio.
- Cargarlos en el panel (Niños → Crear, o el registro de padres que armamos
  desde la ficha del niño).
- Avisarle a cada familia su nuevo acceso — no hay una migración automática de
  contraseña: son cuentas nuevas.

Esto es trabajo del colegio tanto como técnico — nadie más tiene esa lista.

### 2. No hay servidor real, todavía corre en tu máquina

Confirmado ahora mismo: `APP_URL=http://localhost:8000`. El aula solo existe en
tu computadora. Moodle, en cambio, ya está en un VPS con una IP pública
(`108.174.152.179:8082`) — es lo que hoy enlaza "Aula virtual" desde el sitio
público del colegio.

Antes de entregar hace falta: un servidor (puede ser el mismo VPS u otro),
un dominio o subdominio, HTTPS, y la base de datos MySQL en ese servidor (hoy
es local). Sin esto no hay nada que compartirle al colegio.

### 3. No hay correo real configurado

Confirmado: `MAIL_MAILER=log` — los correos no se envían, se escriben en un
archivo de log. Esto rompe en silencio:
- El botón "¿Olvidaste tu contraseña?" (no llega ningún correo)
- Cualquier notificación por email a futuro

Moodle, asumo, tiene SMTP configurado y funcionando (no lo verifiqué en esta
sesión, pero es lo esperable en un sistema en uso real). Antes de entregar hace
falta un proveedor de correo real (Gmail con contraseña de aplicación, SendGrid,
Mailgun, lo que ya use el colegio) y cargarlo en el `.env` del servidor.

---

## 🟡 Importante, pero no bloqueante — se puede resolver después de lanzar

### 4. Recordatorios automáticos de vencimiento

Ayer agregamos la fecha límite de las tareas (`due_date`) y el aviso visual de
"vencida". Lo que **no** armamos es un aviso automático — un correo o
notificación que le llegue a la familia unos días antes de que venza. Moodle
probablemente lo tiene de fábrica en sus tareas (`mod_assign`). Es una pieza
aparte: necesita un job programado (`php artisan schedule`) corriendo en el
servidor, algo que hoy no existe.

### 5. Sin fotografía real

Ya lo hablamos: cero fotos reales de los ambientes o los chicos en el aula.
Los campos ya existen y están conectados (`cover_image`, `image` de
ambientes/áreas) — solo falta que el colegio las provea.

### 6. Cola de notificaciones sin worker corriendo

El sistema de notificaciones de la aula (`ShouldQueue`, canal `database`)
necesita un proceso (`php artisan queue:work`) corriendo todo el tiempo en el
servidor — normalmente vía un supervisor de procesos. Es configuración de
despliegue, no código: hay que dejarlo armado en el servidor final.

### 7. Sin auditoría/logs de acciones

Moodle trae un log de actividad extenso de fábrica (quién entró, qué miró,
qué entregó). El aula tiene tests, pero no un registro de auditoría propio.
Para un sistema chico como este probablemente no haga falta todavía, pero es
una diferencia real si el colegio lo pedía.

---

## 🟢 Ya resuelto, o donde el aula nueva ya está mejor

- **Idioma correcto desde el diseño.** En Moodle quedó pendiente traducir
  "Curso"→"Ambiente", "Tarea"→"Evidencia", y la palabra "Calificar" sigue
  apareciendo en la interfaz aunque no haya notas numéricas (verificado en
  sesión anterior). El aula nació ya en español correcto: Niños, Ambientes,
  Contenidos — sin rastro de "calificar" en ningún lado.
- **Sin calificaciones, en los dos sistemas.** Confirmado en ambos: Moodle
  tiene el assignment configurado sin nota numérica (`gradeType=none`), y el
  aula nunca modeló notas. Coinciden — no hay que resolver nada acá.
- **Múltiples hijos por familia, ya soportado.** Una familia puede tener más
  de un adulto con su propio login (mamá y papá), y ver a todos sus hijos
  desde una sola cuenta.
- **Identidad visual propia**, alineada al sitio real del colegio (mismo logo,
  mismos colores, mismo tono) — no un tema genérico parchado.
- **CRUD completo y auditado.** Los 6 recursos del panel (Niños, Familias,
  Usuarios, Ambientes, Áreas, Contenidos) tienen alta/baja/edición completas,
  con roles y permisos ya probados contra intentos reales de escalamiento
  (`CyberSecurityTest.php`, `SecurityTest.php`).
- **Fecha límite de tareas**, recién agregada — antes ni eso existía.

---

## Lo que sinceramente no puedo responder sin ver el Moodle real

- Si Moodle tiene otras funciones en uso que el colegio valora y que yo no
  tengo documentadas (foros, mensajería interna, calendario compartido, etc.).
- El volumen real de contenido histórico que se perdería si se apaga sin migrar.
- Si hay integraciones externas (constancias, facturación, algún otro sistema)
  que dependan de Moodle.

Si querés que arme un inventario más preciso, la vía correcta es que vos (o
alguien con acceso de administrador) entre al Moodle y me pase capturas de lo
que se usa hoy — no voy a entrar por mi cuenta a un sistema con datos de
menores sin que me lo pidas de forma explícita y puntual.
