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
     6. Formulario               (validación + envío por WhatsApp)
     7. Capa GSAP                (revelado, parallax, componentes)

   El carrusel, la galería en acordeón, el visor de fotos, el botón
   imantado y el foco de luz viven en componentes.js.
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
     6. GALERÍA
     Los filtros por categoría y el visor de fotos que vivían acá se
     retiraron al pasar la galería a acordeón: ese marcado ya no
     existe y el visor nuevo está en componentes.js, junto al
     acordeón que lo abre. Eran 142 líneas que nunca encontraban
     un solo elemento.
     ------------------------------------------------------------ */

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
      // Inicio: React maneja título, bajada Y botones (SplitText +
      // FadeContent) — no queda nada de este código para correr ahí.
      if (document.querySelector('.hero[data-anim="react"]')) return;

      // Nosotros: React solo maneja el título (BlurText) — la bajada y
      // las migas siguen siendo de este código vainilla, así que NO se
      // corta la función entera, solo el bloque que animaría el título
      // por segunda vez y movería el nodo donde vive el componente React.
      var soloTitulo = !!document.querySelector('.hero[data-anim="react-titulo"]');

      var tituloPortada = document.querySelector('.hero__titulo--texto');
      var h1Interior = !soloTitulo && !tituloPortada && document.querySelector('.hero__titulo');
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
      if (elementoTitulo && !soloTitulo) {
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

    /* ------------------------------------------------------------
       REVELADO POR SCROLL — ScrollTrigger.batch
       Reemplaza a los componentes de React (FadeContent,
       AnimatedContent, ScrollReveal). El HTML ya viene completo
       desde el servidor; esto solo le agrega la entrada.

       batch() agrupa los elementos que entran juntos a la pantalla
       y los escalona entre sí. Con un ScrollTrigger por elemento
       cada uno entraría por su cuenta y se pierde el ritmo.
       ------------------------------------------------------------ */
    function iniciarRevelado(gsap) {
      var elementos = document.querySelectorAll('[data-entrada]');
      if (!elementos.length) return;

      if (reducir) {
        gsap.set(elementos, { opacity: 1, y: 0, filter: 'none' });
        return;
      }

      var variantes = {
        subir:   { y: 40, opacity: 0 },
        escala:  { scale: 0.92, opacity: 0 },
        difuso:  { filter: 'blur(10px)', opacity: 0 },
        aparecer:{ opacity: 0 }
      };

      // Agrupados por variante: batch escalona los de un mismo lote,
      // y no tendría sentido mezclar los que entran de formas distintas.
      Object.keys(variantes).forEach(function (nombre) {
        var deEsteTipo = document.querySelectorAll('[data-entrada="' + nombre + '"]');
        if (!deEsteTipo.length) return;

        gsap.set(deEsteTipo, variantes[nombre]);

        window.ScrollTrigger.batch(deEsteTipo, {
          start: 'top 88%',
          once: true,
          onEnter: function (lote) {
            gsap.to(lote, {
              opacity: 1, y: 0, scale: 1, filter: 'blur(0px)',
              duration: 0.7, ease: 'expo.out', stagger: 0.08, overwrite: true
            });
          }
        });
      });

      // Red de seguridad, igual que la del motor de revelado de arriba:
      // si algún trigger no llega a dispararse, nadie se queda mirando
      // un hueco en blanco.
      setTimeout(function () {
        gsap.to(elementos, { opacity: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.3, overwrite: 'auto' });
      }, 5000);
    }

    /* ------------------------------------------------------------
       HEADER QUE SE ESCONDE
       Baja del todo al hacer scroll hacia abajo y vuelve al subir,
       para dejarle la pantalla al contenido. Se queda quieto arriba
       de todo y mientras el menú móvil está abierto.
       ------------------------------------------------------------ */
    function iniciarHeaderQueSeEsconde(gsap) {
      var barra = document.querySelector('.header');
      if (!barra || reducir) return;

      var ultimo = window.scrollY;
      var escondido = false;
      var UMBRAL = 10;    // ruido de scroll que no cuenta como intención
      var DESDE = 200;    // arriba de todo el header siempre se ve

      var mover = function (ocultar) {
        if (ocultar === escondido) return;
        escondido = ocultar;
        gsap.to(barra, {
          yPercent: ocultar ? -100 : 0,
          duration: 0.45,
          ease: 'power2.inOut'
        });
      };

      window.addEventListener('scroll', function () {
        var y = window.scrollY;
        var delta = y - ultimo;

        // El menú abierto ocupa toda la pantalla: esconder el header
        // ahí se llevaría el botón de cerrar.
        var menuAbierto = document.querySelector('.menu-movil.abierto');

        if (Math.abs(delta) > UMBRAL && !menuAbierto) {
          mover(delta > 0 && y > DESDE);
        }
        ultimo = y;
      }, { passive: true });
    }

    function iniciarMovimiento() {
      var gsap = window.gsap;
      if (!gsap || !window.ScrollTrigger) return;
      gsap.registerPlugin(window.ScrollTrigger);

      // El hero corre en TODAS las pantallas (la portada la lleva React
      // ahora — ver el corte data-anim="react" en iniciarEntradaHero).
      if (window.SplitText) gsap.registerPlugin(window.SplitText);

      iniciarEntradaHero(gsap);
      iniciarTitulosSeccion(gsap);
      iniciarRevelado(gsap);
      iniciarHeaderQueSeEsconde(gsap);
      if (window.iniciarComponentes) window.iniciarComponentes(gsap);

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
      // Sin filtro de pantalla: antes GSAP era solo para adornos de
      // escritorio, pero ahora el carrusel y la galería lo necesitan
      // también en celular. A cambio se sacó React (194 KB), así que
      // el celular igual descarga bastante menos que antes.
      yaSePidioGsap = true;
      Promise.all([
        cargarScript('/js/vendor/gsap.min.js'),
        cargarScript('/js/vendor/ScrollTrigger.min.js'),
        cargarScript('/js/vendor/SplitText.min.js')
      ]).then(iniciarMovimiento).catch(function () {
        // Sin GSAP el sitio sigue completo: el contenido ya está en el
        // HTML y los reveals tienen su red de seguridad en CSS.
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
