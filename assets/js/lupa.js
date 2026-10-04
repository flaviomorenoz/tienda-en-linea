/* ===================================================
   LUPA (zoom) DE LAS IMAGENES DE PRODUCTO
   ===================================================
   Efecto de lente que sigue al cursor y amplia la zona apuntada.

   Uso:
       iniciarLupa({
           img:      'detalle-img-principal',              // id de la <img>
           wrapper:  '.product-detail-img-wrapper',        // marco que la contiene
           selector: null                                  // CSS del <img> (si no hay id)
       });

   - img + wrapper  -> una sola imagen (tienda/detalle.php: imagen principal)
   - selector       -> todas las que coincidan (tienda/home.php: cada tarjeta)

   Detalles que conviene no perder de vista:
   - El aumento se configura con data-lupa-zoom en la etiqueta <img> (2.5 por
     defecto) y el tamano de la lente en assets/css/lupa.css (.product-detail-lupa).
   - Las tarjetas del home recortan su contenido (overflow:hidden), asi que
     mientras la lente esta visible se marca la tarjeta y el marco con
     .lupa-activa, que en lupa.css cambia el recorte a visible. Sin eso la
     lente aparece cortada por el borde de la tarjeta.
   - Solo responde a mouse / lapiz: en pantallas tactiles no interfiere.
   =================================================== */
function iniciarLupa(opciones) {
    var cfg = opciones || {};

    var imagenes;
    if (cfg.img) {
        var una = document.getElementById(cfg.img);
        imagenes = una ? [una] : [];
    } else if (cfg.selector) {
        imagenes = Array.prototype.slice.call(document.querySelectorAll(cfg.selector));
    } else {
        imagenes = [];
    }

    for (var k = 0; k < imagenes.length; k++) {
        iniciarLupaEn(imagenes[k], cfg.wrapper);
    }

    return imagenes.length;
}

