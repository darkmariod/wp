const express = require('express');
const db = require('../db');

const router = express.Router();

router.get('/', (req, res) => {
    const children = db.prepare('SELECT * FROM children ORDER BY name').all();
    res.render('public/perfiles', { children });
});

router.post('/elegir/:id', (req, res) => {
    const child = db.prepare('SELECT * FROM children WHERE id = ?').get(req.params.id);

    if (!child) {
        return res.redirect('/');
    }

    req.session.childId = child.id;
    req.session.childName = child.name;
    res.redirect('/biblioteca');
});

router.get('/biblioteca', (req, res) => {
    if (!req.session.childId) {
        return res.redirect('/');
    }

    const subjects = db.prepare('SELECT * FROM subjects ORDER BY sort_order').all();
    const filtroSubject = req.query.materia ? Number(req.query.materia) : null;
    const busqueda = (req.query.q || '').trim();

    let sql = 'SELECT * FROM content_items WHERE published = 1';
    const params = [];

    if (filtroSubject) {
        sql += ' AND subject_id = ?';
        params.push(filtroSubject);
    }
    if (busqueda) {
        sql += ' AND (title LIKE ? OR description LIKE ?)';
        params.push(`%${busqueda}%`, `%${busqueda}%`);
    }
    sql += ' ORDER BY created_at DESC';

    const items = db.prepare(sql).all(...params);

    // Progreso del chico actual, para la marca de "visto"/"completado"
    // en cada tarjeta — no es un dato del contenido en sí, depende de
    // quién está mirando la biblioteca en este momento.
    const vistos = db
        .prepare('SELECT content_id, completed_at FROM content_views WHERE child_id = ?')
        .all(req.session.childId);
    const completadoPorId = new Map(vistos.map(v => [v.content_id, !!v.completed_at]));
    const vistoPorId = new Set(vistos.map(v => v.content_id));
    items.forEach(item => {
        item.visto = vistoPorId.has(item.id);
        item.completado = completadoPorId.get(item.id) || false;
    });

    let modulosConItems = [];
    let itemsSinModulo = [];

    if (filtroSubject) {
        const modulos = db.prepare('SELECT * FROM modules WHERE subject_id = ? ORDER BY sort_order').all(filtroSubject);
        modulosConItems = modulos
            .map(modulo => ({ ...modulo, items: items.filter(item => item.module_id === modulo.id) }))
            .filter(modulo => modulo.items.length > 0);
        itemsSinModulo = items.filter(item => !item.module_id);
    }

    res.render('public/biblioteca', {
        subjects,
        items,
        filtroSubject,
        busqueda,
        modulosConItems,
        itemsSinModulo,
        childName: req.session.childName,
    });
});

router.get('/biblioteca/:id', (req, res) => {
    if (!req.session.childId) {
        return res.redirect('/');
    }

    const item = db
        .prepare(
            `SELECT content_items.*, subjects.name AS subject_name
             FROM content_items
             LEFT JOIN subjects ON subjects.id = content_items.subject_id
             WHERE content_items.id = ? AND content_items.published = 1`
        )
        .get(req.params.id);

    if (!item) {
        return res.redirect('/biblioteca');
    }

    const paginas = item.type === 'book'
        ? db.prepare('SELECT * FROM content_pages WHERE content_id = ? ORDER BY sort_order').all(item.id)
        : [];

    // Una actividad o documento se marca completo apenas se abre — no
    // hay nada más que "terminar" ahí adentro. Un cuento o un video se
    // completan desde el reproductor (POST /completado), al llegar a
    // la última página o al terminar de reproducirse.
    const completarInmediato = ['activity', 'document'].includes(item.type);
    db.prepare(
        `INSERT INTO content_views (content_id, child_id, completed_at)
         VALUES (?, ?, ${completarInmediato ? "datetime('now')" : 'NULL'})
         ON CONFLICT(content_id, child_id) DO UPDATE SET
            completed_at = COALESCE(content_views.completed_at, excluded.completed_at)`
    ).run(item.id, req.session.childId);

    res.render('public/contenido', { item, paginas, childName: req.session.childName });
});

router.post('/biblioteca/:id/completado', (req, res) => {
    if (!req.session.childId) {
        return res.sendStatus(401);
    }

    db.prepare(
        `INSERT INTO content_views (content_id, child_id, completed_at)
         VALUES (?, ?, datetime('now'))
         ON CONFLICT(content_id, child_id) DO UPDATE SET
            completed_at = COALESCE(content_views.completed_at, excluded.completed_at)`
    ).run(req.params.id, req.session.childId);

    res.sendStatus(204);
});

module.exports = router;
