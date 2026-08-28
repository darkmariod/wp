/* ============================================================
   Unidad Educativa Pestalozzi Ambato — main.js
   Vanilla JS, sin dependencias en el núcleo. Un solo archivo
   con módulos internos (IIFE por responsabilidad).

   Estructura:
     1. Motor de revelado        (IntersectionObserver + CSS)
     2. Header compacto          (scroll)
     3. Menú móvil               (hamburguesa + velo + Esc)
     4. Año automático           (footer)
     5. Scrollspy de secciones   (solo anclas locales)
     6. Galería                  (filtros + contador)
     7. Lightbox                 (teclado + foco atrapado)
     8. Formulario               (validación + envío por WhatsApp)
     9. Capa GSAP                (parallax, solo escritorio)
   ============================================================ */

(function () {
  'use strict';

  /* ------------------------------------------------------------
     1. MOTOR DE REVELADO
     Agrega .visible cuando el elemento entra al viewport.
     Con prefers-reduced-motion se revela todo de inmediato.
     ------------------------------------------------------------ */
  var reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------
     DATOS DEL SITIO
     El Layout imprime un <script type="application/json"> con lo que
     este archivo necesita del CMS (teléfono, textos de la galería).
     Si por lo que sea no está, se usan los valores de respaldo: el
     sitio nunca se queda sin número de contacto.
     ------------------------------------------------------------ */
  var DATOS = (function () {
    var nodo = document.getElementById('datos-sitio');
    if (!nodo) return {};
    try {
      return JSON.parse(nodo.textContent) || {};
    } catch (e) {
      return {};
    }
  })();

  function revelarTodo() {
    document.querySelectorAll('[data-revelar]').forEach(function (el) {
      el.classList.add('visible');
    });
  }

  if (reducir) {
    revelarTodo();
  } else if ('IntersectionObserver' in window) {
    var observador = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (entrada) {
        if (!entrada.isIntersecting) return;
        var el = entrada.target;
        var retraso = Number(el.getAttribute('data-retraso') || 0);
        el.style.transitionDelay = (retraso >= 1 ? retraso / 1000 : retraso) + 's';
        el.classList.add('visible');
        observador.unobserve(el);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('[data-revelar]').forEach(function (el) {
      observador.observe(el);
    });

    // Red de seguridad: si por lo que sea el observador no dispara para
    // algún elemento (encontramos casos reales, sin causa clara), que no
    // se quede invisible para siempre. Nadie nota 4 segundos de más;
    // todos notarían una foto que nunca aparece.
    setTimeout(revelarTodo, 4000);
  } else {
    // Sin IntersectionObserver (navegadores muy viejos): contenido visible.
    revelarTodo();
  }

  /* ------------------------------------------------------------
     2. HEADER COMPACTO
     ------------------------------------------------------------ */
  var header = document.querySelector('.header');
  if (header) {
    var actualizarHeader = function () {
      header.classList.toggle('compacto', window.scrollY > 8);
    };
    actualizarHeader();
    window.addEventListener('scroll', actualizarHeader, { passive: true });
  }

  /* ------------------------------------------------------------
     3. MENÚ MÓVIL
     ------------------------------------------------------------ */
  var botonMenu = document.querySelector('.hamburguesa');
  var menu = document.querySelector('.menu-movil');
  var velo = document.querySelector('.velo');

  if (botonMenu && menu) {
    var cerrarMenu = function () {
      botonMenu.setAttribute('aria-expanded', 'false');
      botonMenu.setAttribute('aria-label', 'Abrir menú');
      menu.classList.remove('abierto');
      if (velo) velo.classList.remove('visible');
      document.body.style.overflow = '';
    };

    var abrirMenu = function () {
      botonMenu.setAttribute('aria-expanded', 'true');
      botonMenu.setAttribute('aria-label', 'Cerrar menú');
      menu.classList.add('abierto');
      if (velo) velo.classList.add('visible');
      document.body.style.overflow = 'hidden';
    };

    botonMenu.addEventListener('click', function () {
      if (botonMenu.getAttribute('aria-expanded') === 'true') {
        cerrarMenu();
      } else {
        abrirMenu();
      }
    });

    if (velo) velo.addEventListener('click', cerrarMenu);
    menu.querySelectorAll('a').forEach(function (enlace) {
      enlace.addEventListener('click', cerrarMenu);
    });

    document.addEventListener('keydown', function (evento) {
      if (evento.key === 'Escape' && menu.classList.contains('abierto')) {
        cerrarMenu();
      }
    });
  }

  /* ------------------------------------------------------------
     4. AÑO AUTOMÁTICO
     ------------------------------------------------------------ */
  var anio = document.getElementById('anio');
  if (anio) anio.textContent = String(new Date().getFullYear());

  /* ------------------------------------------------------------
     5. SCROLLSPY (anclas locales)
     Marca .activo en enlaces a[href^="#"] según la sección
     visible. En este sitio las secciones viven en index.html;
     el nav principal navega entre páginas y ya trae .activo
     estático por página.
     ------------------------------------------------------------ */
  var enlacesAncla = Array.from(document.querySelectorAll('a[href^="#"]'));
  if (enlacesAncla.length && 'IntersectionObserver' in window) {
    var secciones = enlacesAncla
      .map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); })
      .filter(Boolean);

    var spy = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (entrada) {
        if (!entrada.isIntersecting) return;
        enlacesAncla.forEach(function (a) {
          a.classList.toggle('activo', a.getAttribute('href') === '#' + entrada.target.id);
        });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });

    secciones.forEach(function (seccion) { spy.observe(seccion); });
  }

  /* ------------------------------------------------------------
     6. GALERÍA — filtros + contador
     ------------------------------------------------------------ */
  var botonesFiltro = Array.prototype.slice.call(document.querySelectorAll('.filtro'));
  var fotos = Array.prototype.slice.call(document.querySelectorAll('.foto'));
  var contador = document.getElementById('cuenta-fotos');
  var descripcionGaleria = document.getElementById('galeria-desc');

  function plural(n) {
    return n + ' ' + (n === 1 ? 'fotografía' : 'fotografías');
  }

  // Un texto propio por categoría: no es la misma frase repetida con
  // otro título, describe lo que realmente hay en esa sección.
  // Los edita el colegio desde el CMS; el objeto de acá abajo solo
  // entra en juego si por algo no llegaron.
  var DESCRIPCIONES_GALERIA = DATOS.galeria || {
    todas: 'Un vistazo a la vida diaria del plantel: instalaciones, actividades, deportes y eventos del año lectivo.',
    instalaciones: 'El espacio donde los niños pasan el día: patio, aulas y áreas comunes del plantel.',
    actividades: 'Momentos de aprendizaje dentro y fuera del aula, con la metodología Montessori.',
    deportes: 'Movimiento y juego al aire libre — parte del aprendizaje, no un premio aparte.',
    eventos: 'Celebraciones y actividades especiales del año lectivo. Se suman fotos aquí en cuanto el colegio las entregue.'
  };

  function envolverEnPalabras(el, texto) {
    el.textContent = '';
    var frag = document.createDocumentFragment();
    texto.split(' ').forEach(function (palabra, i) {
      if (i > 0) frag.appendChild(document.createTextNode(' '));
      var span = document.createElement('span');
      span.className = 'galeria-desc__palabra';
      span.textContent = palabra;
      frag.appendChild(span);
    });
    el.appendChild(frag);
  }

  function actualizarDescripcionGaleria(categoria) {
    if (!descripcionGaleria) return;
    var texto = DESCRIPCIONES_GALERIA[categoria] || DESCRIPCIONES_GALERIA.todas;
    if (descripcionGaleria.dataset.texto === texto) return;
    descripcionGaleria.dataset.texto = texto;
    if (reducir) { descripcionGaleria.textContent = texto; return; }
    envolverEnPalabras(descripcionGaleria, texto);
    var palabras = Array.prototype.slice.call(descripcionGaleria.querySelectorAll('.galeria-desc__palabra'));
    palabras.forEach(function (p, i) { p.style.transitionDelay = (i * 30) + 'ms'; });
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        palabras.forEach(function (p) { p.classList.add('visible'); });
      });
    });
  }

  function aplicarFiltro(categoria) {
    var visibles = 0;
    fotos.forEach(function (foto) {
      var coincide = categoria === 'todas' || foto.getAttribute('data-categoria') === categoria;
      foto.classList.toggle('oculta', !coincide);
      if (coincide) visibles += 1;
    });
    if (contador) contador.textContent = plural(visibles);
    actualizarDescripcionGaleria(categoria);
  }

  botonesFiltro.forEach(function (boton) {
    boton.addEventListener('click', function () {
      botonesFiltro.forEach(function (otro) {
        otro.setAttribute('aria-pressed', 'false');
      });
      boton.setAttribute('aria-pressed', 'true');
      aplicarFiltro(boton.getAttribute('data-categoria'));
    });
  });

  // Entrada inicial de la descripción: mismo efecto de palabra por
  // palabra que al cambiar de filtro, para que no aparezca "muerta"
  // apenas se carga la página.
  actualizarDescripcionGaleria('todas');

  /* ------------------------------------------------------------
     7. LIGHTBOX
     ------------------------------------------------------------ */
  var lightbox = document.querySelector('.lightbox');
  if (lightbox && fotos.length) {
    var lightboxImg = lightbox.querySelector('img');
    var lightboxCuenta = lightbox.querySelector('.lightbox__cuenta');
    var botonCerrar = lightbox.querySelector('.lightbox__cerrar');
    var botonAnt = lightbox.querySelector('.lightbox__ant');
    var botonSig = lightbox.querySelector('.lightbox__sig');

    var indice = 0;
    var ultimoFoco = null;

    function mostrarFoto(i) {
      indice = (i + fotos.length) % fotos.length;
      var foto = fotos[indice];
      var fotoImg = foto.querySelector('img');
      lightboxImg.src = fotoImg.src;
      lightboxImg.alt = fotoImg.alt;
      if (lightboxCuenta) {
        lightboxCuenta.textContent = (indice + 1) + ' / ' + fotos.length;
      }
    }

    function abrirLightbox(i) {
      ultimoFoco = document.activeElement;
      mostrarFoto(i);
      lightbox.classList.add('abierto');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      botonCerrar.focus();
    }

    function cerrarLightbox() {
      lightbox.classList.remove('abierto');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (ultimoFoco) ultimoFoco.focus();
    }

    fotos.forEach(function (foto, i) {
      foto.addEventListener('click', function () { abrirLightbox(i); });
    });

    botonCerrar.addEventListener('click', cerrarLightbox);
    botonAnt.addEventListener('click', function () { mostrarFoto(indice - 1); });
    botonSig.addEventListener('click', function () { mostrarFoto(indice + 1); });

    lightbox.addEventListener('click', function (evento) {
      if (evento.target === lightbox) cerrarLightbox();
    });

    document.addEventListener('keydown', function (evento) {
      if (!lightbox.classList.contains('abierto')) return;
      if (evento.key === 'Escape') cerrarLightbox();
      if (evento.key === 'ArrowLeft') mostrarFoto(indice - 1);
      if (evento.key === 'ArrowRight') mostrarFoto(indice + 1);
    });
  }

  /* ------------------------------------------------------------
     8. FORMULARIO — validación + envío por WhatsApp
     Sitio estático sin backend: el mensaje se compone y se abre
     wa.me con el texto prefabricado. La trampa antispam
     (honeypot) descarta envíos de bots.
     ------------------------------------------------------------ */
  var formulario = document.getElementById('form-contacto');
  if (formulario) {
    var aviso = formulario.querySelector('.form-aviso');
    var campos = Array.prototype.slice.call(formulario.querySelectorAll('.campo[data-validar]'));
    // Lo edita el colegio en el CMS ("Datos del colegio"). El número de
    // respaldo es el oficial confirmado el 2026-08-14.
    var TELEFONO_WHATSAPP = DATOS.whatsapp || '593998246396';

    var esValidoEmail = function (valor) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
    };

    var validarCampo = function (campo) {
      var entrada = campo.querySelector('input, textarea');
      var minimo = Number(entrada.getAttribute('data-min') || 0);
      var valor = entrada.value.trim();
      var valido = valor.length >= minimo;
      if (valido && entrada.type === 'email') valido = esValidoEmail(valor);
      if (valido) campo.removeAttribute('data-error');
      else campo.setAttribute('data-error', '');
      return valido;
    };

    var validarTodo = function () {
      var todoValido = true;
      var primerInvalido = null;
      campos.forEach(function (campo) {
        var valido = validarCampo(campo);
        if (!valido && !primerInvalido) primerInvalido = campo.querySelector('input, textarea');
        todoValido = todoValido && valido;
      });
      if (primerInvalido) primerInvalido.focus();
      return todoValido;
    };

    campos.forEach(function (campo) {
      var entrada = campo.querySelector('input, textarea');
      entrada.addEventListener('input', function () {
        if (campo.hasAttribute('data-error')) validarCampo(campo);
      });
    });

    var mostrarMensaje = function (tipo, texto) {
      if (!aviso) return;
      aviso.textContent = texto;
      aviso.className = 'form-aviso ' + tipo;
      aviso.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    };

    formulario.addEventListener('submit', function (evento) {
      evento.preventDefault();

      var trampa = formulario.querySelector('input[name="sitio"]');
      if (trampa && trampa.value.trim() !== '') {
        mostrarMensaje('exito', 'Gracias por escribirnos. Te contactaremos pronto.');
        formulario.reset();
        return;
      }

      if (!validarTodo()) return;

      var nombre = formulario.elements.nombre.value.trim();
      var correo = formulario.elements.correo.value.trim();
      var telefonoCampo = formulario.elements.telefono.value.trim();
      var nivel = formulario.elements.nivel.value;
      var mensaje = formulario.elements.mensaje.value.trim();

      var lineas = [
        'Hola, soy ' + nombre + '.',
        correo ? 'Mi correo es ' + correo + '.' : '',
        telefonoCampo ? 'Mi teléfono es ' + telefonoCampo + '.' : '',
        nivel ? 'Me interesa: ' + nivel + '.' : '',
        '',
        mensaje
      ];
      var texto = lineas.filter(Boolean).join('\n');
      var url = 'https://wa.me/' + TELEFONO_WHATSAPP + '?text=' + encodeURIComponent(texto);

      mostrarMensaje('exito', 'Gracias por escribirnos. Te abrimos WhatsApp para terminar el envío.');
      window.open(url, '_blank', 'noopener');
      formulario.reset();
    });
  }

  /* ------------------------------------------------------------
     9. CAPA GSAP — parallax y scroll ligado (solo escritorio)
     Los 46 KB (gzip) de gsap + ScrollTrigger no se descargan en
     celular. Si GSAP falla, el sitio sigue completo: la capa
     base ya animó todo.
     ------------------------------------------------------------ */
  // Funciones, no booleanos fijos: capturar el valor una sola vez aquí
  // arriba se leyó falso en algún momento durante la carga (el layout
  // todavía no había asentado el ancho real), y esa lectura equivocada
  // quedaba pegada para toda la sesión — GSAP nunca llegaba a pedirse
  // ni en escritorio. Se evalúa en el momento real de cada decisión.
  var esPunteroFino = function () { return window.matchMedia('(pointer: fine)').matches; };
  var esPantallaAncha = function () { return window.matchMedia('(min-width: 1024px)').matches; };

  if (!reducir) {
    var cargarScript = function (src) {
      return new Promise(function (resolver, rechazar) {
        var s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.onload = resolver;
        s.onerror = rechazar;
        document.head.appendChild(s);
      });
    };

    // Split del titular en letras (preserva <br> y <em>) para el efecto
    // de maquina de escribir en la primera entrada. Misma recursion que
    // dividirEnPalabras, a nivel de caracter en vez de palabra.
    function envolverLetra(caracter) {
      var s = document.createElement('span');
      s.className = 'letra';
      s.textContent = caracter;
      return s;
    }

    function dividirEnLetras(el) {
      var nodos = Array.prototype.slice.call(el.childNodes);
      var fragmento = document.createDocumentFragment();
      nodos.forEach(function (nodo) {
        if (nodo.nodeType === 3) {
          nodo.textContent.split('').forEach(function (caracter) {
            if (caracter === ' ') fragmento.appendChild(document.createTextNode(' '));
            else fragmento.appendChild(envolverLetra(caracter));
          });
        } else if (nodo.nodeName === 'BR') {
          fragmento.appendChild(document.createElement('br'));
        } else {
          dividirEnLetras(nodo);
          fragmento.appendChild(nodo);
        }
      });
      el.textContent = '';
      el.appendChild(fragmento);
    }

    // Split del titular en palabras con máscara (preserva <br> y <em>).
    function envolverPalabra(texto) {
      var m = document.createElement('span');
      m.className = 'palabra';
      var i = document.createElement('span');
      i.className = 'palabra-int';
      i.textContent = texto;
      m.appendChild(i);
      return m;
    }

    function dividirEnPalabras(el) {
      var nodos = Array.prototype.slice.call(el.childNodes);
      var fragmento = document.createDocumentFragment();
      nodos.forEach(function (nodo) {
        if (nodo.nodeType === 3) {
          nodo.textContent.split(/(\s+)/).forEach(function (parte) {
            if (!parte) return;
            if (/^\s+$/.test(parte)) fragmento.appendChild(document.createTextNode(' '));
            else fragmento.appendChild(envolverPalabra(parte));
          });
        } else if (nodo.nodeName === 'BR') {
          fragmento.appendChild(document.createElement('br'));
        } else {
          dividirEnPalabras(nodo);
          fragmento.appendChild(nodo);
        }
      });
      el.textContent = '';
      el.appendChild(fragmento);
    }

    // E0 — entrada del hero: maquina de escribir letra por letra.
    // Pedido explicito del cliente: "que parezca que se escriben al
    // recargar". Solo opacity (sin transform): no necesita mascara ni
    // perspectiva 3D, que sí usa la rotacion posterior de titulares.
    //
    // Portada: el titular vive en .hero__titulo--texto (hay dos, para
    // la rotación). Páginas interiores: no hay ese span, el texto está
    // directo en .hero__titulo — antes esta función cortaba en seco
    // ahí mismo y ni migas ni bajada llegaban a animarse en esas 3
    // páginas.
    function iniciarEntradaHero(gsap) {
      var tituloPortada = document.querySelector('.hero__titulo--texto');
      var h1Interior = !tituloPortada && document.querySelector('.hero__titulo');
      var elementoTitulo = tituloPortada;
      if (h1Interior) {
        // .hero__titulo es display:grid. Partir las letras directo ahí
        // las vuelve items del grid (cada una en su propia fila: el
        // titular salía apilado letra por letra, una por línea). Se
        // envuelve primero en un span, igual que hace la portada con
        // .hero__titulo--texto, para que el grid solo vea un item.
        if (!h1Interior.dataset.split) {
          var envoltorio = document.createElement('span');
          envoltorio.className = 'hero__titulo--texto';
          while (h1Interior.firstChild) envoltorio.appendChild(h1Interior.firstChild);
          h1Interior.appendChild(envoltorio);
          h1Interior.dataset.split = '1';
        }
        elementoTitulo = h1Interior.querySelector('.hero__titulo--texto');
      }
      if (elementoTitulo) {
        if (!elementoTitulo.dataset.split) { dividirEnLetras(elementoTitulo); elementoTitulo.dataset.split = '1'; }
        gsap.from(elementoTitulo.querySelectorAll('.letra'), {
          opacity: 0,
          duration: 0.01,
          stagger: 0.032,
          ease: 'none'
        });
      }
      if (document.querySelector('.hero__migas')) {
        gsap.fromTo('.hero__migas',
          { y: 16, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out',
            clearProps: 'opacity,transform' });
      }
      if (document.querySelector('.hero__bajada')) {
        // fromTo y no from: from fija el estado inicial de inmediato y, si el
        // tween se interrumpe, el elemento queda invisible para siempre.
        // clearProps devuelve el control al CSS al terminar.
        gsap.fromTo('.hero__bajada',
          { y: 28, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.9, delay: 0.2, ease: 'power3.out',
            clearProps: 'opacity,transform' });
      }
      if (document.querySelector('.hero__acciones')) {
        gsap.fromTo('.hero__acciones',
          { y: 28, opacity: 0 },
          { y: 0, opacity: 1, duration: 0.9, delay: 0.35, ease: 'power3.out',
            clearProps: 'opacity,transform' });
      }
    }

    // Rotación del titular al estilo Centeno: las palabras del mensaje
    // actual salen en 3D mientras entran las del siguiente (cada 9 s).
    function iniciarRotacionTitulo(gsap) {
      var textos = document.querySelectorAll('.hero__titulo--texto');
      if (textos.length < 2) return;
      gsap.set(textos, { perspective: 1000 });
      var actual = 0;
      var corriendo = false;
      var tiempo = 9000;

      function rotar() {
        if (corriendo) return;
        corriendo = true;
        var viejo = textos[actual];
        var nuevo = textos[(actual + 1) % textos.length];
        actual = (actual + 1) % textos.length;

        nuevo.hidden = false;
        nuevo.classList.remove('escondido');
        if (!nuevo.dataset.split) { dividirEnPalabras(nuevo); nuevo.dataset.split = '1'; }

        // .palabra-int, .letra: el titular principal (textos[0]) ya
        // llega dividido en LETRAS, no en palabras — lo divide
        // iniciarEntradaHero para el efecto de máquina de escribir, y
        // usa la misma bandera dataset.split que este código, así que
        // nunca se vuelve a dividir en palabras. Sin buscar también
        // .letra, viejas quedaba vacío la primera vez que ese titular
        // salía de rotación: no había nada que animar, así que nunca
        // se desvanecía y quedaba superpuesto con el texto entrante.
        var viejas = viejo.querySelectorAll('.palabra-int, .letra');
        var nuevas = nuevo.querySelectorAll('.palabra-int, .letra');
        gsap.set(nuevas, { opacity: 0, xPercent: 0, yPercent: 0, rotationX: 0, z: 0 });

        var tl = gsap.timeline({ onComplete: function () {
          viejo.classList.add('escondido');
          viejo.hidden = true;
          corriendo = false;
          setTimeout(rotar, tiempo);
        }});
        tl.fromTo(viejas,
          { opacity: 1, xPercent: 0, yPercent: 0, rotationX: 0, z: 0 },
          { duration: 0.5, ease: 'power3.in', opacity: 0,
            xPercent: function () { return gsap.utils.random(-40, 40); },
            yPercent: function () { return gsap.utils.random(-8, 8); },
            rotationX: function () { return gsap.utils.random(-90, 90); },
            z: function () { return gsap.utils.random(-500, -300); },
            stagger: { each: 0.02, from: 'random' } }, 0)
        .fromTo(nuevas,
          { opacity: 0, xPercent: function () { return gsap.utils.random(-40, 40); },
            yPercent: function () { return gsap.utils.random(-8, 8); },
            rotationX: function () { return gsap.utils.random(-90, 90); },
            z: function () { return gsap.utils.random(300, 500); } },
          { duration: 0.8, ease: 'power3.out', opacity: 1, xPercent: 0, yPercent: 0, rotationX: 0, z: 0,
            // 0.65s, no 0.42s: con stagger por palabra, una frase de
            // varias palabras tarda duration(0.5) + (n-1)*0.02 en
            // desvanecerse del todo. A los 0.42s el texto viejo todavía
            // se veía, superpuesto con el que entraba — se veía roto.
            stagger: { each: 0.02, from: 'random' } }, 0.65);
      }

      setTimeout(rotar, tiempo);
    }

    // Titulares de sección: entran por palabra al llegar con el scroll
    // (mismo split que la rotación del hero). "Quiénes somos" entra
    // letra por letra, como el titular principal — pedido explícito
    // del cliente para ese bloque.
    function iniciarTitulosSeccion(gsap) {
      document.querySelectorAll('.enc-seccion h2').forEach(function (h2) {
        if (!h2.dataset.split) { dividirEnPalabras(h2); h2.dataset.split = '1'; }
        gsap.from(h2.querySelectorAll('.palabra-int'), {
          yPercent: 100,
          opacity: 0,
          duration: 0.6,
          ease: 'power3.out',
          stagger: 0.04,
          scrollTrigger: { trigger: h2, start: 'top 85%', toggleActions: 'play none none none' }
        });
      });

      var tituloQuienesSomos = document.querySelector('.partido__texto h2');
      if (tituloQuienesSomos) {
        if (!tituloQuienesSomos.dataset.split) { dividirEnLetras(tituloQuienesSomos); tituloQuienesSomos.dataset.split = '1'; }
        gsap.from(tituloQuienesSomos.querySelectorAll('.letra'), {
          opacity: 0,
          duration: 0.01,
          stagger: 0.028,
          ease: 'none',
          scrollTrigger: { trigger: tituloQuienesSomos, start: 'top 85%', toggleActions: 'play none none none' }
        });
      }
    }

    function iniciarMovimiento() {
      var gsap = window.gsap;
      if (!gsap || !window.ScrollTrigger) return;
      gsap.registerPlugin(window.ScrollTrigger);

      // El hero (entrada + rotación) corre en TODAS las pantallas.
      iniciarEntradaHero(gsap);
      iniciarRotacionTitulo(gsap);
      iniciarTitulosSeccion(gsap);

      // La entrada de las fotos ahora la hace [data-revelar="zoom"] (motor
      // base, funciona en todas las pantallas). Aquí solo queda el
      // parallax por columna, que es un efecto de scroll continuo y
      // exclusivo de escritorio, no una entrada.
      if (document.querySelector('.mosaico')) {
      // Parallax por columna: cada columna se desplaza distinto, asi el
      // mosaico se siente con profundidad al scrollear en vez de moverse
      // en bloque. Solo transform, y solo escritorio.
      var DESPLAZAMIENTO = [7, -7, 10];
      Array.prototype.slice.call(document.querySelectorAll('.mosaico .foto'))
        .forEach(function (foto) {
          var col = Number(foto.getAttribute('data-col')) || 0;
          gsap.to(foto, {
            yPercent: DESPLAZAMIENTO[col],
            ease: 'none',
            scrollTrigger: {
              trigger: foto,
              start: 'top bottom',
              end: 'bottom top',
              scrub: true
            }
          });
        });
      }

      // Parallax: solo escritorio con mouse fino y pantalla amplia.
      if (!esPunteroFino() || !esPantallaAncha()) return;

      // P1 — foto de portada/interiores se mueve más lento (sin huecos: scale).
      if (document.querySelector('.hero__foto')) {
        gsap.fromTo('.hero__foto', { yPercent: -12, scale: 1.15 }, {
          yPercent: 12,
          scale: 1.15,
          ease: 'none',
          scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
        });
      }

      // P1b — el video del hero se mueve igual que la foto (misma capa).
      if (document.querySelector('.hero__video')) {
        gsap.fromTo('.hero__video', { yPercent: -12, scale: 1.15 }, {
          yPercent: 12,
          scale: 1.15,
          ease: 'none',
          scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
        });
      }

      // P2 — el titular sube más rápido que el fondo.
      if (document.querySelector('.hero__titulo')) {
        gsap.fromTo('.hero__titulo', { yPercent: 15 }, {
          yPercent: -35,
          ease: 'none',
          scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true }
        });
      }

      // P3 — fotos de galería con desplazamiento leve por columna.
      if (document.querySelector('.mosaico')) {
        gsap.utils.toArray('.mosaico .foto').forEach(function (foto, i) {
          var magnitud = [14, 22, 10][i % 3];
          gsap.fromTo(foto, { yPercent: magnitud }, {
            yPercent: -magnitud,
            ease: 'none',
            scrollTrigger: { trigger: '.mosaico', start: 'top bottom', end: 'bottom top', scrub: true }
          });
        });
      }

      // P4 — la franja entra con más presencia.
      if (document.querySelector('.franja')) {
        gsap.fromTo('.franja', { yPercent: 10, opacity: 0.7, scale: 0.96 }, {
          yPercent: 0,
          opacity: 1,
          scale: 1,
          ease: 'none',
          scrollTrigger: { trigger: '.franja', start: 'top 85%', end: 'top 40%', scrub: true }
        });
      }

      // P4b — fondo "fijo" de la franja con scroll real: el marco se
      // mueve más lento que el texto, en vez del truco
      // background-attachment:fixed que usan los builders tipo
      // Elementor. El zoom de kb-zoom sigue corriendo aparte, en la
      // foto de adentro, sin pisarse con este scrub.
      if (document.querySelector('.franja__marco')) {
        gsap.fromTo('.franja__marco', { yPercent: -8 }, {
          yPercent: 8,
          ease: 'none',
          scrollTrigger: { trigger: '.franja', start: 'top bottom', end: 'bottom top', scrub: true }
        });
      }

      // P6 — zoom lento de la fachada (Nosotros).
      if (document.querySelector('.partido__foto img')) {
        gsap.fromTo('.partido__foto img', { scale: 1 }, {
          scale: 1.15,
          ease: 'none',
          scrollTrigger: { trigger: '.partido__foto', start: 'top bottom', end: 'bottom top', scrub: true }
        });
      }
    }

    // La condición de escritorio se evalúa DENTRO del listener de load,
    // no antes de registrarlo: evaluarla al declarar las variables (más
    // arriba en el archivo) a veces leía el ancho de pantalla antes de
    // que el layout terminara de asentarse, daba falso en escritorio
    // real y GSAP nunca se pedía — ni el listener llegaba a registrarse.
    var yaSePidioGsap = false;
    var pedirGsapSiCorresponde = function () {
      if (yaSePidioGsap) return;
      if (!esPunteroFino() || !esPantallaAncha()) return;
      yaSePidioGsap = true;
      Promise.all([
        cargarScript('/js/vendor/gsap.min.js'),
        cargarScript('/js/vendor/ScrollTrigger.min.js')
      ]).then(iniciarMovimiento).catch(function () {
        // Sin GSAP el sitio sigue completo: nada que hacer.
      });
    };
    window.addEventListener('load', function () {
      // En algunos casos innerWidth todavía no está asentado justo en el
      // instante del evento load (se vio 0 ahí mismo aunque la pantalla
      // sí era de escritorio un instante después). Un reintento corto
      // cubre esa ventana sin agregar peso: si el primer chequeo ya
      // pasó, el segundo no hace nada (yaSePidioGsap corta el resto).
      pedirGsapSiCorresponde();
      setTimeout(pedirGsapSiCorresponde, 150);
    });
  }

  /* ------------------------------------------------------------
     10. VIDEO DEL HERO — arranca con fade cuando puede reproducirse
     El poster (foto) es el primer paint; el video aparece encima
     cuando el navegador ya puede reproducirlo sin tirones.
     ------------------------------------------------------------ */
  var videoHero = document.querySelector('.hero__video');
  if (videoHero) {
    if (reducir) {
      videoHero.remove();
    } else {
      var mostrarVideo = function () {
        videoHero.classList.add('hero__video--listo');
      };
      if (videoHero.readyState >= 3) mostrarVideo();
      else videoHero.addEventListener('canplay', mostrarVideo, { once: true });
      videoHero.play().catch(function () {
        // Autoplay bloqueado (p. ej. iOS con datos): queda la foto como poster.
      });
    }
  }

  /* ------------------------------------------------------------
     BOTÓN DE CONTACTO FLOTANTE (speed dial)
     Sustituye al bloque genérico de contacto. Sin librerías: en
     celular es donde más se usa y ahí no cargamos GSAP.
     ------------------------------------------------------------ */
  var fab = document.querySelector('.fab');
  if (fab) {
    var disparador = fab.querySelector('.fab__disparador');
    var opciones = Array.prototype.slice.call(fab.querySelectorAll('.fab__opcion'));

    var abrirFab = function (abierto) {
      fab.setAttribute('data-abierto', String(abierto));
      disparador.setAttribute('aria-expanded', String(abierto));
      disparador.setAttribute('aria-label',
        abierto ? 'Cerrar opciones de contacto' : 'Abrir opciones de contacto');
      // Cerradas no deben ser alcanzables con Tab ni por lectores de pantalla
      opciones.forEach(function (o) {
        o.setAttribute('tabindex', abierto ? '0' : '-1');
        o.setAttribute('aria-hidden', String(!abierto));
      });
      if (abierto) opciones[0].focus();
    };

    disparador.addEventListener('click', function () {
      abrirFab(fab.getAttribute('data-abierto') !== 'true');
    });

    // Clic fuera
    document.addEventListener('click', function (e) {
      if (fab.getAttribute('data-abierto') === 'true' && !fab.contains(e.target)) abrirFab(false);
    });

    // Escape y foco atrapado
    fab.addEventListener('keydown', function (e) {
      if (fab.getAttribute('data-abierto') !== 'true') return;
      if (e.key === 'Escape') { abrirFab(false); disparador.focus(); return; }
      if (e.key !== 'Tab') return;
      var foco = [disparador].concat(opciones);
      var i = foco.indexOf(document.activeElement);
      if (i === -1) return;
      var siguiente = e.shiftKey ? i - 1 : i + 1;
      if (siguiente < 0 || siguiente >= foco.length) { e.preventDefault(); foco[e.shiftKey ? foco.length - 1 : 0].focus(); }
    });

    // Al elegir una opción se cierra
    opciones.forEach(function (o) { o.addEventListener('click', function () { abrirFab(false); }); });

    abrirFab(false);   // estado inicial coherente
  }


  /* ------------------------------------------------------------
     PALABRAS CLAVE Y CORTINAS
     Reutiliza el mismo patron del revelado: el observador agrega
     una clase y el CSS hace el movimiento. Sin GSAP, para que
     tambien funcione en celular.
     ------------------------------------------------------------ */
  var cortinas = Array.prototype.slice.call(document.querySelectorAll('[data-cortina]'));

  if (reducir || !('IntersectionObserver' in window)) {
    cortinas.forEach(function (c) { c.classList.add('visible'); });
  } else {
    if (cortinas.length) {
      var obsCortina = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (e) {
          if (!e.isIntersecting) return;
          e.target.classList.add('visible');
          obsCortina.unobserve(e.target);
        });
      }, { threshold: 0.2, rootMargin: '0px 0px -60px 0px' });
      cortinas.forEach(function (c) { obsCortina.observe(c); });

      // Misma red de seguridad que el revelado general: si el
      // observador no dispara, la foto no se queda invisible.
      setTimeout(function () {
        cortinas.forEach(function (c) { c.classList.add('visible'); });
      }, 4000);
    }
  }

})();
