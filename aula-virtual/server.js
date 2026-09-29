require('dotenv').config();

const path = require('path');
const express = require('express');
const session = require('express-session');

require('./src/db'); // corre las migraciones/seed antes de levantar el server

const app = express();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

app.use(
    session({
        // MemoryStore por ahora: alcanza para un solo proceso chico como
        // este. Antes de un uso real con más de un puñado de sesiones a
        // la vez, cambiar a un store persistente (archivo o Redis).
        secret: process.env.SESSION_SECRET || 'cambiar-en-produccion',
        resave: false,
        saveUninitialized: false,
        cookie: { maxAge: 1000 * 60 * 60 * 8 }, // 8 horas, una jornada escolar
    })
);

app.use('/admin', require('./src/routes/admin'));
app.use('/', require('./src/routes/public'));

const PORT = process.env.PORT || 3300;
app.listen(PORT, () => console.log(`Aula virtual arriba en http://localhost:${PORT}`));
