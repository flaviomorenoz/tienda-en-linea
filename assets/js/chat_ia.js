/**
 * chat_ia.js - Tienda Virtual
 * Widget flotante de chat: asistente IA (DeepSeek) con posibilidad
 * de transferir la conversación a una vendedora en vivo (polling).
 */
(function () {
    var HISTORIAL_KEY = 'chat_ia_historial';
    var TOKEN_KEY      = 'chat_ia_token';
    var MAX_HISTORIAL  = 20;
    var POLL_MS        = 4000;

    var widget         = document.getElementById('chat-ia-widget');
    var toggleBtn       = document.getElementById('chat-ia-toggle');
    var cerrarBtn       = document.getElementById('chat-ia-cerrar');
    var panel           = document.getElementById('chat-ia-panel');
    var mensajesEl      = document.getElementById('chat-ia-mensajes');
    var escribiendoEl   = document.getElementById('chat-ia-escribiendo');
    var estadoEl        = document.getElementById('chat-ia-estado');
    var btnTransferir   = document.getElementById('chat-ia-transferir');
    var btnNueva        = document.getElementById('chat-ia-nueva');
    var form            = document.getElementById('chat-ia-form');
    var input           = document.getElementById('chat-ia-input');

    if (!widget || !window.CHAT_IA_URL) return;

    var estadoActual        = 'ia';
    var nombreVendedoraActual = null;
    var ultimoMsgId          = 0;
    var pollTimer            = null;

    function generarToken() {
        if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function obtenerToken() {
        var token = sessionStorage.getItem(TOKEN_KEY);
        if (!token) {
            token = generarToken();
            sessionStorage.setItem(TOKEN_KEY, token);
        }
        return token;
    }

    var token = obtenerToken();

    function cargarHistorial() {
        try {
            return JSON.parse(sessionStorage.getItem(HISTORIAL_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function guardarHistorial(historial) {
        sessionStorage.setItem(HISTORIAL_KEY, JSON.stringify(historial.slice(-MAX_HISTORIAL)));
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str == null ? '' : str));
        return d.innerHTML;
    }

    var CLASE_POR_EMISOR = { user: 'user', assistant: 'bot', cliente: 'user', ia: 'bot', vendedor: 'vendedor', sistema: 'sistema' };

    function pintarMensaje(emisor, texto, imagenUrl) {
        var clase = CLASE_POR_EMISOR[emisor] || 'bot';
        var burbuja = document.createElement('div');
        burbuja.className = 'chat-ia-msg chat-ia-msg-' + clase;

        var html = '';
        if (imagenUrl) {
            html += '<img src="' + escapeHtml(imagenUrl) + '" class="chat-ia-img" alt="Imagen enviada">';
        }
        if (texto) {
            html += escapeHtml(texto).replace(/\n/g, '<br>');
        }
        burbuja.innerHTML = html;

        mensajesEl.appendChild(burbuja);
        mensajesEl.scrollTop = mensajesEl.scrollHeight;
    }

    function pintarHistorialLocal() {
        var historial = cargarHistorial();
        if (historial.length === 0) {
            pintarMensaje('assistant', '¡Hola! Soy el asistente de la tienda. Pregúntame por tallas, colores, precios o categorías de nuestras prendas.');
            return;
        }
        historial.forEach(function (h) {
            pintarMensaje(h.role, h.content);
        });
    }

    function mostrarEstado(texto) {
        if (!texto) {
            estadoEl.classList.add('d-none');
            return;
        }
        estadoEl.textContent = texto;
        estadoEl.classList.remove('d-none');
    }

    function actualizarModoUI() {
        if (estadoActual === 'ia') {
            btnTransferir.classList.remove('d-none');
            btnNueva.classList.add('d-none');
            input.disabled = false;
            mostrarEstado('');
        } else if (estadoActual === 'en_espera') {
            btnTransferir.classList.add('d-none');
            btnNueva.classList.add('d-none');
            input.disabled = false;
            mostrarEstado('Buscando una vendedora disponible...');
        } else if (estadoActual === 'vendedor') {
            btnTransferir.classList.add('d-none');
            btnNueva.classList.add('d-none');
            input.disabled = false;
            mostrarEstado('Hablando con ' + (nombreVendedoraActual || 'una vendedora'));
        } else if (estadoActual === 'cerrada') {
            btnTransferir.classList.add('d-none');
            btnNueva.classList.remove('d-none');
            input.disabled = true;
            mostrarEstado('La conversación fue cerrada.');
            detenerPolling();
        }
    }

    function detenerPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function iniciarPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(function () { actualizarDesdeServidor(false); }, POLL_MS);
    }

    function actualizarDesdeServidor(reset) {
        var url = window.CHAT_ESTADO_URL + '?token=' + encodeURIComponent(token) + '&desde=' + (reset ? 0 : ultimoMsgId);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) return;

                if (reset) {
                    mensajesEl.innerHTML = '';
                    ultimoMsgId = 0;
                }

                estadoActual = data.estado;
                nombreVendedoraActual = data.vendedor_nombre;

                data.mensajes.forEach(function (m) {
                    pintarMensaje(m.emisor, m.texto, m.imagen);
                    ultimoMsgId = Math.max(ultimoMsgId, m.id);
                });

                actualizarModoUI();

                if (estadoActual !== 'ia' && estadoActual !== 'cerrada') {
                    iniciarPolling();
                }
            });
    }

    function iniciar() {
        var url = window.CHAT_ESTADO_URL + '?token=' + encodeURIComponent(token) + '&desde=0';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok || data.estado === 'ia') {
                    pintarHistorialLocal();
                    actualizarModoUI();
                    return;
                }

                actualizarDesdeServidor(true);
            })
            .catch(function () {
                pintarHistorialLocal();
                actualizarModoUI();
            });
    }

    function abrirPanel() {
        panel.classList.remove('d-none');
        toggleBtn.classList.add('d-none');
        input.focus();
    }

    function cerrarPanel() {
        panel.classList.add('d-none');
        toggleBtn.classList.remove('d-none');
    }

    toggleBtn.addEventListener('click', abrirPanel);
    cerrarBtn.addEventListener('click', cerrarPanel);

    btnTransferir.addEventListener('click', function () {
        btnTransferir.disabled = true;

        var formData = new FormData();
        formData.append('token', token);

        fetch(window.CHAT_SOLICITAR_URL, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function () { actualizarDesdeServidor(true); })
        .finally(function () { btnTransferir.disabled = false; });
    });

    btnNueva.addEventListener('click', function () {
        detenerPolling();
        sessionStorage.removeItem(HISTORIAL_KEY);
        sessionStorage.removeItem(TOKEN_KEY);
        token = obtenerToken();
        estadoActual = 'ia';
        nombreVendedoraActual = null;
        ultimoMsgId = 0;
        mensajesEl.innerHTML = '';
        pintarHistorialLocal();
        actualizarModoUI();
    });

    function subirImagen(archivo) {
        if (estadoActual !== 'en_espera' && estadoActual !== 'vendedor') {
            pintarMensaje('sistema', 'Para enviar imágenes primero pulsa "Hablar con una vendedora".');
            return;
        }

        var formData = new FormData();
        formData.append('token', token);
        formData.append('imagen', archivo);

        fetch(window.CHAT_SUBIR_IMAGEN_URL, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.ok) {
                pintarMensaje('cliente', '', data.url);
                ultimoMsgId = Math.max(ultimoMsgId, data.id || 0);
            } else {
                pintarMensaje('sistema', (data && data.error) || 'No se pudo enviar la imagen.');
            }
        })
        .catch(function () {
            pintarMensaje('sistema', 'No se pudo enviar la imagen.');
        });
    }

    input.addEventListener('paste', function (e) {
        var items = (e.clipboardData && e.clipboardData.items) || [];
        for (var i = 0; i < items.length; i++) {
            if (items[i].type && items[i].type.indexOf('image/') === 0) {
                e.preventDefault();
                subirImagen(items[i].getAsFile());
                return;
            }
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var mensaje = input.value.trim();
        if (mensaje === '') return;

        if (estadoActual === 'ia') {
            var historial = cargarHistorial();

            pintarMensaje('user', mensaje);
            input.value = '';
            input.disabled = true;
            escribiendoEl.classList.remove('d-none');

            var formData = new FormData();
            formData.append('token', token);
            formData.append('mensaje', mensaje);
            formData.append('historial', JSON.stringify(historial));

            fetch(window.CHAT_IA_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var respuesta = (data && data.ok) ? data.respuesta : (data && data.error) || 'Ocurrió un error, intenta nuevamente.';
                pintarMensaje('assistant', respuesta);

                if (data && data.ok) {
                    historial.push({ role: 'user', content: mensaje });
                    historial.push({ role: 'assistant', content: respuesta });
                    guardarHistorial(historial);
                }
            })
            .catch(function () {
                pintarMensaje('assistant', 'No se pudo conectar con el asistente. Intenta nuevamente.');
            })
            .finally(function () {
                input.disabled = false;
                input.focus();
                escribiendoEl.classList.add('d-none');
            });
        } else if (estadoActual === 'en_espera' || estadoActual === 'vendedor') {
            pintarMensaje('cliente', mensaje);
            input.value = '';

            var formData2 = new FormData();
            formData2.append('token', token);
            formData2.append('mensaje', mensaje);

            fetch(window.CHAT_ENVIAR_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData2
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    pintarMensaje('sistema', (data && data.error) || 'No se pudo enviar el mensaje.');
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', iniciar);
    if (document.readyState !== 'loading') iniciar();
})();
