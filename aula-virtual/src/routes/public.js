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

    const items = filtroSubject
        ? db
              .prepare('SELECT * FROM content_items WHERE published = 1 AND subject_id = ? ORDER BY created_at DESC')
              .all(filtroSubject)
        : db.prepare('SELECT * FROM content_items WHERE published = 1 ORDER BY created_at DESC').all();

    res.render('public/biblioteca', {
        subjects,
        items,
        filtroSubject,
        childName: req.session.childName,
    });
});

module.exports = router;
