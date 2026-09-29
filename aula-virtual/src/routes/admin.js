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

router.get('/contenido/nuevo', (req, res) => {
    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    res.render('admin/contenido-form', { subjects, errores: [] });
});

router.post('/contenido/nuevo', upload.single('cover'), (req, res) => {
    const { title, description, subject_id, type } = req.body;
    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    const errores = [];

    if (!title || !title.trim()) {
        errores.push('El título es obligatorio.');
    }

    if (!['book', 'video', 'activity'].includes(type)) {
        errores.push('Elegí un tipo de contenido válido.');
    }

    if (errores.length > 0) {
        return res.status(422).render('admin/contenido-form', { subjects, errores });
    }

    const coverPath = req.file ? `/uploads/${req.file.filename}` : null;

    db.prepare(
        `INSERT INTO content_items (subject_id, type, title, description, cover_path)
         VALUES (?, ?, ?, ?, ?)`
    ).run(subject_id || null, type, title.trim(), description || null, coverPath);

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
