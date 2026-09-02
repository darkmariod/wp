/* ============================================================
   COMPONENTES — Astro + GSAP, sin React

   Reemplaza a los componentes de ReactBits que vivían como islas
   de React. Mismos efectos, sin hidratación: el HTML llega hecho
   desde el servidor y este archivo solo le agrega el movimiento.

   Se carga después de GSAP (ver main.js). Cada bloque revisa si su
   marcado existe; si no está, no hace nada y sigue.
   ============================================================ */
(function () {
  'use strict';

  var reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var limitar = function (v, min, max) { return Math.min(Math.max(v, min), max); };

  window.iniciarComponentes = function (gsap) {

    /* ----------------------------------------------------------
       1. CARRUSEL DE PROFUNDIDAD
       Las tarjetas se apilan hacia el fondo con desenfoque y
       oscurecimiento crecientes. Arrastre, rueda, teclado y
       avance automático.
       ---------------------------------------------------------- */
    (function carrusel() {
      var raiz = document.querySelector('[data-carrusel]');
      if (!raiz) return;

      var tarjetas = Array.prototype.slice.call(raiz.querySelectorAll('.carrusel__tarjeta'));
      var puntos = Array.prototype.slice.call(raiz.querySelectorAll('.carrusel__punto'));
      var total = tarjetas.length;
      if (total < 2) return;

      var cfg = {
        profundidad: 200, separacion: 80, inclinacion: 20,
        visibles: 4, caida: 0.18, desenfoque: 5, duracion: 0.7
      };

      var pos = 0, foco = 0, escala = 1, tween = null;
      var arrastre = null, temporizadorRueda = null, temporizadorAuto = null;

      function acomodar(p) {
        for (var i = 0; i < total; i++) {
          var el = tarjetas[i];
          var d = i - p;
          // Camino más corto en el bucle: una tarjeta del final puede
          // estar "delante" de la primera.
          d = ((d % total) + total) % total;
          if (d > total / 2) d -= total;

          var atras = Math.max(0, d);
          var seVe = Math.abs(d) <= cfg.visibles + 0.5;

          var tz = -cfg.profundidad * d;
          var tx = cfg.separacion * d;
          var ry = cfg.inclinacion * limitar(d, 0, 1);

          var opacidad = d < 0 ? Math.max(0, 1 + d) : 1;
          if (!seVe) opacidad = 0;

          var brillo = Math.max(0.15, 1 - atras * cfg.caida);
          var borroso = Math.min(cfg.desenfoque, (atras / cfg.visibles) * cfg.desenfoque);

          el.style.transform = 'translate(-50%, -50%) scale(' + escala + ') translateX(' +
            tx.toFixed(2) + 'px) translateZ(' + tz.toFixed(2) + 'px) rotateY(' + ry.toFixed(2) + 'deg)';
          el.style.opacity = opacidad.toFixed(3);
          el.style.filter = 'brightness(' + brillo.toFixed(3) + ') blur(' + borroso.toFixed(2) + 'px)';
          el.style.zIndex = String(Math.round(2000 - d * 20));
          el.style.pointerEvents = seVe && opacidad > 0.05 ? 'auto' : 'none';
        }
      }

      function marcarPunto(i) {
        puntos.forEach(function (p, j) {
          p.classList.toggle('activo', j === i);
          p.setAttribute('aria-selected', j === i ? 'true' : 'false');
        });
        tarjetas.forEach(function (t, j) { t.setAttribute('aria-hidden', j === i ? 'false' : 'true'); });
      }

      function irA(destino, animar) {
        if (tween) tween.kill();
        var proxy = { p: pos };
        tween = gsap.to(proxy, {
          p: destino,
          duration: animar && !reducir ? cfg.duracion : 0,
          ease: 'power3.out',
          onUpdate: function () { pos = proxy.p; acomodar(pos); },
          onComplete: function () { pos = ((pos % total) + total) % total; acomodar(pos); }
        });
      }

      function enfocar(indice, animar) {
        var idx = ((indice % total) + total) % total;
        var delta = idx - pos;
        delta = ((delta % total) + total) % total;
        if (delta > total / 2) delta -= total;
        irA(pos + delta, animar !== false);
        if (idx !== foco) { foco = idx; marcarPunto(idx); }
      }

      function medir() {
        var ancho = raiz.getBoundingClientRect().width;
        var necesario = 300 + cfg.separacion * 2 + 120;
        escala = limitar(ancho / necesario, 0.4, 1);
        acomodar(pos);
      }

      medir();
      marcarPunto(0);
      if (window.ResizeObserver) new ResizeObserver(medir).observe(raiz);
      window.addEventListener('resize', medir);

      // --- controles ---
      var anterior = raiz.querySelector('.carrusel__flecha--ant');
      var siguiente = raiz.querySelector('.carrusel__flecha--sig');
      if (anterior) anterior.addEventListener('click', function () { enfocar(foco - 1); });
      if (siguiente) siguiente.addEventListener('click', function () { enfocar(foco + 1); });
      puntos.forEach(function (p, i) { p.addEventListener('click', function () { enfocar(i); }); });
      tarjetas.forEach(function (t, i) {
        t.addEventListener('click', function () {
          if (arrastre && arrastre.movio) return;
          enfocar(i);
        });
      });

      raiz.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); enfocar(foco - 1); }
        else if (e.key === 'ArrowRight') { e.preventDefault(); enfocar(foco + 1); }
      });

      // --- rueda del ratón ---
      raiz.addEventListener('wheel', function (e) {
        e.preventDefault();
        if (tween) tween.kill();
        var bruto = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
        pos += limitar(bruto / 270, -0.6, 0.6);
        acomodar(pos);
        clearTimeout(temporizadorRueda);
        temporizadorRueda = setTimeout(function () { enfocar(Math.round(pos)); }, 130);
      }, { passive: false });

      // --- arrastre con dedo o ratón ---
      raiz.addEventListener('pointerdown', function (e) {
        if (tween) tween.kill();
        arrastre = { x: e.clientX, desde: pos, ultimaX: e.clientX, t: performance.now(), v: 0, movio: false, id: e.pointerId };
      });
      raiz.addEventListener('pointermove', function (e) {
        if (!arrastre) return;
        var paso = Math.max(300 * 0.55 * escala, 40);
        var dx = e.clientX - arrastre.x;
        if (!arrastre.movio && Math.abs(dx) > 4) {
          arrastre.movio = true;
          try { raiz.setPointerCapture(arrastre.id); } catch (_) {}
        }
        if (!arrastre.movio) return;
        var ahora = performance.now();
        arrastre.v = (e.clientX - arrastre.ultimaX) / Math.max(ahora - arrastre.t, 1);
        arrastre.ultimaX = e.clientX;
        arrastre.t = ahora;
        pos = arrastre.desde - dx / paso;
        acomodar(pos);
      });
      var soltar = function () {
        if (!arrastre) return;
        var d = arrastre;
        arrastre = null;
        if (!d.movio) return;
        var paso = Math.max(300 * 0.55 * escala, 40);
        enfocar(Math.round(pos - (d.v * 180) / paso));
      };
      raiz.addEventListener('pointerup', soltar);
      raiz.addEventListener('pointercancel', soltar);

      // --- avance automático, en pausa al pasar el cursor o enfocar ---
      if (!reducir) {
        var quieto = false;
        var arrancar = function () {
          clearInterval(temporizadorAuto);
          temporizadorAuto = setInterval(function () { if (!quieto) enfocar(foco + 1); }, 4000);
        };
        raiz.addEventListener('mouseenter', function () { quieto = true; });
        raiz.addEventListener('mouseleave', function () { quieto = false; });
        raiz.addEventListener('focusin', function () { quieto = true; });
        raiz.addEventListener('focusout', function () { quieto = false; });
        arrancar();
      }
    })();

    /* ----------------------------------------------------------
       2. GALERÍA EN ACORDEÓN
       Los paneles se expanden al pasar el cursor. Un clic sobre el
       ya abierto amplía la foto — en celular, sin cursor, son dos
       toques.
       ---------------------------------------------------------- */
    (function acordeon() {
      var raiz = document.querySelector('[data-acordeon]');
      if (!raiz) return;

      var paneles = Array.prototype.slice.call(raiz.querySelectorAll('.acordeon__panel'));
      var total = paneles.length;
      if (!total) return;

      var activo = Math.floor(total / 2);
      var tl = null;
      var proporcion = 0.5;

      function acomodar(animar) {
        var crecer = total > 1 ? (proporcion * (total - 1)) / (1 - proporcion) : 1;
        if (tl) tl.kill();
        var dur = animar && !reducir ? 0.6 : 0;
        tl = gsap.timeline();

        paneles.forEach(function (panel, i) {
          var esActivo = i === activo;
          var medio = panel.querySelector('.acordeon__medio');
          var barra = panel.querySelector('.acordeon__barra');
          var texto = panel.querySelector('.acordeon__texto');
          var giro = esActivo ? 0 : (i < activo ? 8 : -8);

          tl.to(panel, { flexGrow: esActivo ? crecer : 1, rotateY: giro, duration: dur, ease: 'power3.out' }, 0);

          if (medio) {
            var deriva = limitar(activo - i, -1.5, 1.5);
            tl.to(medio, {
              xPercent: -50, yPercent: -50,
              x: esActivo ? 0 : deriva * 0.5 * 320 * 0.06,
              '--dim': esActivo ? 0 : 0.35,
              duration: dur, ease: 'power3.out'
            }, 0);
          }
          if (barra && texto) {
            if (esActivo) tl.to([barra, texto], { opacity: 1, x: 0, duration: dur, ease: 'power3.out', stagger: reducir ? 0 : 0.06 }, 0);
            else tl.to([barra, texto], { opacity: 0, x: -14, duration: dur * 0.6, ease: 'power3.out' }, 0);
          }
          panel.setAttribute('aria-current', esActivo ? 'true' : 'false');
        });
      }

      acomodar(false);

      paneles.forEach(function (panel, i) {
        panel.addEventListener('mouseenter', function () {
          if (i !== activo) { activo = i; acomodar(true); }
        });
        panel.addEventListener('focus', function () {
          if (i !== activo) { activo = i; acomodar(true); }
        });
        panel.addEventListener('click', function () {
          if (i !== activo) { activo = i; acomodar(true); }
          else abrirVisor(i);
        });
        panel.addEventListener('keydown', function (e) {
          if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault(); activo = (i + 1) % total; acomodar(true); paneles[activo].focus();
          } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault(); activo = (i - 1 + total) % total; acomodar(true); paneles[activo].focus();
          } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (i === activo) abrirVisor(i); else { activo = i; acomodar(true); }
          }
        });
      });

      /* --- visor de foto ampliada --- */
      var visor = document.querySelector('.lightbox');
      if (!visor) return;
      var visorImg = visor.querySelector('img');
      var visorCuenta = visor.querySelector('.lightbox__cuenta');
      var enVisor = null;
      var focoPrevio = null;

      function pintarVisor() {
        var panel = paneles[enVisor];
        visorImg.src = panel.getAttribute('data-grande') || panel.querySelector('img').src;
        visorImg.alt = panel.querySelector('img').alt;
        if (visorCuenta) visorCuenta.textContent = (enVisor + 1) + ' / ' + total;
      }

      function abrirVisor(i) {
        enVisor = i;
        focoPrevio = document.activeElement;
        pintarVisor();
        visor.classList.add('abierto');
        visor.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        var cerrar = visor.querySelector('.lightbox__cerrar');
        if (cerrar) cerrar.focus();
      }

      function cerrarVisor() {
        enVisor = null;
        visor.classList.remove('abierto');
        visor.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        // Devolver el foco a donde estaba: sin esto, quien navega con
        // teclado queda al principio de la página al cerrar.
        if (focoPrevio && focoPrevio.focus) focoPrevio.focus();
      }

      var mover = function (paso) {
        if (enVisor === null) return;
        enVisor = (enVisor + paso + total) % total;
        pintarVisor();
      };

      var btnCerrar = visor.querySelector('.lightbox__cerrar');
      var btnAnt = visor.querySelector('.lightbox__ant');
      var btnSig = visor.querySelector('.lightbox__sig');
      if (btnCerrar) btnCerrar.addEventListener('click', cerrarVisor);
      if (btnAnt) btnAnt.addEventListener('click', function () { mover(-1); });
      if (btnSig) btnSig.addEventListener('click', function () { mover(1); });
      visor.addEventListener('click', function (e) { if (e.target === visor) cerrarVisor(); });

      document.addEventListener('keydown', function (e) {
        if (enVisor === null) return;
        if (e.key === 'Escape') cerrarVisor();
        else if (e.key === 'ArrowRight') mover(1);
        else if (e.key === 'ArrowLeft') mover(-1);
      });
    })();

    /* ----------------------------------------------------------
       3. BOTÓN IMANTADO
       El botón sigue al cursor dentro de un radio. Solo con ratón:
       en pantallas táctiles no hay puntero que seguir.
       ---------------------------------------------------------- */
    (function iman() {
      if (reducir || !window.matchMedia('(pointer: fine)').matches) return;
      var objetivos = document.querySelectorAll('[data-iman]');
      if (!objetivos.length) return;

      Array.prototype.forEach.call(objetivos, function (el) {
        var moverX = gsap.quickTo(el, 'x', { duration: 0.4, ease: 'power3.out' });
        var moverY = gsap.quickTo(el, 'y', { duration: 0.4, ease: 'power3.out' });
        var radio = 40;

        document.addEventListener('mousemove', function (e) {
          var r = el.getBoundingClientRect();
          var cx = r.left + r.width / 2;
          var cy = r.top + r.height / 2;
          var dentro = e.clientX > r.left - radio && e.clientX < r.right + radio &&
                       e.clientY > r.top - radio && e.clientY < r.bottom + radio;
          if (dentro) { moverX((e.clientX - cx) / 6); moverY((e.clientY - cy) / 6); }
          else { moverX(0); moverY(0); }
        }, { passive: true });
      });
    })();

    /* ----------------------------------------------------------
       4. TARJETAS CON FOCO DE LUZ
       Un resplandor sigue al cursor dentro de la tarjeta. Se hace
       con variables CSS: el JS solo escribe la posición.
       ---------------------------------------------------------- */
    (function focoDeLuz() {
      if (!window.matchMedia('(pointer: fine)').matches) return;
      var tarjetas = document.querySelectorAll('[data-foco]');
      Array.prototype.forEach.call(tarjetas, function (t) {
        t.addEventListener('mousemove', function (e) {
          var r = t.getBoundingClientRect();
          t.style.setProperty('--foco-x', (e.clientX - r.left) + 'px');
          t.style.setProperty('--foco-y', (e.clientY - r.top) + 'px');
          t.style.setProperty('--foco-op', '0.6');
        }, { passive: true });
        t.addEventListener('mouseleave', function () {
          t.style.setProperty('--foco-op', '0');
        });
      });
    })();
  };
})();
