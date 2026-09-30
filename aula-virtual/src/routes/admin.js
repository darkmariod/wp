const path = require('path');
const crypto = require('crypto');
const express = require('express');
const multer = require('multer');
const bcrypt = require('bcrypt');
const db = require('../db');
const { requireAdmin, requireAdministrador } = require('../middleware/auth');

const router = express.Router();

const upload = multer({
    storage: multer.diskStorage({
        destination: path.join(__dirname, '..', '..', 'public', 'uploads'),
        filename: (req, file, cb) => {
            const ext = path.extname(file.originalname);
            cb(null, `${Date.now()}-${crypto.randomBytes(4).toString('hex')}${ext}`);
        },
    }),
    limits: { fileSize: 8 * 1024 * 1024 }, // 8MB, igual tope que ya usás en aula
});

router.get('/login', (req, res) => {
    if (req.session.adminId) {
        return res.redirect('/admin');
    }

    res.render('admin/login', { error: null });
});

router.post('/login', (req, res) => {
    const { email, password } = req.body;
    const admin = db.prepare('SELECT * FROM admins WHERE email = ?').get(email);

    if (!admin || !bcrypt.compareSync(password || '', admin.password_hash)) {
        return res.render('admin/login', { error: 'Correo o contraseña incorrectos.' });
    }

    req.session.adminId = admin.id;
    req.session.adminName = admin.name;
    req.session.adminRole = admin.role;
    res.redirect('/admin');
});

router.post('/logout', (req, res) => {
    req.session.destroy(() => res.redirect('/admin/login'));
});

router.use(requireAdmin);

// Disponibles en TODAS las vistas de acá para abajo sin tener que
// pasarlas a mano en cada res.render — la barra de navegación decide
// sola si muestra "Personal" según el rol de la sesión.
router.use((req, res, next) => {
    res.locals.adminName = req.session.adminName;
    res.locals.adminRole = req.session.adminRole;
    next();
});

router.get('/', (req, res) => {
    const items = db
        .prepare(
            `SELECT content_items.*, subjects.name AS subject_name
             FROM content_items
             LEFT JOIN subjects ON subjects.id = content_items.subject_id
             ORDER BY content_items.created_at DESC`
        )
        .all();

    res.render('admin/dashboard', { items });
});

router.get('/modulos', (req, res) => {
    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    const modules = db.prepare('SELECT * FROM modules ORDER BY subject_id, sort_order').all();
    res.render('admin/modulos', { subjects, modules, errores: [] });
});

router.post('/modulos/nuevo', (req, res) => {
    const { subject_id, name } = req.body;
    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    const errores = [];

    if (!subject_id || !subjects.some(s => s.id === Number(subject_id))) {
        errores.push('Elegí una materia válida.');
    }
    if (!name || !name.trim()) {
        errores.push('El nombre del módulo es obligatorio.');
    }

    if (errores.length > 0) {
        const modules = db.prepare('SELECT * FROM modules ORDER BY subject_id, sort_order').all();
        return res.status(422).render('admin/modulos', { subjects, modules, errores });
    }

    const siguienteOrden = db
        .prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 AS n FROM modules WHERE subject_id = ?')
        .get(subject_id).n;

    db.prepare('INSERT INTO modules (subject_id, name, sort_order) VALUES (?, ?, ?)')
        .run(subject_id, name.trim(), siguienteOrden);

    res.redirect('/admin/modulos');
});

router.post('/modulos/:id/eliminar', (req, res) => {
    db.prepare('DELETE FROM modules WHERE id = ?').run(req.params.id);
    res.redirect('/admin/modulos');
});

router.get('/contenido/nuevo', (req, res) => {
    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    const modules = db
        .prepare(
            `SELECT modules.*, subjects.name AS subject_name
             FROM modules JOIN subjects ON subjects.id = modules.subject_id
             ORDER BY subjects.sort_order, modules.sort_order`
        )
        .all();
    res.render('admin/contenido-form', { subjects, modules, errores: [] });
});

const uploadContenido = upload.fields([
    { name: 'cover', maxCount: 1 },
    { name: 'archivo', maxCount: 1 }, // video, type='video'
    { name: 'paginas', maxCount: 30 }, // imágenes del cuento, type='book'
]);

