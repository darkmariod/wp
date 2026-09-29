function requireAdmin(req, res, next) {
    if (!req.session.adminId) {
        return res.redirect('/admin/login');
    }

    next();
}

// Solo la administración crea o borra cuentas de personal — una
// docente que entra a esa pantalla no debería ver ni tocar eso.
function requireAdministrador(req, res, next) {
    if (req.session.adminRole !== 'administrador') {
        return res.status(403).send('No tenés permiso para ver esta página.');
    }

    next();
}

module.exports = { requireAdmin, requireAdministrador };
