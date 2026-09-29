const path = require('path');
const { DatabaseSync } = require('node:sqlite');
const bcrypt = require('bcrypt');

const db = new DatabaseSync(path.join(__dirname, '..', 'data', 'aula-virtual.sqlite'));

db.exec(`
    CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        name TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'docente' CHECK (role IN ('administrador','docente')),
        created_at TEXT DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS subjects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        color TEXT NOT NULL,
        icon TEXT NOT NULL,
        sort_order INTEGER DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS content_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER REFERENCES subjects(id),
        type TEXT NOT NULL CHECK (type IN ('book','video','activity')),
        title TEXT NOT NULL,
        description TEXT,
        cover_path TEXT,
        file_path TEXT,
        published INTEGER NOT NULL DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS children (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        avatar_path TEXT,
        created_at TEXT DEFAULT (datetime('now'))
    );
`);

// Semilla inicial: las 4 materias de las capturas de referencia. Se
// puede ampliar desde el panel más adelante, esto solo evita arrancar
// con la biblioteca completamente vacía.
const subjectCount = db.prepare('SELECT COUNT(*) AS n FROM subjects').get().n;

if (subjectCount === 0) {
    const insertSubject = db.prepare(
        'INSERT INTO subjects (name, slug, color, icon, sort_order) VALUES (?, ?, ?, ?, ?)'
    );

    insertSubject.run('Letras', 'letras', 'violet', '🐘', 1);
    insertSubject.run('Lectura', 'lectura', 'pink', '🦊', 2);
    insertSubject.run('Matemática', 'matematica', 'amber', '🐦', 3);
    insertSubject.run('Lógica+', 'logica', 'orange', '🦊', 4);
}

// Cuenta de admin por defecto, solo si todavía no hay ninguna: mismo
// patrón que el seeder de aula, la contraseña sale por consola una
// única vez y nunca queda en texto plano en el código.
const adminCount = db.prepare('SELECT COUNT(*) AS n FROM admins').get().n;

if (adminCount === 0) {
    const email = process.env.ADMIN_EMAIL || 'admin@aula-virtual.test';
    const password = process.env.ADMIN_PASSWORD || Math.random().toString(36).slice(2, 10);
    const hash = bcrypt.hashSync(password, 10);

    db.prepare('INSERT INTO admins (email, password_hash, name, role) VALUES (?, ?, ?, ?)')
        .run(email, hash, 'Administrador', 'administrador');

    console.log('');
    console.log('== Cuenta de administrador creada ==');
    console.log(`Correo:      ${email}`);
    console.log(`Contraseña:  ${password}`);
    console.log('Guardala ahora, no se vuelve a mostrar.');
    console.log('');
}

module.exports = db;
