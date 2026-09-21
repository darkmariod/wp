# Cargar familias en bloque

Para no dar de alta a cada familia una por una desde el panel cuando
el colegio manda la lista completa de un curso (o de todo el año).

---

## 1. Escribir el archivo

Creá un archivo de texto (por ejemplo `familias.txt`) con un bloque
por familia, separados por una línea en blanco:

```
FAMILIA: Pérez García
TELEFONO: 0991234567
CORREO: juan.perez@gmail.com
NINO: Juan Pérez | 2020-05-10 | Inicial 1
NINO: Ana Pérez | 2022-03-15 | Inicial 1

FAMILIA: Torres López
TELEFONO: 0987654321
CORREO: maria.torres@gmail.com
NINO: Pedro Torres | 2019-08-20 | Primero de Básica
```

- `CORREO` es con lo que esa familia va a entrar al aula virtual.
- `NINO` va uno por línea si hay hermanos: `Nombre | Fecha de nacimiento | Ambiente`.
- El `Ambiente` (Inicial 1, Primero de Básica, etc.) tiene que estar
  escrito **igual** a como está creado en el panel — si no existe, ese
  niño se saltea con un aviso, no rompe el resto del archivo.
- `TELEFONO` es opcional.

---

## 2. Cargar

Copiá el archivo al VPS y corré el comando dentro del contenedor:

```bash
docker cp familias.txt aula-pestalozzi:/var/www/html/familias.txt
docker exec aula-pestalozzi php artisan familias:importar familias.txt
```

La salida es una tabla con la familia, el correo y la **contraseña
generada** de cada una — es la que le pasás a cada familia (mismo
formato de tabla que ya venís usando por WhatsApp).

Se puede correr las veces que haga falta: una familia cuyo correo ya
existe se saltea sola, no duplica ni pisa nada.

---

## Si preferís cargarlas a mano

Panel → **Gestión de Familias**: primero *Padres de Familia* → *Nueva
familia*, después *Niños* → *Nuevo niño*, y por último *Usuarios* →
*Nuevo usuario* (rol Familia, vinculado a esa familia). Es el mismo
resultado, uno por uno.