function iniciarLupaEn(img, selectorMarco) {
    if (!img || !img.closest) { return; }

    var wrapper = selectorMarco ? img.closest(selectorMarco) : img.parentElement;
    if (!wrapper) { return; }

    // Una sola lente por marco.
    if (wrapper.getAttribute('data-lupa-lista') === '1') { return; }
    wrapper.setAttribute('data-lupa-lista', '1');

    var ZOOM = parseFloat(img.getAttribute('data-lupa-zoom')) || 2.5;

    var lupa = document.createElement('div');
    lupa.className = 'product-detail-lupa';
    lupa.setAttribute('aria-hidden', 'true');
    wrapper.appendChild(lupa);

    // La tarjeta del catalogo (si es que la hay) tambien recorta: se marca junto
    // con el marco para que la lente pueda salir mientras esta visible.
    var tarjeta = wrapper.closest('.product-card');

    /* Tamano de la lente definido en el CSS. Se lee en el primer pintado, no al
       inicializar: leer offsetWidth fuerza recalculo de estilos y con un
       catalogo de muchas tarjetas eso se paga al cargar la pagina. */
    var diametroCSS = -1;
    function diametroBase() {
        if (diametroCSS < 0) {
            diametroCSS = lupa.offsetWidth || 190;
        }
        return diametroCSS;
    }

    // El fondo de la lente debe fundirse con el del marco, que cambia segun el tema.
    var fondoMarco = window.getComputedStyle(wrapper).backgroundColor;
    if (fondoMarco && fondoMarco !== 'transparent' && fondoMarco !== 'rgba(0, 0, 0, 0)') {
        lupa.style.backgroundColor = fondoMarco;
    }

    var srcActual = '';
    var dAplicado = -1;
    var ultimoEvento = null;
    var raf = null;
    var activa = false;

    /* Geometria real de la imagen dibujada dentro del marco: contempla
       object-fit (cover / contain / fill / none) y el recorte que produce. */
    function geometria() {
        var w = wrapper.getBoundingClientRect();
        var r = img.getBoundingClientRect();
        var nw = img.naturalWidth;
        var nh = img.naturalHeight;
        if (!nw || !nh || r.width <= 0 || r.height <= 0 || w.width <= 0) { return null; }

        var fit = (window.getComputedStyle(img).objectFit || 'fill').toLowerCase();
        var escX, escY;
        if (fit === 'none') {
            escX = 1;
            escY = 1;
        } else if (fit === 'fill') {
            escX = r.width / nw;
            escY = r.height / nh;
        } else if (fit === 'contain') {
            escX = escY = Math.min(r.width / nw, r.height / nh);
        } else if (fit === 'scale-down') {
            escX = escY = Math.min(1, Math.min(r.width / nw, r.height / nh));
        } else { // cover (por defecto en los temas)
            escX = escY = Math.max(r.width / nw, r.height / nh);
        }

        var anchoDib = nw * escX;
        var altoDib  = nh * escY;

        return {
            caja: w,
            anchoCaja: r.width,
            altoCaja: r.height,
            anchoDib: anchoDib,
            altoDib: altoDib,
            xImg: r.left - w.left,                                 // esquina de la caja <img>
            yImg: r.top - w.top,
            x0: (r.left - w.left) + (r.width - anchoDib) / 2,      // esquina de la imagen dibujada
            y0: (r.top - w.top) + (r.height - altoDib) / 2
        };
    }

    function sincronizarFondo() {
        // currentSrc cambia al pasar de una miniatura a otra (home y detalle).
        var s = img.currentSrc || img.src;
        if (s && s !== srcActual) {
            srcActual = s;
            lupa.style.backgroundImage = 'url("' + s.replace(/"/g, '%22') + '")';
        }
    }

    function pintar() {
        raf = null;
        if (!activa || !ultimoEvento) { return; }

        var g = geometria();
        if (!g) { ocultar(); return; }

        // La lente nunca es mas grande que la imagen.
        var d = Math.min(diametroBase(), g.anchoCaja, g.altoCaja);
        if (d !== dAplicado) {
            lupa.style.width  = d + 'px';
            lupa.style.height = d + 'px';
            dAplicado = d;
        }

        var mx = ultimoEvento.clientX - g.caja.left;
        var my = ultimoEvento.clientY - g.caja.top;

        var ix = mx - g.xImg;   // cursor dentro de la caja <img>
        var iy = my - g.yImg;
        if (ix < 0 || iy < 0 || ix > g.anchoCaja || iy > g.altoCaja) { ocultar(); return; }

        // La lente se mantiene dentro de la imagen (centrada en el cursor cuando cabe).
        var lx = g.xImg + Math.min(Math.max(ix - d / 2, 0), Math.max(0, g.anchoCaja - d));
        var ly = g.yImg + Math.min(Math.max(iy - d / 2, 0), Math.max(0, g.altoCaja - d));

        sincronizarFondo();

        // El punto apuntado por el cursor se ve ampliado en esa misma posicion
        // dentro de la lente, asi que la lupa se siente "pegada" a la imagen.
        var bx = (mx - lx) - (mx - g.x0) * ZOOM;
        var by = (my - ly) - (my - g.y0) * ZOOM;

        lupa.style.transform          = 'translate(' + lx + 'px,' + ly + 'px)';
        lupa.style.backgroundSize     = (g.anchoDib * ZOOM) + 'px ' + (g.altoDib * ZOOM) + 'px';
        lupa.style.backgroundPosition = bx + 'px ' + by + 'px';
    }

    function mostrar() {
        if (activa) { return; }
        activa = true;
        sincronizarFondo();
        lupa.classList.add('activa');
        wrapper.classList.add('lupa-activa');
        if (tarjeta) { tarjeta.classList.add('lupa-activa'); }
    }

    function ocultar() {
        if (raf) { cancelAnimationFrame(raf); raf = null; }
        ultimoEvento = null;
        if (!activa) { return; }
        activa = false;
        lupa.classList.remove('activa');
        wrapper.classList.remove('lupa-activa');
        if (tarjeta) { tarjeta.classList.remove('lupa-activa'); }
    }

    // Pointer events permiten ignorar el dedo y responder solo al mouse / lapiz
    // (util en laptops con pantalla tactil, donde el hover igual existe).
    var usaPointer = 'onpointerdown' in window;
    var evEntra = usaPointer ? 'pointerenter' : 'mouseenter';
    var evMueve = usaPointer ? 'pointermove'  : 'mousemove';
    var evSale  = usaPointer ? 'pointerleave' : 'mouseleave';

    function esMouse(e) {
        if (!usaPointer) { return true; }
        return e.pointerType === 'mouse' || e.pointerType === 'pen' || e.pointerType === '';
    }

    wrapper.addEventListener(evEntra, function (e) {
        if (!esMouse(e)) { return; }
        mostrar();
        ultimoEvento = e;
        if (!raf) { raf = requestAnimationFrame(pintar); }
    });

    wrapper.addEventListener(evMueve, function (e) {
        if (!esMouse(e)) { return; }
        ultimoEvento = e;
        mostrar();
        if (!raf) { raf = requestAnimationFrame(pintar); }
    });

    wrapper.addEventListener(evSale, function (e) {
        if (!esMouse(e)) { return; }
        ocultar();
    });

    // Al cambiar de imagen (miniaturas, o respaldo por onerror) se recalcula todo.
    img.addEventListener('load', function () {
        srcActual = '';
        dAplicado = -1;
        sincronizarFondo();
        if (activa && ultimoEvento && !raf) { raf = requestAnimationFrame(pintar); }
    });

    window.addEventListener('resize', function () {
        dAplicado = -1;
        ocultar();
    });

    // Si se hace scroll con el cursor sobre la imagen, la lente sigue alineada.
    window.addEventListener('scroll', function () {
        if (activa && ultimoEvento && !raf) { raf = requestAnimationFrame(pintar); }
    }, true);
}