router.post('/contenido/nuevo', uploadContenido, (req, res) => {
    const { title, description, subject_id, module_id, type } = req.body;
    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    const modules = db
        .prepare(
            `SELECT modules.*, subjects.name AS subject_name
             FROM modules JOIN subjects ON subjects.id = modules.subject_id
             ORDER BY subjects.sort_order, modules.sort_order`
        )
        .all();
    const errores = [];

    if (!title || !title.trim()) {
        errores.push('El título es obligatorio.');
    }

    if (!['book', 'video', 'activity', 'document'].includes(type)) {
        errores.push('Elegí un tipo de contenido válido.');
    }

    const paginas = req.files?.paginas || [];
    if (type === 'book' && paginas.length === 0) {
        errores.push('Un cuento necesita al menos una página (imagen).');
    }

    if ((type === 'video' || type === 'document') && !req.files?.archivo?.[0]) {
        errores.push(type === 'video' ? 'Un video necesita el archivo de video.' : 'Un documento necesita el archivo a descargar.');
    }

    if (errores.length > 0) {
        return res.status(422).render('admin/contenido-form', { subjects, modules, errores });
    }

    const coverFile = req.files?.cover?.[0];
    const archivoFile = req.files?.archivo?.[0];

    // La portada es opcional en general, pero un cuento sin portada
    // propia usa su primera página como portada — así no queda un
    // ícono genérico en la biblioteca cuando la docente se olvida de
    // subirla aparte.
    const coverPath = coverFile
        ? `/uploads/${coverFile.filename}`
        : (type === 'book' && paginas[0] ? `/uploads/${paginas[0].filename}` : null);
    const filePath = archivoFile ? `/uploads/${archivoFile.filename}` : null;

    // Un módulo ya pertenece a una materia — si se eligió uno, esa es
    // la materia real del contenido, sin importar qué haya quedado
    // seleccionado en el combo de materia.
    const moduloElegido = module_id ? modules.find(m => m.id === Number(module_id)) : null;
    const materiaFinal = moduloElegido ? moduloElegido.subject_id : (subject_id || null);

    const { lastInsertRowid } = db.prepare(
        `INSERT INTO content_items (subject_id, module_id, type, title, description, cover_path, file_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)`
    ).run(materiaFinal, moduloElegido ? moduloElegido.id : null, type, title.trim(), description || null, coverPath, filePath);

    if (type === 'book' && paginas.length > 0) {
        const insertPagina = db.prepare(
            'INSERT INTO content_pages (content_id, image_path, sort_order) VALUES (?, ?, ?)'
        );
        paginas.forEach((pagina, indice) => {
            insertPagina.run(lastInsertRowid, `/uploads/${pagina.filename}`, indice);
        });
    }

    res.redirect('/admin');
});

router.post('/contenido/:id/eliminar', (req, res) => {
    db.prepare('DELETE FROM content_items WHERE id = ?').run(req.params.id);
    res.redirect('/admin');
});

router.get('/ninos', (req, res) => {
    const children = db.prepare('SELECT * FROM children ORDER BY name').all();
    res.render('admin/ninos', { children, errores: [] });
});

router.post('/ninos/nuevo', upload.single('avatar'), (req, res) => {
    const { name } = req.body;

    if (!name || !name.trim()) {
        const children = db.prepare('SELECT * FROM children ORDER BY name').all();
        return res.status(422).render('admin/ninos', { children, errores: ['El nombre es obligatorio.'] });
    }

    const avatarPath = req.file ? `/uploads/${req.file.filename}` : null;
    db.prepare('INSERT INTO children (name, avatar_path) VALUES (?, ?)').run(name.trim(), avatarPath);

    res.redirect('/admin/ninos');
});

router.post('/ninos/:id/eliminar', (req, res) => {
    db.prepare('DELETE FROM children WHERE id = ?').run(req.params.id);
    res.redirect('/admin/ninos');
});

// Personal (administradores y docentes): solo la administración
// gestiona esto, es la puerta para que entre gente nueva al panel.
router.get('/personal', requireAdministrador, (req, res) => {
    const personal = db.prepare('SELECT id, email, name, role FROM admins ORDER BY name').all();
    res.render('admin/personal', { personal, errores: [] });
});

router.post('/personal/nuevo', requireAdministrador, (req, res) => {
    const { name, email, password, role } = req.body;
    const errores = [];

    if (!name || !name.trim()) errores.push('El nombre es obligatorio.');
    if (!email || !email.trim()) errores.push('El correo es obligatorio.');
    if (!password || password.length < 8) errores.push('La contraseña debe tener al menos 8 caracteres.');
    if (!['administrador', 'docente'].includes(role)) errores.push('Elegí un rol válido.');

    if (errores.length === 0 && db.prepare('SELECT id FROM admins WHERE email = ?').get(email)) {
        errores.push('Ya existe una cuenta con ese correo.');
    }

    if (errores.length > 0) {
        const personal = db.prepare('SELECT id, email, name, role FROM admins ORDER BY name').all();
        return res.status(422).render('admin/personal', { personal, errores });
    }

    const hash = bcrypt.hashSync(password, 10);
    db.prepare('INSERT INTO admins (email, password_hash, name, role) VALUES (?, ?, ?, ?)')
        .run(email.trim(), hash, name.trim(), role);

    res.redirect('/admin/personal');
});

router.post('/personal/:id/eliminar', requireAdministrador, (req, res) => {
    // No te podés borrar a vos mismo y quedarte afuera del panel sin
    // querer — el resto sí puede eliminarse.
    if (Number(req.params.id) === req.session.adminId) {
        const personal = db.prepare('SELECT id, email, name, role FROM admins ORDER BY name').all();
        return res.status(422).render('admin/personal', { personal, errores: ['No podés eliminar tu propia cuenta.'] });
    }

    db.prepare('DELETE FROM admins WHERE id = ?').run(req.params.id);
    res.redirect('/admin/personal');
});

module.exports = router;
