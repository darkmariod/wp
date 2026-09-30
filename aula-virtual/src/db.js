const path = require('path');
const { DatabaseSync } = require('node:sqlite');
const bcrypt = require('bcrypt');

const db = new DatabaseSync(path.join(__dirname, '..', 'data', 'aula-virtual.sqlite'));

db.exec('PRAGMA foreign_keys = ON');

// Migración liviana: content_items venía sin module_id y con un CHECK
// de tipo más chico. SQLite no permite alterar un CHECK existente, así
// que si la tabla es de una versión vieja Y está vacía, se recrea con
// el esquema nuevo de una — no hay nada que perder todavía. Si ya
// tiene filas, no se toca (evitar borrar datos reales sin querer).
const contentItemsPrevias = db
    .prepare("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'content_items'")
    .get();

if (contentItemsPrevias && !contentItemsPrevias.sql.includes('module_id')) {
    const filas = db.prepare('SELECT COUNT(*) AS n FROM content_items').get().n;

    if (filas === 0) {
        db.exec('DROP TABLE IF EXISTS content_pages');
        db.exec('DROP TABLE IF EXISTS content_items');
    } else {
        console.warn('content_items tiene un esquema viejo y filas existentes — migración omitida, revisar a mano.');
    }
}

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

    CREATE TABLE IF NOT EXISTS modules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
        name TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS content_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id INTEGER REFERENCES subjects(id),
        module_id INTEGER REFERENCES modules(id) ON DELETE SET NULL,
        type TEXT NOT NULL CHECK (type IN ('book','video','activity','document')),
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

    -- Páginas de un cuento (type='book'): una imagen por página, en
    -- orden. Videos y actividades no la usan — un video es un solo
    -- archivo (content_items.file_path) y una actividad es solo texto.
    CREATE TABLE IF NOT EXISTS content_pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        content_id INTEGER NOT NULL REFERENCES content_items(id) ON DELETE CASCADE,
        image_path TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0
    );

    -- Seguimiento por chico: cuándo vio un contenido por primera vez y,
    -- si aplica, cuándo lo terminó (última página de un cuento, fin de
    -- un video). Una actividad o documento se marca completo apenas se
    -- abre, porque no hay una forma de "terminarlo" dentro de la app.
    CREATE TABLE IF NOT EXISTS content_views (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        content_id INTEGER NOT NULL REFERENCES content_items(id) ON DELETE CASCADE,
        child_id INTEGER NOT NULL REFERENCES children(id) ON DELETE CASCADE,
        viewed_at TEXT NOT NULL DEFAULT (datetime('now')),
        completed_at TEXT,
        UNIQUE(content_id, child_id)
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
