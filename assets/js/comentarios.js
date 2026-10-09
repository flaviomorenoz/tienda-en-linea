/* =============================================================================
 * COMENTARIOS DE PRODUCTOS  -  assets/js/comentarios.js
 * -----------------------------------------------------------------------------
 * Implementa mostrar_popup_comentario(), la funcion que ya llama el boton de
 * las tarjetas del catalogo (application/views/tienda/home.php):
 *
 *     <a href="#" onclick="mostrar_popup_comentario()">Comenta</a>
 *
 * Abre una ventana emergente para escribir un comentario simple sobre el
 * producto y lo guarda en la tabla tec_comentarios (id_producto, comentario,
 * fecha) a traves de Comentarios::guardar  ->  POST /comentarios/guardar.
 *
 * Es JavaScript puro (sin jQuery) porque el catalogo usa layouts/footer2.php,
 * que no carga jQuery. Se incluye desde layouts/footer2.php y
 * layouts/footer.php para que este disponible en el catalogo y en la ficha.
 *
 * Uso:
 *     mostrar_popup_comentario()      -> toma el producto de la tarjeta pulsada
 *     mostrar_popup_comentario(123)   -> producto indicado explicitamente
 * ========================================================================== */
(function () {
    'use strict';

    var MAX_LARGO = 1000;

    var popup    = null;   // nodo raiz del popup (se crea la primera vez)
    var idProd   = 0;      // producto del comentario en curso
    var enviando = false;
    var elementoClic = null;  // boton "Comenta" pulsado (lo guarda el listener de captura)

    /* ------------------------------------------------------------------ */
    /* Utilidades                                                          */
    /* ------------------------------------------------------------------ */

    function urlGuardar() {
        if (window.COMENTARIOS_URL) { return window.COMENTARIOS_URL; }

        // Respaldo: se deduce de la ruta de este mismo script
        var scripts = document.getElementsByTagName('script');
        for (var i = 0; i < scripts.length; i++) {
            var src = scripts[i].src || '';
            if (src.indexOf('comentarios.js') !== -1) {
                return src.replace(/assets\/js\/comentarios\.js.*$/, '') + 'comentarios/guardar';
            }
        }

        return 'comentarios/guardar';
    }

    // El token CSRF esta en la cookie csrf_cookie (y en los formularios de la
    // pagina). csrf_regenerate = TRUE: cambia en cada POST, por eso se lee en
    // cada envio y se refresca con el token que devuelve el servidor.
    function tokenCsrf() {
        var m = document.cookie.match(/(?:^|;\s*)csrf_cookie=([^;]*)/);
        if (m && m[1]) { return decodeURIComponent(m[1]); }

        var input = document.querySelector('input[name="csrf_token"]');
        return (input && input.value) ? input.value : '';
    }

    function refrescarToken(token) {
        if (!token) { return; }
        var inputs = document.querySelectorAll('input[name="csrf_token"]');
        for (var i = 0; i < inputs.length; i++) { inputs[i].value = token; }
    }

    // Producto: el del argumento o, si no se paso ninguno, la tarjeta del boton
    // "Comenta" pulsado (elementoClic lo guarda el listener de captura; si no,
    // se usa window.event, que es lo que hay dentro de un onclick en linea).
    function productoDelClic() {
        var el = elementoClic;
        if (!el) {
            var ev = window.event;
            el = ev ? (ev.currentTarget || ev.target) : null;
        }

        var tarjeta = (el && el.closest) ? el.closest('.product-card') : null;

        if (!tarjeta) { return { id: 0, nombre: '' }; }

        var id = 0;
        var m;

        // 1) El bloque de descripcion de cada tarjeta se llama div-descrip_<id>
        var desc = tarjeta.querySelector('[id^="div-descrip_"]');
        if (desc) {
            m = /div-descrip_(\d+)/.exec(desc.id || '');
            if (m) { id = parseInt(m[1], 10); }
        }

        // 2) Cualquier enlace de la tarjeta a la ficha del producto
        if (!id) {
            var enlace = tarjeta.querySelector('a[href*="/tienda/producto/"]');
            if (enlace) {
                m = /\/tienda\/producto\/(\d+)/.exec(enlace.getAttribute('href') || '');
                if (m) { id = parseInt(m[1], 10); }
            }
        }

        var nom = tarjeta.querySelector('.product-name-link');

        return {
            id: id,
            nombre: nom ? (nom.textContent || '').replace(/\s+/g, ' ').trim() : ''
        };
    }

    /* ------------------------------------------------------------------ */
    /* Popup                                                               */
    /* ------------------------------------------------------------------ */

    function inyectarEstilos() {
        if (document.getElementById('pc-estilos')) { return; }

        var css =
            '#popup-comentario{position:fixed;top:0;left:0;right:0;bottom:0;z-index:30000;' +
                'background:rgba(0,0,0,.5);padding:16px;overflow-y:auto;display:none;}' +
            '#popup-comentario.pc-abierto{display:block;}' +
            '#popup-comentario .pc-caja{background:#fff;border-radius:12px;width:100%;max-width:540px;' +
                'margin:7vh auto 24px;box-shadow:0 12px 40px rgba(0,0,0,.35);overflow:hidden;}' +
            '#popup-comentario .pc-cabecera{display:flex;align-items:center;justify-content:space-between;' +
                'padding:14px 18px;border-bottom:1px solid #ececec;}' +
            '#popup-comentario .pc-titulo{margin:0;font-size:1.05rem;font-weight:700;}' +
            '#popup-comentario .pc-cerrar{border:0;background:transparent;font-size:1.7rem;line-height:1;' +
                'cursor:pointer;color:#777;padding:0 4px;}' +
            '#popup-comentario .pc-cerrar:hover{color:#111;}' +
            '#popup-comentario .pc-cuerpo{padding:18px;}' +
            '#popup-comentario .pc-producto{margin:0 0 10px;color:#777;font-size:.9rem;}' +
            '#popup-comentario .pc-producto strong{color:#333;}' +
            '#popup-comentario .pc-label{display:block;font-weight:600;margin-bottom:6px;font-size:.95rem;}' +
            '#popup-comentario .pc-texto{display:block;width:100%;box-sizing:border-box;border:1px solid #ccc;' +
                'border-radius:8px;padding:10px;font-family:inherit;font-size:1rem;resize:vertical;}' +
            '#popup-comentario .pc-texto:focus{outline:0;border-color:#343a40;}' +
            '#popup-comentario .pc-info{display:flex;align-items:center;gap:8px;margin-top:4px;}' +
            '#popup-comentario .pc-error{color:#c0392b;display:none;}' +
            '#popup-comentario .pc-contador{color:#999;margin-left:auto;}' +
            '#popup-comentario .pc-ok{display:none;margin-top:12px;background:#e8f6ec;border:1px solid #b7e0c4;' +
                'color:#1d6f42;border-radius:8px;padding:8px 10px;font-size:.9rem;}' +
            '#popup-comentario .pc-botones{display:flex;justify-content:flex-end;gap:8px;padding:14px 18px;' +
                'border-top:1px solid #ececec;background:#fafafa;}' +
            '#popup-comentario .pc-botones button{border-radius:8px;padding:8px 14px;font-size:.95rem;' +
                'cursor:pointer;font-family:inherit;}' +
            '#popup-comentario .pc-cancelar{background:#fff;border:1px solid #ccc;color:#333;}' +
            '#popup-comentario .pc-enviar{background:#343a40;border:1px solid #343a40;color:#fff;}' +
            '#popup-comentario .pc-enviar[disabled]{opacity:.6;cursor:default;}' +
            '@media (max-width:576px){#popup-comentario .pc-caja{margin-top:3vh;}}';

        var estilo = document.createElement('style');
        estilo.id = 'pc-estilos';
        estilo.type = 'text/css';
        estilo.appendChild(document.createTextNode(css));
        document.head.appendChild(estilo);
    }

    function crearPopup() {
        if (popup) { return popup; }

        inyectarEstilos();

        popup = document.createElement('div');
        popup.id = 'popup-comentario';
        popup.setAttribute('role', 'dialog');
        popup.setAttribute('aria-modal', 'true');
        popup.setAttribute('aria-labelledby', 'pc-titulo');
        popup.innerHTML =
            '<div class="pc-caja">' +
                '<div class="pc-cabecera">' +
                    '<h6 class="pc-titulo" id="pc-titulo">Deja tu comentario</h6>' +
                    '<button type="button" class="pc-cerrar" aria-label="Cerrar">&times;</button>' +
                '</div>' +
                '<form class="pc-form" novalidate>' +
                    '<div class="pc-cuerpo">' +
                        '<p class="pc-producto"></p>' +
                        '<label class="pc-label" for="pc-texto">Tu comentario</label>' +
                        '<textarea id="pc-texto" class="pc-texto" rows="4" maxlength="' + MAX_LARGO + '" ' +
                            'placeholder="Escribe aquí tu opinión sobre el producto..."></textarea>' +
                        '<div class="pc-info">' +
                            '<small class="pc-error"></small>' +
                            '<small class="pc-contador">0/' + MAX_LARGO + '</small>' +
                        '</div>' +
                        '<div class="pc-ok"></div>' +
                    '</div>' +
                    '<div class="pc-botones">' +
                        '<button type="button" class="pc-cancelar">Cancelar</button>' +
                        '<button type="submit" class="pc-enviar">Publicar comentario</button>' +
                    '</div>' +
                '</form>' +
            '</div>';

        document.body.appendChild(popup);

        popup.querySelector('.pc-cerrar').addEventListener('click', cerrar);
        popup.querySelector('.pc-cancelar').addEventListener('click', cerrar);
        popup.querySelector('.pc-form').addEventListener('submit', enviar);
        popup.querySelector('.pc-texto').addEventListener('input', function () {
            popup.querySelector('.pc-contador').textContent = this.value.length + '/' + MAX_LARGO;
        });

        // Clic fuera de la caja = cerrar
        popup.addEventListener('click', function (e) {
            if (e.target === popup) { cerrar(); }
        });

        return popup;
    }

    function limpiarMensajes() {
        popup.querySelector('.pc-error').style.display = 'none';
        popup.querySelector('.pc-error').textContent = '';
        popup.querySelector('.pc-ok').style.display = 'none';
        popup.querySelector('.pc-ok').textContent = '';
    }

    function ponerError(msg) {
        popup.querySelector('.pc-ok').style.display = 'none';
        var el = popup.querySelector('.pc-error');
        el.textContent = msg;
        el.style.display = 'inline';
    }

    function ponerOk(msg) {
        popup.querySelector('.pc-error').style.display = 'none';
        var el = popup.querySelector('.pc-ok');
        el.textContent = msg;
        el.style.display = 'block';
    }

    function bloquearBoton(bloquear) {
        var btn = popup.querySelector('.pc-enviar');
        btn.disabled = !!bloquear;
        btn.textContent = bloquear ? 'Enviando...' : 'Publicar comentario';
    }

    function abrir() {
        crearPopup();
        limpiarMensajes();
        bloquearBoton(false);
        popup.classList.add('pc-abierto');
        popup.scrollTop = 0;

        setTimeout(function () {
            var area = popup.querySelector('.pc-texto');
            if (area.focus) { area.focus(); }
        }, 50);
    }

    function cerrar() {
        if (!popup) { return; }
        popup.classList.remove('pc-abierto');
    }

    function enviar(e) {
        e.preventDefault();

        if (enviando) { return; }

        var area  = popup.querySelector('.pc-texto');
        var texto = (area.value || '').replace(/^\s+|\s+$/g, '');

        if (texto === '') {
            ponerError('Escribe tu comentario antes de enviarlo.');
            area.focus();
            return;
        }

        if (texto.length > MAX_LARGO) {
            ponerError('El comentario no puede pasar de ' + MAX_LARGO + ' caracteres.');
            return;
        }

        if (!idProd) {
            ponerError('No se pudo identificar el producto. Recarga la página e inténtalo otra vez.');
            return;
        }

        if (!window.fetch) {
            ponerError('Tu navegador no permite enviar el comentario. Actualízalo e inténtalo otra vez.');
            return;
        }

        enviando = true;
        bloquearBoton(true);
        limpiarMensajes();

        var cuerpo = 'id_producto=' + encodeURIComponent(idProd) +
                     '&comentario=' + encodeURIComponent(texto);
        var token = tokenCsrf();
        if (token) { cuerpo += '&csrf_token=' + encodeURIComponent(token); }

        fetch(urlGuardar(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: cuerpo
        })
        .then(function (resp) {
            return resp.text().then(function (txt) {
                var datos = null;
                try { datos = JSON.parse(txt); } catch (err) { datos = null; }
                return { status: resp.status, datos: datos };
            });
        })
        .then(function (res) {
            enviando = false;
            bloquearBoton(false);

            var datos = res.datos || {};

            // csrf_regenerate = TRUE: hay que quedarse con el token nuevo
            refrescarToken(datos.csrf_token);

            if (res.status !== 200 || datos.ok !== true) {
                ponerError(datos.error || 'No se pudo guardar el comentario. Inténtalo nuevamente.');
                return;
            }

            area.value = '';
            popup.querySelector('.pc-contador').textContent = '0/' + MAX_LARGO;
            ponerOk(datos.mensaje || '¡Gracias! Tu comentario fue publicado.');
            setTimeout(cerrar, 1600);
        })
        .catch(function () {
            enviando = false;
            bloquearBoton(false);
            ponerError('No se pudo conectar con el servidor. Revisa tu conexión e inténtalo nuevamente.');
        });
    }

    /* ------------------------------------------------------------------ */
    /* Funcion publica                                                     */
    /* ------------------------------------------------------------------ */

    function mostrar_popup_comentario(idProducto) {
        var ev = window.event;

        // El boton es un enlace a "#": se evita el salto de pagina
        if (ev && ev.preventDefault) { ev.preventDefault(); }

        var datos = (idProducto !== undefined && idProducto !== null && idProducto !== '')
            ? { id: parseInt(idProducto, 10) || 0, nombre: '' }
            : productoDelClic();

        idProd = datos.id;

        crearPopup();

        var info = popup.querySelector('.pc-producto');
        if (idProd && datos.nombre) {
            info.innerHTML = 'Producto: <strong>' + datos.nombre
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</strong>';
        } else {
            info.textContent = '';
        }

        abrir();
    }

    // Cerrar con Escape
    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.keyCode === 27) && popup && popup.classList.contains('pc-abierto')) {
            cerrar();
        }
    });

    // El boton "Comenta" es un enlace a "#": en la fase de captura (antes del
    // onclick del propio enlace) se guarda el boton pulsado y se evita el salto
    // de pagina. Asi la funcion no depende de window.event.
    document.addEventListener('click', function (e) {
        var t = e.target;
        var boton = (t && t.closest) ? t.closest('[onclick*="mostrar_popup_comentario"]') : null;
        if (!boton) { return; }

        elementoClic = boton;
        if (e.preventDefault) { e.preventDefault(); }
    }, true);

    // El onclick del boton la busca en el ambito global
    window.mostrar_popup_comentario = mostrar_popup_comentario;
})();
