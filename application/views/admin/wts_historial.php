<style>
    /* ===== Chat estilo WhatsApp ===== */
    .chats-mensajes {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
        max-height: 60vh;
        background: #e5ddd5;
        background-image: linear-gradient(rgba(255,255,255,.2) 1px, transparent 1px),
                          linear-gradient(90deg, rgba(255,255,255,.2) 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .chat-fila {
        display: flex;
        margin-bottom: .6rem;
    }
    .chat-fila.recibido { justify-content: flex-start; }
    .chat-fila.enviado   { justify-content: flex-end; }
    .burbuja {
        position: relative;
        max-width: 75%;
        padding: .5rem .8rem 1.4rem;
        border-radius: 12px;
        font-size: .9rem;
        white-space: pre-wrap;
        word-break: break-word;
        box-shadow: 0 1px 1px rgba(0,0,0,.13);
        line-height: 1.4;
    }
    .burbuja.recibido {
        background: #ffffff;
        color: #111;
        border-top-left-radius: 2px;
    }
    .burbuja.enviado {
        background: #dcf8c6;
        color: #111;
        border-top-right-radius: 2px;
    }
    .burbuja .hora {
        position: absolute;
        bottom: .25rem;
        right: .6rem;
        font-size: .68rem;
        color: #999;
        display: flex;
        align-items: center;
        gap: .25rem;
    }
    .burbuja.enviado .hora { color: #6b8f5e; }
    .burbuja .hora .bi-check2-all { color: #53bdeb; font-size: .8rem; }
    #tabla-whatsapp {
        --bs-body-font-size: 0.9rem;
        font-size: 0.9rem;
    }
</style>

<!-- Contenido principal -->
<div class="main-content">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table id="tabla-whatsapp" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Id</th>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Nombre</th>
                        <th class="text-center">Msjes</th>
                        <th class="">Ultimo Msje</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Opciones</th>
                        <!--<th class="">Nro.Propio</th>-->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal conversación estilo chat -->
<div class="modal fade" id="modalTranscripcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalTranscripcionLabel">
                    <i class="bi bi-whatsapp text-success me-1"></i> Conversación
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="transcripcion-loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
                    Cargando...
                </div>
                <div id="transcripcion-error" class="alert alert-danger m-3 d-none">
                    No se pudo cargar la conversación.
                </div>
                <div id="chats-mensajes" class="chats-mensajes d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal enviar mensaje -->
<div class="modal fade" id="modalEnviar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-send text-success me-1"></i> Enviar mensaje
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Número origen</label>
                    <input type="text" id="enviar-origen" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Número destino</label>
                    <input type="text" id="enviar-destino" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mensaje</label>
                    <textarea id="enviar-mensaje" class="form-control" rows="4" placeholder="Escribe el mensaje..."></textarea>
                </div>
                <div id="enviar-resultado"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-enviar" onclick="enviar_mensaje()">
                    <i class="bi bi-send me-1"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    const conversacionUrl = <?php echo json_encode(base_url('wts/conversacion/')); ?>;

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&' + 'amp;')
            .replace(/</g, '&' + 'lt;')
            .replace(/>/g, '&' + 'gt;')
            .replace(/"/g, '&' + 'quot;');
    }

    function fmtFechaCorta(str) {
        if (!str) return '&mdash;';
        var p = String(str).split('-');
        if (p.length === 3) return p[2] + '/' + p[1] + '/' + p[0];
        return str;
    }

    $(document).ready(function() {
        $('#tabla-whatsapp').DataTable({
            ajax: { url: '<?= base_url("wts/historial_json") ?>', type: 'GET', dataSrc: 'data' },
            processing: true,
            columns: [
                { data: 0, className: 'text-center' },
                { data: 1, className: 'small text-muted' },
                { data: 2 },
                { data: 3 },
                { data: 4, className: 'text-center' },
                { data: 5},
                { data: 6},
                { data: 7}
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function(d, type) {
                        return type === 'display' ? '<strong>#' + d + '</strong>' : d;
                    }
                },{
                    targets: 1,
                    render: function(d, type) {
                        return type === 'display' ? fmtFechaCorta(d) : d;
                    }
                },{
                    targets: 2,
                    render: function(d, type) {
                        var t = String(d || '').replace(/^whatsapp:/i, '');
                        return type === 'display' ? escHtml(t) : t;
                    }
                },{
                    targets: 3,
                    render: function(d, type) {
                        return type === 'display' ? escHtml(d) : d;
                    }
                },{
                    targets: 4,
                    render: function(d, type) {
                        return type === 'display' ? escHtml(d) : d;
                    }
                },{
                    targets: 5,
                    render: function(d, type) {
                        return type === 'display' ? escHtml(d) : d;
                    }
                },{
                    targets: 6,
                    render: function(d, type) {
                        //return type === 'display' ? escHtml(d) : d;
                        if(d.trim() == 'ENVIADO'){
                            return "<div style=\"background-color:lightgreen;text-align:center;border-radius:7px;\">" + d + "</div>"
                        }else{
                            return "<div style=\"background-color:rgb(255,110,110);text-align:center;border-radius:7px;\">" + d + "</div>"
                        }
                    }
                },{
                    targets: 7,
                    render: function(d, type, row) {
                        if (type !== 'display') return '';
                        var telefono  = String(row[2] || '').replace(/^whatsapp:/i, '');
                        var nroPropio = String(row[8] || '').replace(/^whatsapp:/i, '');

                        return '<a href="#" title="Ver" onclick="detalle(' + row[0] + ');return false;">' +
                               '<i class="bi bi-eye me-3" style="font-size:18px"></i></a>' +
                               '<a href="#" title="Enviar" onclick="abrirEnviar(' + row[0] + ',\'' + escHtml(nroPropio) + '\',\'' + escHtml(telefono) + '\');return false;">' +
                               '<i class="bi bi-send" style="font-size:18px"></i></a>';
                    }
                }
            ],
            dom:
                "<'row mb-3'<'col-md-6'B><'col-md-6 d-flex justify-content-end align-items-center'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-md-5 small text-muted'i><'col-md-7 d-flex justify-content-end'p>>",
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="bi bi-clipboard me-1"></i>Copiar',
                    className: 'btn btn-outline-secondary btn-sm',
                    exportOptions: { columns: [0,1,2,3,4] }
                },
                {
                    extend: 'excel',
                    text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: { columns: [0,1,2,3,4] }
                },
                {
                    extend: 'pdf',
                    text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: { columns: [0,1,2,3,4] }
                },
                {
                    extend: 'csv',
                    text: '<i class="bi bi-filetype-csv me-1"></i>CSV',
                    className: 'btn btn-secondary btn-sm',
                    exportOptions: { columns: [0,1,2,3,4] }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer me-1"></i>Imprimir',
                    className: 'btn btn-info btn-sm text-white',
                    exportOptions: { columns: [0,1,2,3,4] }
                }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            order: [[0, 'desc']],
            pageLength: 22
        });
    });

    function detalle(id) {
        const $loading = $('#transcripcion-loading');
        const $error   = $('#transcripcion-error');
        const $chat    = $('#chats-mensajes');

        $loading.removeClass('d-none');
        $error.addClass('d-none');
        $chat.addClass('d-none').empty();

        fetch(conversacionUrl + id)
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                $loading.addClass('d-none');

                if (!data.ok || !Array.isArray(data.mensajes) || data.mensajes.length === 0) {
                    $error.removeClass('d-none');
                    return;
                }

                data.mensajes.forEach(function(m) {
                    var tipo = (m.tipo || '').trim().toUpperCase();
                    var esEnviado = (tipo === 'ENVIADO');

                    var fila = document.createElement('div');
                    fila.className = 'chat-fila ' + (esEnviado ? 'enviado' : 'recibido');

                    var burbuja = document.createElement('div');
                    burbuja.className = 'burbuja ' + (esEnviado ? 'enviado' : 'recibido');

                    var texto = document.createElement('div');
                    texto.textContent = m.mensaje || '';

                    var hora = document.createElement('div');
                    hora.className = 'hora';
                    hora.textContent = fmtFechaHora(m.fecha);
                    if (esEnviado) {
                        hora.innerHTML = fmtFechaHora(m.fecha) + ' <i class="bi bi-check2-all"></i>';
                    }

                    burbuja.appendChild(texto);
                    burbuja.appendChild(hora);
                    fila.appendChild(burbuja);
                    $chat.append(fila);
                });

                $chat.removeClass('d-none');

                // Scroll al inicio (mensaje más antiguo)
                $chat.scrollTop(0);
            })
            .catch(function() {
                $loading.addClass('d-none');
                $error.removeClass('d-none');
            });

        var modalEl = document.getElementById('modalTranscripcion');
        var modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    var telDestinoEnvio = '';
    var telOrigenEnvio = '';

    function abrirEnviar(id, origen, telefono) {
        // Limpiar campos del modal
        $('#enviar-mensaje').val('');
        $('#enviar-resultado').empty();
        $('#btn-enviar').prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');

        // Quitar prefijo "whatsapp:" y espacios
        var numero = String(telefono || '').trim();
        numero = numero.replace(/^whatsapp:/i, '');
        telDestinoEnvio = numero;
        $('#enviar-destino').val(numero);

        var numeroD = String(origen || '').trim();
        numeroD = numeroD.replace(/^whatsapp:/i, '');
        telOrigenEnvio = numeroD;
        $('#enviar-origen').val(numeroD);

        var modalEl = document.getElementById('modalEnviar');
        var modal   = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function enviar_mensaje() {
        var msg = $('#enviar-mensaje').val().trim();
        var $btn = $('#btn-enviar');
        var $res = $('#enviar-resultado');

        $res.empty();

        if (!msg) {
            $res.html('<div class="alert alert-warning py-2 mb-0">Escribe un mensaje antes de enviar.</div>');
            return;
        }

        // Se le extrae el signo "+" al inicio del número
        var destino = telDestinoEnvio.replace(/^\+/, '');
        var origen = telOrigenEnvio.replace(/^\+/, '');

        // URL local del controlador (proxy) — evita el bloqueo de CORS
        var url = <?php echo json_encode(base_url('../varios/twilios/twilio_enviar_mensajes.php')); ?>
            + '?origen=' + encodeURIComponent(origen)
            + '&destino=' + encodeURIComponent(destino)
            + '&msg=' + encodeURIComponent(msg);

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Enviando...');

        fetch(url)
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');

                if (data && data.ok) {
                    $res.html('<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle me-1"></i> Mensaje enviado correctamente.</div>');
                    $('#enviar-mensaje').val('');
                } else {
                    var errorMsg = (data && data.error) ? data.error : 'El servicio no confirmó el envío.';
                    $res.html('<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-1"></i> ' + errorMsg + '</div>');
                }
            })
            .catch(function() {
                $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');
                $res.html('<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-1"></i> No se pudo enviar el mensaje. Verifica la conexión con el servicio.</div>');
            });
    }

    function fmtFechaHora(str) {
        if (!str) return '';
        // La fecha puede venir como "YYYY-MM-DD HH:MM:SS"
        var d = new Date(String(str).replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        var fecha = d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        var hora  = d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
        return fecha + ' ' + hora;
    }
</script>