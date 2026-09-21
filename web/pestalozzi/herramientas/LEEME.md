<img src="monkey-computer-logo.png" alt="Monkey Computer" width="420">

# Cargar el contenido del colegio

Dos pasos. Cinco minutos.

---

## 1. Escribir lo que te dé el colegio

Abrí **`contenido.txt`** y completá. El formato es con barras `|`:

```
NIVEL: Inicial 1 | 3 a 4 años | Primeros años de exploración | Grupos de hasta 12; Informe diario
DATO: Horario de clases | 07:30 a 13:00 | Lunes a viernes
```

- Lo que dejes **vacío no se publica** — no rompe nada, simplemente no aparece.
- Las líneas con `#` son notas, se ignoran.

---

## 2. Cargar

```bash
cd ~/Desktop/pestalozzi-carga
./cargar.sh
```

El sitio se reconstruye solo. **En 1 o 2 minutos está en vivo.**

Se puede correr las veces que haga falta: lo que dejaste vacío no se
toca, así que no pisa lo ya cargado.

---

## Si preferís cargarlo a mano

Entrá a **pestalozzi-opal.vercel.app/admin** → *Página de Inicio* →
pestañas **"Qué ofrece cada nivel"** y **"Datos prácticos"**.

Es el mismo resultado, solo que llenando formularios.

---

## Fotos de la galería, en bloque

Cuando el colegio te mande fotos para la galería, no hace falta
subirlas una por una desde el Studio. Metelas todas en una carpeta —
el **nombre de la carpeta** es la categoría en la que van a aparecer
(si esa categoría no existe todavía, se crea sola):

```bash
./cargar-fotos.sh ~/Desktop/fotos-actividades
```

Se puede correr varias veces con carpetas distintas: nunca pisa lo
que ya está cargado, solo agrega. La descripción de cada foto sale
del nombre del archivo (`patio-de-juegos.jpg` → "Patio de juegos");
si el nombre no dice nada (`IMG_0042.jpg`), queda una descripción
genérica con el nombre de la categoría — conviene repasarlas después
en el Studio si el nombre de archivo no era descriptivo.

---

> El Aula Virtual (Moodle) tiene sus propias guías, fuera de este
> repositorio: sus datos de acceso no deben quedar versionados.

---

## Qué mostrar en la reunión

| Qué | Dónde |
|---|---|
| El sitio | **pestalozzi-opal.vercel.app** |
| El aula virtual | botón **Aula virtual**, arriba a la derecha |
| El panel del colegio | **/admin** — mostrar que ellos editan sin depender de nadie |

**Un aviso:** el aula virtual entra por IP sin certificado, así que el
navegador dice *"No seguro"*. Es normal en esta etapa — se resuelve
cuando compren el dominio. Conviene decirlo antes de que lo pregunten.

---

## Soporte

| | |
|---|---|
| **Correo electrónico** | monkeycomputerec@gmail.com |
| **Número de contacto** | 0982981564 |
| **Sitio web** | monkeycomputer.com |

<sub>Monkey Computer · Ambato, Ecuador</sub>
