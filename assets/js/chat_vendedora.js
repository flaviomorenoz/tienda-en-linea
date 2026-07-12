/**
 * chat_vendedora.js - Tienda Virtual
 * Panel de la vendedora: cola de espera, conversaciones asignadas
 * y ventana de chat, todo por polling (sin websockets).
 */
(function () {
    var urls = window.VENDEDORA_URLS;
    if (!urls) return;

    var listaEspera   = document.getElementById('lista-espera');
    var listaMias     = document.getElementById('lista-mias');
    var badgeEspera   = document.getElementById('badge-espera');
    var mensajesEl    = document.getElementById('chats-mensajes');
    var formEl        = document.getElementById('chats-form');
    var formResponder = document.getElementById('form-responder');
    var inputRespuesta = document.getElementById('input-respuesta');
    var btnCerrar     = document.getElementById('btn-cerrar-chat');
    var switchDisponible = document.getElementById('switch-disponible');
    var labelDisponible  = document.getElementById('label-disponible');

    var conversacionActual = null;
    var ultimoMsgId = 0;

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str == null ? '' : str));
        return d.innerHTML;
    }

    function post(url, params) {
        var formData = new FormData();
        for (var k in params) { formData.append(k, params[k]); }
        return fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData })
            .then(function (r) { return r.json(); });
    }

    function get(url) {
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); });
    }

    function cargarEspera() {
        get(urls.enEspera).then(function (data) {
            var rows = (data && data.data) || [];
            badgeEspera.classList.toggle('d-none', rows.length === 0);
            badgeEspera.textContent = rows.length;

            if (rows.length === 0) {
                listaEspera.innerHTML = '<p class="text-muted small">No hay clientes esperando.</p>';
                return;
            }

            listaEspera.innerHTML = rows.map(function (c) {
                return '<div class="conv-item" data-tomar="' + c.id + '">' +
                    '<div class="nombre">' + escapeHtml(c.nombre_cliente || 'Cliente sin nombre') + '</div>' +
                    '<div class="motivo">' + escapeHtml(c.motivo || 'Solicitó hablar con una vendedora') + '</div>' +
                    '<button type="button" class="btn btn-sm btn-dark mt-2 w-100">Tomar chat</button>' +
                    '</div>';
            }).join('');

            listaEspera.querySelectorAll('[data-tomar]').forEach(function (el) {
                el.addEventListener('click', function () {
                    var id = this.getAttribute('data-tomar');
                    post(urls.tomar + id, {}).then(function (resp) {
                        if (resp && resp.ok) {
                            cargarEspera();
                            cargarMias();
                            abrirConversacion(id);
                        }
                    });
                });
            });
        });
    }

    function cargarMias() {
        get(urls.misConversaciones).then(function (data) {
            var rows = (data && data.data) || [];

            if (rows.length === 0) {
                listaMias.innerHTML = '<p class="text-muted small">Aún no tienes chats asignados.</p>';
                return;
            }

            listaMias.innerHTML = rows.map(function (c) {
                var activo = conversacionActual === String(c.id) ? ' activo' : '';
                return '<div class="conv-item' + activo + '" data-abrir="' + c.id + '">' +
                    '<div class="nombre">' + escapeHtml(c.nombre_cliente || 'Cliente sin nombre') + '</div>' +
                    '</div>';
            }).join('');

            listaMias.querySelectorAll('[data-abrir]').forEach(function (el) {
                el.addEventListener('click', function () {
                    abrirConversacion(this.getAttribute('data-abrir'));
                });
            });
        });
    }

    function pintarMensaje(m) {
        var div = document.createElement('div');
        div.className = 'msg msg-' + m.emisor;
        div.textContent = m.texto;
        mensajesEl.appendChild(div);
    }

    function abrirConversacion(id) {
        conversacionActual = String(id);
        ultimoMsgId = 0;
        mensajesEl.innerHTML = '';
        formEl.style.display = 'block';

        get(urls.mensajes + id).then(function (data) {
            if (!data || !data.ok) return;
            data.mensajes.forEach(function (m) {
                pintarMensaje(m);
                ultimoMsgId = Math.max(ultimoMsgId, m.id);
            });
            mensajesEl.scrollTop = mensajesEl.scrollHeight;

            if (data.estado === 'cerrada') {
                formEl.style.display = 'none';
            }
        });

        cargarMias();
    }

    function refrescarConversacionAbierta() {
        if (!conversacionActual) return;

        get(urls.mensajes + conversacionActual).then(function (data) {
            if (!data || !data.ok) return;

            data.mensajes.forEach(function (m) {
                if (m.id > ultimoMsgId) {
                    pintarMensaje(m);
                    ultimoMsgId = m.id;
                }
            });
            mensajesEl.scrollTop = mensajesEl.scrollHeight;

            if (data.estado === 'cerrada') {
                formEl.style.display = 'none';
            }
        });
    }

    formResponder.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!conversacionActual) return;

        var mensaje = inputRespuesta.value.trim();
        if (mensaje === '') return;

        inputRespuesta.value = '';
        post(urls.responder + conversacionActual, { mensaje: mensaje }).then(function (resp) {
            if (resp && resp.ok) {
                refrescarConversacionAbierta();
            }
        });
    });

    btnCerrar.addEventListener('click', function () {
        if (!conversacionActual) return;
        if (!confirm('¿Cerrar esta conversación?')) return;

        post(urls.cerrar + conversacionActual, {}).then(function (resp) {
            if (resp && resp.ok) {
                formEl.style.display = 'none';
                mensajesEl.innerHTML = '<p class="vacio">Selecciona un cliente de la lista para ver la conversación.</p>';
                conversacionActual = null;
                cargarMias();
            }
        });
    });

    switchDisponible.addEventListener('change', function () {
        var estado = this.checked ? 'disponible' : 'ocupada';
        labelDisponible.textContent = this.checked ? 'Disponible' : 'Ocupada';
        post(urls.disponibilidad, { estado: estado });
    });

    cargarEspera();
    cargarMias();
    setInterval(cargarEspera, 5000);
    setInterval(cargarMias, 8000);
    setInterval(refrescarConversacionAbierta, 3000);
})();
