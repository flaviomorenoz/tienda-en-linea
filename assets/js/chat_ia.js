/**
 * chat_ia.js - Tienda Virtual
 * Widget flotante de chat con el asistente IA (DeepSeek).
 */

(function () {
    var HISTORIAL_KEY = 'chat_ia_historial';
    var MAX_HISTORIAL  = 20;

    var widget      = document.getElementById('chat-ia-widget');
    var toggleBtn    = document.getElementById('chat-ia-toggle');
    var cerrarBtn    = document.getElementById('chat-ia-cerrar');
    var panel        = document.getElementById('chat-ia-panel');
    var mensajesEl   = document.getElementById('chat-ia-mensajes');
    var escribiendoEl = document.getElementById('chat-ia-escribiendo');
    var form         = document.getElementById('chat-ia-form');
    var input        = document.getElementById('chat-ia-input');

    if (!widget || !window.CHAT_IA_URL) return;

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
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    function pintarMensaje(role, texto) {
        var burbuja = document.createElement('div');
        burbuja.className = 'chat-ia-msg chat-ia-msg-' + (role === 'user' ? 'user' : 'bot');
        burbuja.innerHTML = escapeHtml(texto).replace(/\n/g, '<br>');
        mensajesEl.appendChild(burbuja);
        mensajesEl.scrollTop = mensajesEl.scrollHeight;
    }

    function pintarHistorial() {
        var historial = cargarHistorial();
        if (historial.length === 0) {
            pintarMensaje('assistant', '¡Hola! Soy el asistente de la tienda. Pregúntame por tallas, colores, precios o categorías de nuestras prendas.');
            return;
        }
        historial.forEach(function (h) {
            pintarMensaje(h.role, h.content);
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

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var mensaje = input.value.trim();
        if (mensaje === '') return;

        var historial = cargarHistorial();

        pintarMensaje('user', mensaje);
        input.value = '';
        input.disabled = true;
        escribiendoEl.classList.remove('d-none');

        var formData = new FormData();
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
    });

    document.addEventListener('DOMContentLoaded', pintarHistorial);
    if (document.readyState !== 'loading') pintarHistorial();
})();
